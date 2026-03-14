<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - SIMS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
    <style>
        .otp-box {
            max-width: 400px;
            margin: 100px auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            text-align: center;
        }
        .otp-header h2 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }
        .otp-header p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        .otp-input-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
        }
        .otp-input {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .otp-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .btn-verify {
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            font-weight: 600;
        }
        .resend-link {
            margin-top: 1.5rem;
            font-size: 0.85rem;
            color: var(--text-muted);
        }
        .resend-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body style="background: #f8fafc;">
    <div class="container">
        <div class="otp-box">
            <div class="otp-header">
                <h2>Two-Factor Auth</h2>
                <p>We've sent a 6-digit verification code to your email. Please enter it below to continue.</p>
            </div>

            <form id="otpForm">
                <input type="hidden" name="action" value="verify_otp">
                <div class="otp-input-group">
                    <input type="text" maxlength="1" class="otp-input" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" pattern="\d*" inputmode="numeric">
                    <input type="text" maxlength="1" class="otp-input" pattern="\d*" inputmode="numeric">
                </div>
                <input type="hidden" id="fullOtp" name="otp">

                <button type="submit" class="btn btn-primary btn-verify" id="btnVerify">Verify & Sign In</button>
            </form>

            <div class="resend-link">
                Didn't receive code? <a href="<?= BASE_URL ?>/login">Back to Login</a>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/resources/js/toast.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputs = document.querySelectorAll('.otp-input');
            const form = document.getElementById('otpForm');
            const btnVerify = document.getElementById('btnVerify');
            const fullOtpInput = document.getElementById('fullOtp');

            // Handle OTP input focus and movement
            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    if (e.target.value.length >= 1) {
                        if (index < inputs.length - 1) {
                            inputs[index + 1].focus();
                        }
                    }
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value) {
                        if (index > 0) {
                            inputs[index - 1].focus();
                        }
                    }
                });
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                let otp = "";
                inputs.forEach(input => otp += input.value);
                
                if (otp.length < 6) {
                    showToast('Please enter all 6 digits.', 'warning');
                    return;
                }

                fullOtpInput.value = otp;
                btnVerify.disabled = true;
                btnVerify.textContent = "Verifying...";

                const formData = new FormData(this);
                
                fetch('<?= BASE_URL ?>/controllers/login.controller.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    const parts = data.split('|');
                    if (parts[0].trim() === 'success') {
                        showToast('Verification successful!', 'success');
                        setTimeout(() => {
                            window.location.href = parts[1];
                        }, 500);
                    } else {
                        showToast(parts[1] || 'Verification failed.', 'error');
                        btnVerify.disabled = false;
                        btnVerify.textContent = "Verify & Sign In";
                        // Reset inputs on error
                        inputs.forEach(input => input.value = '');
                        inputs[0].focus();
                    }
                })
                .catch(err => {
                    showToast('System Error: ' + err.message, 'error');
                    btnVerify.disabled = false;
                    btnVerify.textContent = "Verify & Sign In";
                });
            });
        });
    </script>
</body>

</html>
