<?php require_once __DIR__ . '/includes/bootstrap.php';
$captchaA = random_int(1, 9);
$captchaB = random_int(1, 9);
$_SESSION['registration_captcha_answer'] = (string)($captchaA + $captchaB); ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Register | Secured OTR</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <main class="page"><a class="back" href="index.php">← Home</a>
        <section class="card">
            <div class="brand smallbrand">SECURED <span>OTR</span></div>
            <h1>Create your account</h1>
            <p class="muted">Verify your email and phone demo OTP.</p>
            <div id="notice" class="notice"></div>
            <form id="registerForm"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><label>Full name<input id="name" name="name" required></label><label>Email<input id="email" name="email" type="email" required></label><button id="sendEmail" class="btn secondary" type="button">Generate Email OTP</button> <span id="emailTimer" class="timer"></span>
                <div id="emailOtpBox" class="otpbox" hidden><label>Email OTP<input id="emailOtp" maxlength="4" inputmode="numeric"></label><button id="verifyEmail" class="btn secondary" type="button">Verify Email</button><span id="emailState" class="verify-state"></span><button id="resendEmail" class="textbtn" type="button" disabled>Resend Email OTP</button></div><label>Phone number<input id="phone" name="phone" maxlength="10" inputmode="numeric" required></label><button id="sendPhone" class="btn secondary" type="button">Generate Phone OTP</button> <span id="phoneTimer" class="timer"></span>
                <div id="phoneOtpBox" class="otpbox" hidden>
                    <div id="demoOtp" class="demo-otp" hidden></div><label>Phone OTP<input id="phoneOtp" maxlength="4" inputmode="numeric"></label><button id="verifyPhone" class="btn secondary" type="button">Verify Phone</button><span id="phoneState" class="verify-state"></span><button id="resendPhone" class="textbtn" type="button" disabled>Resend Phone OTP</button>
                </div><label>Security check <span class="muted">(CAPTCHA)</span>
                    <div class="captcha-question"><?= h((string)$captchaA) ?> + <?= h((string)$captchaB) ?> = ?</div><input name="captcha_answer" inputmode="numeric" autocomplete="off" required aria-label="CAPTCHA answer">
                </label><label>Password<input name="password" type="password" minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}" title="At least 8 characters, one uppercase, one lowercase, and one special character." autocomplete="new-password" required></label><label>Confirm password<input name="confirm_password" type="password" minlength="8" required autocomplete="new-password"></label>
                <p class="small password-rule">Password must have 8+ characters, one uppercase letter, one lowercase letter, and one special character (such as @, #, !).</p><button class="btn primary full" type="submit">Complete Registration</button>
            </form>
            <p class="small">Phone OTP is for classroom demo only; no SMS is sent.</p>
        </section>
    </main>
    <script src="assets/app.js"></script>
</body>

</html>