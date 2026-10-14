const $ = s => document.querySelector(s);
const notice = $('#notice');

function show(message, type = 'error') {
    if (notice) {
        notice.textContent = message;
        notice.className = 'notice show ' + type;
    }
}

async function post(url, data) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: data
        });
        return await response.json();
    } catch {
        return {
            ok: false,
            message: 'Server error. Check XAMPP/PHP.'
        };
    }
}

function timer(button, element, seconds) {
    if (!button || !element) return;

    let remaining = seconds;
    button.disabled = true;
    element.textContent = `Resend available in ${remaining}s`;

    const interval = setInterval(() => {
        remaining--;

        if (remaining <= 0) {
            clearInterval(interval);
            button.disabled = false;
            element.textContent = 'You can resend OTP now.';
        } else {
            element.textContent = `Resend available in ${remaining}s`;
        }
    }, 1000);
}

async function sendOtp(channel, target, box, timerElement, resendButton, extra = {}) {
    if (!target) {
        show(channel === 'email'
            ? 'Enter email first.'
            : 'Enter phone number first.');
        return;
    }

    const csrf = $('input[name="csrf"]');
    if (!csrf) {
        show('Security token missing. Refresh the page.');
        return;
    }

    const data = new FormData();
    data.append('csrf', csrf.value);
    data.append('purpose', extra.purpose || 'registration');
    data.append('channel', channel);
    data.append('target', target);

    if (extra.registration_number) {
        data.append('registration_number', extra.registration_number);
    }

    const result = await post('api/send_otp.php', data);

    if (!result.ok) {
        show(result.message);
        return;
    }

    if (box) box.hidden = false;

    if (channel === 'phone' && result.test_otp) {
        show(
            `Demo phone OTP: ${result.test_otp}. No SMS was sent.`,
            'success'
        );
    } else {
        show('OTP sent. Check your email inbox and spam folder.', 'success');
    }

    timer(resendButton, timerElement, result.resend_in || 60);
}

async function verifyOtp(channel, target, otp, purpose = 'registration') {
    const csrf = $('input[name="csrf"]');

    if (!csrf) {
        show('Security token missing. Refresh the page.');
        return false;
    }

    const data = new FormData();
    data.append('csrf', csrf.value);
    data.append('purpose', purpose);
    data.append('channel', channel);
    data.append('target', target);
    data.append('otp', otp);

    const result = await post('api/verify_otp.php', data);

    if (!result.ok) {
        show(result.message);
        return false;
    }

    show(result.message || 'OTP verified successfully.', 'success');
    return true;
}

/* REGISTRATION */
if ($('#registerForm')) {
    $('#sendEmail').onclick = () =>
        sendOtp('email', $('#email').value.trim(),
            $('#emailOtpBox'), $('#emailTimer'), $('#resendEmail'));

    $('#resendEmail').onclick = () =>
        sendOtp('email', $('#email').value.trim(),
            $('#emailOtpBox'), $('#emailTimer'), $('#resendEmail'));

    $('#sendPhone').onclick = () =>
        sendOtp('phone', $('#phone').value.trim(),
            $('#phoneOtpBox'), $('#phoneTimer'), $('#resendPhone'));

    $('#resendPhone').onclick = () =>
        sendOtp('phone', $('#phone').value.trim(),
            $('#phoneOtpBox'), $('#phoneTimer'), $('#resendPhone'));

    $('#verifyEmail').onclick = async () => {
        const email = $('#email').value.trim();

        if (await verifyOtp('email', email, $('#emailOtp').value.trim())) {
            $('#emailState').textContent = '✓ Email verified';
            $('#emailState').className = 'verify-state verified';
        }
    };

    $('#verifyPhone').onclick = async () => {
        const phone = $('#phone').value.trim();

        if (await verifyOtp('phone', phone, $('#phoneOtp').value.trim())) {
            $('#phoneState').textContent = '✓ Phone demo verified';
            $('#phoneState').className = 'verify-state verified';
        }
    };

    $('#registerForm').onsubmit = async event => {
        event.preventDefault();

        const result = await post(
            'api/register.php',
            new FormData(event.target)
        );

        if (result.ok) {
            location.href = result.redirect;
        } else {
            show(result.message);

            if (result.message.toLowerCase().includes('captcha')) {
                const captcha = event.target.querySelector(
                    'input[name="captcha_answer"]'
                );

                if (captcha) {
                    captcha.value = '';
                    captcha.focus();
                }
            }
        }
    };
}

/* LOGIN: EMAIL OTP + OTR PASSWORD */
if ($('#loginForm')) {
    const emailInput = $('#loginEmail');
    const sendButton = $('#sendLoginOtp');
    const otpBox = $('#loginOtpBox');
    const otpInput = $('#loginOtp');
    const verifyButton = $('#verifyLoginOtp');
    const timerElement = $('#loginOtpTimer');
    const passwordBox = $('#loginPasswordBox');
    const passwordInput = $('#loginPassword');

    let verifiedEmail = '';

    sendButton.onclick = async () => {
        const email = emailInput.value.trim();

        if (!email) {
            show('Enter your registered email address.');
            return;
        }

        const csrf = $('input[name="csrf"]');
        const data = new FormData();

        data.append('csrf', csrf.value);
        data.append('purpose', 'login');
        data.append('channel', 'email');
        data.append('target', email);

        const result = await post('api/send_otp.php', data);

        if (!result.ok) {
            show(result.message);
            return;
        }

        otpBox.hidden = false;
        passwordBox.hidden = true;
        verifiedEmail = '';

        show('Login OTP sent. Check your email.', 'success');
        timer(sendButton, timerElement, result.resend_in || 60);
    };

    verifyButton.onclick = async () => {
        const email = emailInput.value.trim();
        const otp = otpInput.value.trim();

        if (!email || !otp) {
            show('Enter your email and OTP.');
            return;
        }

        const csrf = $('input[name="csrf"]');
        const data = new FormData();

        data.append('csrf', csrf.value);
        data.append('purpose', 'login');
        data.append('channel', 'email');
        data.append('target', email);
        data.append('otp', otp);

        const result = await post('api/verify_otp.php', data);

        if (!result.ok) {
            show(result.message);
            return;
        }

        verifiedEmail = email;
        passwordBox.hidden = false;
        passwordInput.required = true;

        show('Email OTP verified. Enter your OTR password.', 'success');
    };

    emailInput.addEventListener('input', () => {
        if (emailInput.value.trim() !== verifiedEmail) {
            verifiedEmail = '';
            passwordBox.hidden = true;
        }
    });

    $('#loginForm').onsubmit = async event => {
        event.preventDefault();

        if (!verifiedEmail || emailInput.value.trim() !== verifiedEmail) {
            show('Please verify your email OTP first.');
            return;
        }

        const data = new FormData();
        data.append('csrf', $('input[name="csrf"]').value);
        data.append('email', verifiedEmail);
        data.append('password', passwordInput.value);

        const result = await post('api/login.php', data);

        if (result.ok) {
            location.href = result.redirect;
        } else {
            show(result.message);
        }
    };

    /* FORGOT PASSWORD */
    $('#forgotBtn').onclick = () => {
        $('#forgotBox').hidden = !$('#forgotBox').hidden;
    };

    $('#sendForgot').onclick = () =>
        sendOtp(
            $('#forgotChannel').value,
            $('#forgotTarget').value.trim(),
            $('#forgotOtpBox'),
            $('#forgotTimer'),
            $('#resendForgot'),
            {
                purpose: 'forgot_password',
                registration_number: $('#forgotReg').value.trim()
            }
        );

    $('#resendForgot').onclick = () =>
        sendOtp(
            $('#forgotChannel').value,
            $('#forgotTarget').value.trim(),
            $('#forgotOtpBox'),
            $('#forgotTimer'),
            $('#resendForgot'),
            {
                purpose: 'forgot_password',
                registration_number: $('#forgotReg').value.trim()
            }
        );

    $('#verifyForgot').onclick = async () => {
        if (await verifyOtp(
            $('#forgotChannel').value,
            $('#forgotTarget').value.trim(),
            $('#forgotOtp').value.trim(),
            'forgot_password'
        )) {
            $('#resetBox').hidden = false;
        }
    };

    $('#resetPasswordBtn').onclick = async () => {
        const data = new FormData();

        data.append('csrf', $('input[name="csrf"]').value);
        data.append('registration_number', $('#forgotReg').value.trim());
        data.append('channel', $('#forgotChannel').value);
        data.append('target', $('#forgotTarget').value.trim());
        data.append('new_password', $('#resetPassword').value);
        data.append('confirm_password', $('#resetConfirm').value);

        const result = await post('api/reset_password.php', data);
        show(result.message, result.ok ? 'success' : 'error');
    };
}

/* CHANGE PASSWORD */
if ($('#changeForm')) {
    $('#changeForm').onsubmit = async event => {
        event.preventDefault();

        const result = await post(
            'api/change_password.php',
            new FormData(event.target)
        );

        show(result.message, result.ok ? 'success' : 'error');
    };
}

/* SHOW / HIDE PASSWORD */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[type="password"]').forEach(input => {
        if (input.parentElement.classList.contains('password-wrap')) return;

        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        wrapper.style.display = 'flex';
        wrapper.style.alignItems = 'center';

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        input.style.flex = '1';
        input.style.paddingRight = '45px';

        const button = document.createElement('button');
        button.type = 'button';
        button.textContent = '👁️';
        button.setAttribute('aria-label', 'Show password');

        button.style.position = 'absolute';
        button.style.right = '10px';
        button.style.border = 'none';
        button.style.background = 'transparent';
        button.style.cursor = 'pointer';
        button.style.fontSize = '18px';

        button.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            button.textContent = visible ? '🙈' : '👁️';
            button.setAttribute(
                'aria-label',
                visible ? 'Hide password' : 'Show password'
            );
        });

        wrapper.appendChild(button);
    });
});