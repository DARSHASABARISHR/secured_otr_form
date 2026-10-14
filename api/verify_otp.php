<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/otp.php';
check_csrf();

$purpose = (string)($_POST['purpose'] ?? '');
$channel = (string)($_POST['channel'] ?? '');
$target  = trim((string)($_POST['target'] ?? ''));
$otp     = trim((string)($_POST['otp'] ?? ''));

$allowedPurposes = [
    'registration',
    'forgot_password',
    'login'
];

if (
    !in_array($purpose, $allowedPurposes, true) ||
    !in_array($channel, ['email', 'phone'], true)
) {
    json_response([
        'ok' => false,
        'message' => 'Invalid OTP verification request.'
    ], 400);
}

/* Login OTP must use email */
if ($purpose === 'login' && $channel !== 'email') {
    json_response([
        'ok' => false,
        'message' => 'Login OTP is available through email only.'
    ], 422);
}

/* Confirm that login email belongs to a registered user */
if ($purpose === 'login') {
    if (!valid_email($target)) {
        json_response([
            'ok' => false,
            'message' => 'Enter a valid email address.'
        ], 422);
    }

    $query = db()->prepare(
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

$result = verify_otp($purpose, $channel, $target, $otp);

json_response($result, !empty($result['ok']) ? 200 : 422);