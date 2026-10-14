<?php
require_once __DIR__.'/includes/bootstrap.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login | Secured OTR</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<main class="page">
<a class="back" href="index.php">← Home</a>

<section class="card narrow">
<div class="brand smallbrand">SECURED <span>OTR</span></div>

<h1>Welcome back</h1>
<div id="notice" class="notice"></div>

<form id="loginForm">
<input type="hidden" name="csrf" value="<?=h($_SESSION['csrf'])?>">

<label>Email ID
<input type="email" id="loginEmail" name="email" required>
</label>

<button class="btn secondary full" type="button" id="sendLoginOtp">
Send OTP
</button>

<div id="loginOtpBox" hidden>
<label>Enter Email OTP
<input type="text" id="loginOtp" maxlength="4"
inputmode="numeric" pattern="[0-9]{4}">
</label>

<button class="btn secondary full" type="button" id="verifyLoginOtp">
Verify OTP
</button>

<span id="loginOtpTimer" class="timer"></span>
</div>

<div id="loginPasswordBox" hidden>
<label>OTR Password
<input type="password" name="password" id="loginPassword">
</label>

<button class="btn primary full" type="submit">Login</button>
</div>
</form>

<button id="forgotBtn" class="textbtn" type="button">
Forgot password?
</button>

<div id="forgotBox" hidden>
<hr>
<h2>Reset password</h2>

<label>Registration number
<input id="forgotReg">
</label>

<label>Channel
<select id="forgotChannel">
<option value="email">Email</option>
<option value="phone">Phone demo</option>
</select>
</label>

<label>Email or phone
<input id="forgotTarget">
</label>

<button id="sendForgot" class="btn secondary" type="button">
Generate OTP
</button>

<span id="forgotTimer" class="timer"></span>

<div id="forgotOtpBox" hidden>
<label>OTP
<input id="forgotOtp" maxlength="4" inputmode="numeric">
</label>

<button id="verifyForgot" class="btn secondary" type="button">
Verify OTP
</button>

<button id="resendForgot" class="textbtn" type="button" disabled>
Resend OTP
</button>
</div>

<div id="resetBox" hidden>
<label>New password
<input id="resetPassword" type="password" minlength="8"
pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}"
title="At least 8 characters, one uppercase, one lowercase, and one special character.">
</label>

<p class="small password-rule">
Use 8+ characters, one uppercase, one lowercase, and one special character.
</p>

<label>Confirm new password
<input id="resetConfirm" type="password" minlength="8">
</label>

<button id="resetPasswordBtn" class="btn primary" type="button">
Reset password
</button>
</div>
</div>

<p class="small">
New user? <a href="register.php">Register here</a>
</p>

</section>
</main>

<script src="assets/app.js"></script>
</body>
</html>