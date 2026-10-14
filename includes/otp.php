<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/mail.php';
require_once __DIR__ . '/sms.php';
function create_otp(string $purpose, string $channel, string $target): array
{
    $pdo = db();
    $valid = $channel === 'phone' ? PHONE_OTP_VALID_SECONDS : EMAIL_OTP_VALID_SECONDS;
    $resend = OTP_RESEND_SECONDS;
    $q = $pdo->prepare('SELECT resend_available_at FROM otp_verifications WHERE purpose=? AND channel=? AND target=? ORDER BY id DESC LIMIT 1');
    $q->execute([$purpose, $channel, $target]);
    $last = $q->fetch();
    if ($last && strtotime($last['resend_available_at']) > time()) return ['ok' => false, 'message' => 'Please wait until resend is available.', 'retry_after' => strtotime($last['resend_available_at']) - time()];
    $otp = otp_code();
    $hash = password_hash($otp, PASSWORD_DEFAULT);
    $expires = date('Y-m-d H:i:s', time() + $valid);
    $resendAt = date('Y-m-d H:i:s', time() + $resend);
    $q = $pdo->prepare('INSERT INTO otp_verifications(purpose,channel,target,otp_hash,expires_at,resend_available_at) VALUES(?,?,?,?,?,?)');
    $q->execute([$purpose, $channel, $target, $hash, $expires, $resendAt]);
    $id = (int)$pdo->lastInsertId();
    $sent = $channel === 'email' ? send_email_otp($target, $otp, $purpose) : send_sms_otp($target, $otp);
    if (!$sent['ok']) {
        $pdo->prepare('DELETE FROM otp_verifications WHERE id=?')->execute([$id]);
        return $sent;
    }
    return ['ok' => true, 'otp_id' => $id, 'expires_in' => $valid, 'resend_in' => $resend, 'test_otp' => $channel === 'phone' ? $otp : null, 'demo_notice' => $channel === 'phone' ? 'DEMO ONLY: no SMS was sent.' : ''];
}
function verify_otp(string $purpose, string $channel, string $target, string $otp): array
{
    if (!preg_match('/^[0-9]{4}$/', $otp)) return ['ok' => false, 'message' => 'Enter the 4-digit OTP.'];
    $pdo = db();
    $q = $pdo->prepare('SELECT * FROM otp_verifications WHERE purpose=? AND channel=? AND target=? AND verified_at IS NULL ORDER BY id DESC LIMIT 1');
    $q->execute([$purpose, $channel, $target]);
    $row = $q->fetch();
    if (!$row) return ['ok' => false, 'message' => 'Invalid OTP. Generate a new one.'];
    if (strtotime($row['expires_at']) <= time()) return ['ok' => false, 'message' => 'OTP expired. Please resend.'];
    if ((int)$row['attempts'] >= OTP_MAX_ATTEMPTS) return ['ok' => false, 'message' => 'Too many attempts. Generate a new OTP.'];
    if (!password_verify($otp, $row['otp_hash'])) {
        $pdo->prepare('UPDATE otp_verifications SET attempts=attempts+1 WHERE id=?')->execute([$row['id']]);
        return ['ok' => false, 'message' => 'Invalid OTP.'];
    }
    $pdo->prepare('UPDATE otp_verifications SET verified_at=? WHERE id=?')->execute([now_sql(), $row['id']]);
    $_SESSION['verified_' . $purpose . '_' . $channel . '_' . hash('sha256', $target)] = true;

    if ($purpose === 'login' && $channel === 'email') {
        $_SESSION['verified_login_email_' . hash('sha256', $target)] = true;
    }
    return ['ok' => true, 'message' => ucfirst($channel) . ' OTP verified successfully.'];
}
