<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/otp.php';
check_csrf();

$purpose = (string)($_POST['purpose'] ?? '');
$channel = (string)($_POST['channel'] ?? '');
$target  = trim((string)($_POST['target'] ?? ''));

$allowedPurposes = ['registration', 'forgot_password', 'login'];

if (
    !in_array($purpose, $allowedPurposes, true) ||
    !in_array($channel, ['email', 'phone'], true)
) {
    json_response([
        'ok' => false,
        'message' => 'Invalid OTP request.'
    ], 400);
}

/* Validate email or phone */
if ($channel === 'email' && !valid_email($target)) {
    json_response([
        'ok' => false,
        'message' => 'Enter a valid email address.'
    ], 422);
}

if ($channel === 'phone' && !valid_phone($target)) {
    json_response([
        'ok' => false,
        'message' => 'Enter a valid 10-digit Indian mobile number.'
    ], 422);
}

$pdo = db();

/* Registration OTP */
if ($purpose === 'registration') {
    $column = $channel === 'email' ? 'email' : 'phone';

    $query = $pdo->prepare(
        "SELECT id FROM users WHERE $column = ? LIMIT 1"
    );
    $query->execute([$target]);

    if ($query->fetch()) {
        json_response([
            'ok' => false,
            'message' => ucfirst($channel) . ' already registered.'
        ], 409);
    }
}

/* Forgot password OTP */
if ($purpose === 'forgot_password') {
    $registrationNumber = strtoupper(
        trim((string)($_POST['registration_number'] ?? ''))
    );

    $column = $channel === 'email' ? 'email' : 'phone';

    $query = $pdo->prepare(
        "SELECT id FROM users
         WHERE registration_number = ? AND $column = ?
         LIMIT 1"
    );

    $query->execute([$registrationNumber, $target]);

    if (!$query->fetch()) {
        json_response([
            'ok' => false,
            'message' => 'Details do not match our records.'
        ], 422);
    }
}

/* Login OTP: only an already-registered email is allowed */
if ($purpose === 'login') {
    if ($channel !== 'email') {
        json_response([
            'ok' => false,
            'message' => 'Login OTP is available through email only.'
        ], 422);
    }

    $query = $pdo->prepare(
        'SELECT id FROM users WHERE email = ? LIMIT 1'
    );
    $query->execute([$target]);

    if (!$query->fetch()) {
        json_response([
            'ok' => false,
            'message' => 'Email address is not registered.'
        ], 422);
    }
}

/* Generate and send OTP */
$result = create_otp($purpose, $channel, $target);

json_response($result, !empty($result['ok']) ? 200 : 422);