<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
check_csrf();

$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response([
        'ok' => false,
        'message' => 'Enter a valid email address.'
    ], 422);
}

if ($password === '') {
    json_response([
        'ok' => false,
        'message' => 'Enter your OTR password.'
    ], 422);
}

/* Check that this email was verified for login */
$verificationKey = 'verified_login_email_' . hash('sha256', $email);

if (empty($_SESSION[$verificationKey])) {
    json_response([
        'ok' => false,
        'message' => 'Please verify your email OTP before logging in.'
    ], 401);
}

/* Find the registered user */
$query = db()->prepare(
    'SELECT id, password_hash FROM users WHERE email = ? LIMIT 1'
);
$query->execute([$email]);
$user = $query->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    json_response([
        'ok' => false,
        'message' => 'Invalid email address or OTR password.'
    ], 401);
}

/* Login successful */
unset($_SESSION[$verificationKey]);
session_regenerate_id(true);

$_SESSION['user_id'] = (int)$user['id'];

json_response([
    'ok' => true,
    'redirect' => 'dashboard.php'
]);