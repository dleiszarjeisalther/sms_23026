<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified System Login</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/resources/css/index.css?v=<?= time() ?>">
</head>

<body>
    <div class="container login-container">
        <div class="login-box">
            <div class="login-header">
                <h2>System Portal</h2>
                <p>Login for Students & Registrars</p>
            </div>
            <div class="login-body">
                <div id="loginMessage"></div>

                <form id="loginForm" class="login-form">
                    <div class="form-group">
                        <label for="userId">User ID</label>
                        <input type="text" id="userId" name="userId" placeholder="e.g. 2024-001" value="<?= htmlspecialchars($_COOKIE['remembered_student'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-input-group">
                            <input type="password" id="password" name="password" placeholder="Enter your 8-character password" required>
                            <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('password', this)" aria-label="Toggle password visibility">
                                <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg class="eye-closed" style="display: none;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" id="rememberMe" name="remember_me" style="width: auto; margin: 0;">
                        <label for="rememberMe" style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary login-btn" id="btnLogin">Sign In</button>

                    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                        <span style="color: var(--text-muted);">New student? </span>
                        <a href="<?= BASE_URL ?>/admission" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Apply for Admission</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginForm = document.getElementById('loginForm');
            const btnLogin = document.getElementById('btnLogin');
            const loginMessage = document.getElementById('loginMessage');
            const inputs = loginForm.querySelectorAll('input');

            // Clear errors on input validation
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    input.style.borderColor = 'var(--border-color)';
                    loginMessage.innerText = '';
                });
            });

            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();

                let isValid = true;
                inputs.forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        input.style.borderColor = 'red';
                    }
                });

                if (!isValid) {
                    showToast('Please fill in both fields.', 'warning');
                    return;
                }

                btnLogin.disabled = true;
                btnLogin.textContent = "Authenticating...";

                var formData = new FormData(this);

                fetch('<?= BASE_URL ?>/controllers/login.controller.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        var parts = data.split('|');
                        var status = parts[0];
                        var responseData = parts[1] || 'Login failed.';

                        if (status.trim() === 'success') {
                            showToast('Login successful! Redirecting...', 'success');
                            setTimeout(() => {
                                window.location.href = responseData;
                            }, 500);
                        } else if (status.trim() === 'success_otp') {
                            showToast(parts[2] || 'OTP sent! Please verify.', 'success');
                            setTimeout(() => {
                                window.location.href = responseData;
                            }, 1000);
                        } else {
                            showToast(responseData, 'error');
                            btnLogin.disabled = false;
                            btnLogin.textContent = "Sign In";
                        }
                    })
                    .catch(error => {
                        showToast('Network Error: ' + error.message, 'error');
                        btnLogin.disabled = false;
                        btnLogin.textContent = "Sign In";
                    });
            });
        });

        function togglePasswordVisibility(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');

            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
            } else {
                input.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
            }
        }
    </script>
    <script src="<?= BASE_URL ?>/resources/js/toast.js"></script>
</body>

</html>