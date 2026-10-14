<?php

declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
/* CLASSROOM DEMO ONLY: no SMS is sent; the code is shown on the page. */
function send_sms_otp(string $mobile, string $otp): array
{
    $_SESSION['test_phone_otp'] = $otp;
    $_SESSION['test_phone_number'] = $mobile;
    $_SESSION['test_phone_otp_created'] = time();
    return ['ok' => true, 'message' => 'Demo OTP generated.'];
}
