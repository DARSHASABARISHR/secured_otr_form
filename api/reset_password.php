<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
check_csrf();
$reg = strtoupper(trim((string)($_POST['registration_number'] ?? '')));
$c = (string)($_POST['channel'] ?? '');
$t = trim((string)($_POST['target'] ?? ''));
$pw = (string)($_POST['new_password'] ?? '');
$cf = (string)($_POST['confirm_password'] ?? '');
if (!valid_password_rules($pw)) json_response(['ok' => false, 'message' => 'Password needs 8+ characters, at least one uppercase letter, one lowercase letter, and one special character.'], 422);
if ($pw !== $cf) json_response(['ok' => false, 'message' => 'Passwords do not match.'], 422);
$key = 'verified_forgot_password_' . $c . '_' . hash('sha256', $t);
if (empty($_SESSION[$key])) json_response(['ok' => false, 'message' => 'Verify OTP first.'], 422);
$q = db()->prepare('UPDATE users SET password_hash=? WHERE registration_number=?');
$q->execute([password_hash($pw, PASSWORD_DEFAULT), $reg]);
if (!$q->rowCount()) json_response(['ok' => false, 'message' => 'Could not update password. Check registration number.'], 422);
unset($_SESSION[$key]);
json_response(['ok' => true, 'message' => 'Password updated.']);
