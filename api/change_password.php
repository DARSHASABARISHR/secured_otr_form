<?php

declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
check_csrf();
if (empty($_SESSION['user_id'])) json_response(['ok' => false, 'message' => 'Please log in again.'], 401);
$old = (string)($_POST['current_password'] ?? '');
$pw = (string)($_POST['new_password'] ?? '');
$cf = (string)($_POST['confirm_password'] ?? '');
if (!valid_password_rules($pw)) json_response(['ok' => false, 'message' => 'New password needs 8+ characters, at least one uppercase letter, one lowercase letter, and one special character.'], 422);
if ($pw !== $cf) json_response(['ok' => false, 'message' => 'Passwords do not match.'], 422);
$q = db()->prepare('SELECT password_hash FROM users WHERE id=?');
$q->execute([$_SESSION['user_id']]);
$u = $q->fetch();
if (!$u || !password_verify($old, $u['password_hash'])) json_response(['ok' => false, 'message' => 'Current password is incorrect.'], 422);
db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($pw, PASSWORD_DEFAULT), $_SESSION['user_id']]);
json_response(['ok' => true, 'message' => 'Password changed.']);
