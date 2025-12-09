<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Security Password - Home Assistant</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --body-bg: #f8f9fa;
            --card-bg: #fff;
            --text-color: #474d53;
            --input-bg: #fff;
            --input-border: #ced4da;
            --btn-primary: #4e73df;
            --btn-primary-hover: #3a5fc4;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        [data-theme="dark"] {
            --body-bg: #1a1d21;
            --card-bg: #2d343c;
            --text-color: #e9ecef;
            --input-bg: #343a40;
            --input-border: #495057;
            --btn-primary: #4e73df;
            --btn-primary-hover: #5a7ae2;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-color);
            transition: var(--transition);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 100%;
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 15px;
        }

        header {
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            background-color: var(--card-bg);
            margin-bottom: 40px;
            transition: var(--transition);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-title {
            font-size: 24px;
            font-weight: 600;
        }

        .theme-control {
            display: flex;
            gap: 10px;
        }

        .theme-btn {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            background-color: var(--card-bg);
            color: var(--text-color);
            border: 1px solid var(--input-border);
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .theme-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .theme-btn.active {
            background-color: var(--btn-primary);
            color: white;
            border-color: var(--btn-primary);
        }

        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            width: 100%;
            max-width: 600px;
            overflow: hidden;
            transition: var(--transition);
        }

        .card-header {
            padding: 20px;
            border-bottom: 1px solid var(--input-border);
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
        }

        .card-body {
            padding: 30px 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .input-group {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid var(--input-border);
            background-color: var(--input-bg);
            color: var(--text-color);
            transition: var(--transition);
            font-size: 16px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--btn-primary);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.25);
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--input-border);
            cursor: pointer;
        }

        .password-toggle:hover {
            color: var(--btn-primary);
        }

        .help-block {
            display: block;
            margin-top: 5px;
            color: #dc3545;
            font-size: 14px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--btn-primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--btn-primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .btn-default {
            background-color: transparent;
            color: var(--text-color);
            border-color: var(--input-border);
        }

        .btn-default:hover {
            background-color: var(--input-bg);
            transform: translateY(-2px);
            box-shadow: var(--box-shadow);
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .password-strength {
            height: 5px;
            margin-top: 8px;
            border-radius: 2px;
            background-color: #e9ecef;
            overflow: hidden;
        }

        .password-strength-meter {
            height: 100%;
            width: 0;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .strength-text {
            font-size: 14px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }

            .button-group {
                flex-direction: column;
            }

            .card {
                margin: 0 15px;
            }
        }

        .alert {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border-left: 4px solid #28a745;
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="header-content">
                <h1 class="header-title">Change Security Password</h1>
                <div class="theme-control">
                    <button class="theme-btn" data-theme="light">
                        <i class="fas fa-sun"></i> Light
                    </button>
                    <button class="theme-btn" data-theme="dark">
                        <i class="fas fa-moon"></i> Dark
                    </button>
                    <button class="theme-btn" data-theme="system">
                        <i class="fas fa-laptop"></i> System
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Home Assistant Security Password</h3>
            </div>
            <div class="card-body">
                <div id="alerts-container"></div>

                <form class="form" id="password-form" action="{{ route('change-pass') }}" method="post">
                    <input type="hidden" name="_token" id="csrf-token" value="{{ csrf_token() }}">

                    <div class="form-group">
                        <label for="old_password">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="old_password" id="old_password" class="form-control" required>
                            <button type="button" class="password-toggle" data-target="old_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="help-block text-danger" id="old_password_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new_password" id="new_password" class="form-control" required>
                            <button type="button" class="password-toggle" data-target="new_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="password-strength-meter" id="password-meter"></div>
                        </div>
                        <div class="strength-text" id="password-strength-text"></div>
                        <span class="help-block text-danger" id="new_password_error"></span>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control"
                                required>
                            <button type="button" class="password-toggle" data-target="confirm_password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <span class="help-block text-danger" id="confirm_password_error"></span>
                    </div>

                    <div class="button-group" style="justify-content: center; display: flex;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Change Password
                        </button>
                        <a href="/" class="btn btn-default">
                            <i class="fas fa-ban"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Theme management
            const themeButtons = document.querySelectorAll('.theme-btn');

            // Function to apply theme
            function applyTheme(theme) {
                if (theme === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    document.documentElement.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
                } else {
                    document.documentElement.setAttribute('data-theme', theme);
                }

                // Update active button state
                themeButtons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.theme === theme);
                });

                // Save theme preference
                localStorage.setItem('backpack-theme', theme);
            }

            // Set up event listeners for theme buttons
            themeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const theme = button.dataset.theme;
                    applyTheme(theme);
                });
            });

            // Apply saved theme on page load or use system default
            const savedTheme = localStorage.getItem('backpack-theme') || 'system';
            applyTheme(savedTheme);

            // Listen for system theme changes if using system theme
            if (savedTheme === 'system') {
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
                    if (localStorage.getItem('backpack-theme') === 'system') {
                        document.documentElement.setAttribute('data-theme', event.matches ? 'dark' :
                            'light');
                    }
                });
            }

            // Password toggle functionality
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const inputField = document.getElementById(targetId);
                    const icon = this.querySelector('i');

                    if (inputField.type === 'password') {
                        inputField.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        inputField.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });

            // Password strength meter
            const passwordInput = document.getElementById('new_password');
            const passwordMeter = document.getElementById('password-meter');
            const strengthText = document.getElementById('password-strength-text');

            passwordInput.addEventListener('input', function() {
                const password = this.value;
                let strength = 0;
                let message = '';

                if (password.length >= 4) strength += 25;
                if (password.length >= 8) strength += 25;
                if (/[A-Z]/.test(password)) strength += 15;
                if (/[0-9]/.test(password)) strength += 15;
                if (/[^A-Za-z0-9]/.test(password)) strength += 20;

                // Update the meter
                passwordMeter.style.width = strength + '%';

                // Set color based on strength
                if (strength < 30) {
                    passwordMeter.style.backgroundColor = '#dc3545'; // red
                    message = 'Weak';
                } else if (strength < 60) {
                    passwordMeter.style.backgroundColor = '#ffc107'; // yellow
                    message = 'Moderate';
                } else if (strength < 80) {
                    passwordMeter.style.backgroundColor = '#28a745'; // green
                    message = 'Strong';
                } else {
                    passwordMeter.style.backgroundColor = '#20c997'; // teal
                    message = 'Very Strong';
                }

                strengthText.textContent = message ? `Password strength: ${message}` : '';
            });

            // Form validation and submission
            const form = document.getElementById('password-form');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

                // Reset previous errors
                document.querySelectorAll('.help-block').forEach(el => el.textContent = '');

                // Get form values
                const oldPassword = document.getElementById('old_password').value;
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;

                // Validation
                let hasErrors = false;

                if (!oldPassword) {
                    document.getElementById('old_password_error').textContent =
                        'Your current password is required';
                    hasErrors = true;
                }

                if (!newPassword) {
                    document.getElementById('new_password_error').textContent = 'New password is required';
                    hasErrors = true;
                } else if (newPassword.length < 4) {
                    document.getElementById('new_password_error').textContent =
                        'New password must be at least 4 characters';
                    hasErrors = true;
                } else if (newPassword === oldPassword) {
                    document.getElementById('new_password_error').textContent =
                        'New password must be different from current password';
                    hasErrors = true;
                }

                if (!confirmPassword) {
                    document.getElementById('confirm_password_error').textContent =
                        'Password confirmation is required';
                    hasErrors = true;
                } else if (confirmPassword !== newPassword) {
                    document.getElementById('confirm_password_error').textContent =
                        'Password confirmation must match new password';
                    hasErrors = true;
                }

                if (!hasErrors) {
                    // Form is valid, submit
                    const formData = new FormData(form);

                    fetch("{{ route('change-pass') }}", {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.getElementById('csrf-token').value,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                old_password: oldPassword,
                                new_password: newPassword,
                                confirm_password: confirmPassword
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            const alertsContainer = document.getElementById('alerts-container');

                            if (data.success) {
                                // Show success message
                                alertsContainer.innerHTML = `
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <span>${data.success}</span>
                                </div>
                            `;
                                submitButton.disabled = false;
                                submitButton.innerHTML = '<i class="fas fa-save"></i> Change Password';

                                // Reset form
                                form.reset();
                            } else if (data.error) {
                                submitButton.disabled = false;
                                submitButton.innerHTML = '<i class="fas fa-save"></i> Change Password';
                                // Show error message
                                alertsContainer.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span>${data.error}</span>
                                    </div>
                            `;
                            }
                        })
                        .catch(err => {
                            submitButton.disabled = false;
                            submitButton.innerHTML = '<i class="fas fa-save"></i> Change Password';
                            console.error('Error:', err);
                            const alertsContainer = document.getElementById('alerts-container');
                            alertsContainer.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>An unexpected error occurred. Please try again later.</span>
                            </div>
                            `;
                        });
                } else {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-save"></i> Change Password';
                }
            });
        });
    </script>
</body>

</html>
