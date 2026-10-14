<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
function smtp_read($s): string
{
    $r = '';
    while (($l = fgets($s, 515)) !== false) {
        $r .= $l;
        if (isset($l[3]) && $l[3] === ' ') break;
    }
    return $r;
}
function smtp_cmd($s, string $c, array $codes): string
{
    fwrite($s, $c . "\r\n");
    $r = smtp_read($s);
    if (!in_array((int)substr($r, 0, 3), $codes, true)) throw new RuntimeException('SMTP command failed: ' . substr($r, 0, 3));
    return $r;
}
function send_email_otp(string $to, string $otp, string $purpose): array
{
    if (GMAIL_USERNAME === '' || GMAIL_APP_PASSWORD === '') return ['ok' => false, 'message' => 'Email not configured. Add Gmail username and App Password in config/config.php.'];
    $s = @stream_socket_client('ssl://smtp.gmail.com:465', $eno, $estr, 15);
    if (!$s) return ['ok' => false, 'message' => 'Cannot connect to Gmail SMTP. Check internet/OpenSSL.'];
    try {
        stream_set_timeout($s, 15);
        $g = smtp_read($s);
        if ((int)substr($g, 0, 3) !== 220) throw new RuntimeException('SMTP greeting failed');
        smtp_cmd($s, 'EHLO localhost', [250]);
        smtp_cmd($s, 'AUTH LOGIN', [334]);
        smtp_cmd($s, base64_encode(GMAIL_USERNAME), [334]);
        smtp_cmd($s, base64_encode(str_replace(' ', '', GMAIL_APP_PASSWORD)), [235]);
        smtp_cmd($s, 'MAIL FROM:<' . GMAIL_USERNAME . '>', [250]);
        smtp_cmd($s, 'RCPT TO:<' . $to . '>', [250, 251]);
        smtp_cmd($s, 'DATA', [354]);
        $subject = $purpose === 'forgot_password' ? 'Your password reset OTP' : 'Your Secured OTR OTP';
        $msg = "From: Secured OTR <" . GMAIL_USERNAME . ">\r\nTo: <$to>\r\nSubject: $subject\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\nYour OTP is $otp. It expires in 5 minutes. If you did not request it, ignore this email.";
        fwrite($s, preg_replace('/(?m)^\./', '..', $msg) . "\r\n.\r\n");
        $r = smtp_read($s);
        if ((int)substr($r, 0, 3) !== 250) throw new RuntimeException('Email rejected');
        smtp_cmd($s, 'QUIT', [221]);
        fclose($s);
        return ['ok' => true];
    } catch (Throwable $e) {
        @fclose($s);
        if (APP_DEBUG) error_log('Secured OTR SMTP: ' . $e->getMessage());
        return ['ok' => false, 'message' => 'Email sending failed. Check Gmail App Password and XAMPP PHP error log.'];
    }
}
