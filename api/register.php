<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
check_csrf();
$n = trim((string)($_POST['name'] ?? ''));
$e = trim((string)($_POST['email'] ?? ''));
$p = trim((string)($_POST['phone'] ?? ''));
$pw = (string)($_POST['password'] ?? '');
$captcha = trim((string)($_POST['captcha_answer'] ?? ''));
$cf = (string)($_POST['confirm_password'] ?? '');
if (strlen($n) < 2) json_response(['ok' => false, 'message' => 'Enter your full name.'], 422);
if (!valid_email($e)) json_response(['ok' => false, 'message' => 'Enter a valid email.'], 422);
if (!valid_phone($p)) json_response(['ok' => false, 'message' => 'Enter a valid 10-digit phone number.'], 422);
if (!isset($_SESSION['registration_captcha_answer']) || $captcha !== (string)$_SESSION['registration_captcha_answer']) {
    unset($_SESSION['registration_captcha_answer']);
    json_response(['ok' => false, 'message' => 'Incorrect CAPTCHA. Please refresh the registration page and try again.'], 422);
}
unset($_SESSION['registration_captcha_answer']);
if (!valid_password_rules($pw)) json_response(['ok' => false, 'message' => 'Password needs 8+ characters, at least one uppercase letter, one lowercase letter, and one special character.'], 422);
if ($pw !== $cf) json_response(['ok' => false, 'message' => 'Passwords do not match.'], 422);
$ek = 'verified_registration_email_' . hash('sha256', $e);
$pk = 'verified_registration_phone_' . hash('sha256', $p);
if (empty($_SESSION[$ek]) || empty($_SESSION[$pk])) json_response(['ok' => false, 'message' => 'Verify both email and phone OTP first.'], 422);
try {
    $reg = 'OTR' . date('ym') . strtoupper(bin2hex(random_bytes(3)));
    $q = db()->prepare('INSERT INTO users(registration_number,name,email,phone,password_hash) VALUES(?,?,?,?,?)');
    $q->execute([$reg, $n, $e, $p, password_hash($pw, PASSWORD_DEFAULT)]);
    unset($_SESSION[$ek], $_SESSION[$pk]);
    json_response(['ok' => true, 'redirect' => 'success.php?reg=' . urlencode($reg)]);
} catch (PDOException $ex) {
    json_response(['ok' => false, 'message' => 'Email or phone may already be registered.'], 409);
}
