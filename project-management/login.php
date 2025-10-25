<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f7f0ff 0%, #ede6ff 100%);
            padding: 2rem;
        }

        .auth-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 1rem;
        }

        .auth-logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-weight: bold;
            font-size: 1.2rem;
        }

        .auth-logo-text {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .auth-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .auth-content {
            padding: 2rem;
        }

        .auth-tabs {
            display: flex;
            margin-bottom: 2rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .auth-tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            background: none;
            border: none;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            color: var(--gray);
            cursor: pointer;
            transition: var(--transition);
            border-bottom: 3px solid transparent;
        }

        .auth-tab.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .auth-form {
            display: none;
        }

        .auth-form.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(119, 0, 255, 0.1);
        }

        .password-input {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        .auth-button {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-bottom: 1.5rem;
        }

        .auth-button:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .auth-button:disabled {
            background: var(--gray-light);
            cursor: not-allowed;
            transform: none;
        }

        .auth-divider {
            text-align: center;
            margin: 2rem 0;
            position: relative;
            color: var(--gray);
        }

        .auth-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gray-light);
        }

        .auth-divider span {
            background: white;
            padding: 0 1rem;
            position: relative;
        }

        .social-auth {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .social-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            background: white;
            cursor: pointer;
            transition: var(--transition);
            font-family: 'Inter', sans-serif;
            font-size: 0.875rem;
        }

        .social-button:hover {
            border-color: var(--primary);
            transform: translateY(-1px);
        }

        .auth-footer {
            text-align: center;
            color: var(--gray);
            font-size: 0.875rem;
        }

        .auth-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .two-factor-section {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: var(--light);
            border-radius: var(--radius);
            border-left: 4px solid var(--primary);
        }

        .two-factor-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .two-factor-description {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1rem;
        }

        .code-inputs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .code-input {
            width: 40px;
            height: 40px;
            text-align: center;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .code-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(119, 0, 255, 0.1);
        }

        .resend-code {
            font-size: 0.875rem;
            color: var(--primary);
            cursor: pointer;
        }

        .resend-code:hover {
            text-decoration: underline;
        }

        .resend-code.disabled {
            color: var(--gray);
            cursor: not-allowed;
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-bar {
            height: 4px;
            background: var(--gray-light);
            border-radius: 2px;
            margin-bottom: 0.25rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-text {
            font-size: 0.75rem;
            color: var(--gray);
        }

        .password-requirements {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: var(--gray);
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-bottom: 0.25rem;
        }

        .requirement.met {
            color: var(--success);
        }

        .requirement.met::before {
            content: '✓';
            color: var(--success);
        }

        .agreement {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 1.5rem;
        }

        .agreement a {
            color: var(--primary);
            text-decoration: none;
        }

        .agreement a:hover {
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 1rem;
            }
            
            .auth-content {
                padding: 1.5rem;
            }
            
            .social-auth {
                grid-template-columns: 1fr;
            }
            
            .form-options {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <div class="auth-logo-icon">РТК</div>
                    <div class="auth-logo-text">Управление проектами</div>
                </div>
                <div class="auth-subtitle">Система управления проектами коммерческого подразделения</div>
            </div>

            <div class="auth-content">
                <div class="auth-tabs">
                    <button class="auth-tab active" data-tab="login">Вход</button>
                    <button class="auth-tab" data-tab="register">Регистрация</button>
                </div>

                <!-- Форма входа -->
                <form class="auth-form active" id="login-form">
                    <div class="form-group">
                        <label class="form-label">Email или логин</label>
                        <input type="text" class="form-input" placeholder="Введите ваш email или логин" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Пароль</label>
                        <div class="password-input">
                            <input type="password" class="form-input" id="login-password" placeholder="Введите ваш пароль" required>
                            <button type="button" class="password-toggle" data-target="login-password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox">
                            <span>Запомнить меня</span>
                        </label>
                        <a href="#" class="forgot-password">Забыли пароль?</a>
                    </div>

                    <button type="submit" class="auth-button">Войти в систему</button>

                    <div class="auth-divider">
                        <span>или войдите через</span>
                    </div>

                    <div class="social-auth">
                        <button type="button" class="social-button">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M22 12C22 6.48 17.52 2 12 2C6.48 2 2 6.48 2 12C2 16.84 5.44 20.87 10 21.8V15H8V12H10V9.5C10 7.57 11.57 6 13.5 6H16V9H14C13.45 9 13 9.45 13 10V12H16V15H13V21.95C18.05 21.45 22 17.19 22 12Z" fill="#1877F2"/>
                            </svg>
                            Facebook
                        </button>
                        <button type="button" class="social-button">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M22.56 12.25C22.56 11.47 22.49 10.72 22.36 10H12V14.26H17.92C17.66 15.63 16.88 16.79 15.71 17.57V20.34H19.28C21.36 18.42 22.56 15.6 22.56 12.25Z" fill="#4285F4"/>
                                <path d="M12 23C14.97 23 17.46 22.02 19.28 20.34L15.71 17.57C14.73 18.23 13.48 18.63 12 18.63C9.14 18.63 6.72 16.7 5.85 14.1H2.18V16.94C4 20.53 7.7 23 12 23Z" fill="#34A853"/>
                                <path d="M5.85 14.09C5.62 13.43 5.49 12.73 5.49 12C5.49 11.27 5.62 10.57 5.85 9.91V7.07H2.18C1.43 8.55 1 10.22 1 12C1 13.78 1.43 15.45 2.18 16.93L5.85 14.09Z" fill="#FBBC05"/>
                                <path d="M12 5.38C13.62 5.38 15.06 5.94 16.21 7.02L19.36 3.87C17.45 2.09 14.97 1 12 1C7.7 1 4 3.47 2.18 7.07L5.85 9.91C6.72 7.31 9.14 5.38 12 5.38Z" fill="#EA4335"/>
                            </svg>
                            Google
                        </button>
                    </div>

                    <div class="auth-footer">
                        Нет аккаунта? <a href="#" class="switch-to-register">Зарегистрироваться</a>
                    </div>
                </form>

                <!-- Форма регистрации -->
                <form class="auth-form" id="register-form">
                    <div class="form-group">
                        <label class="form-label">ФИО</label>
                        <input type="text" class="form-input" placeholder="Введите ваше полное имя" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" placeholder="Введите ваш email" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Логин</label>
                        <input type="text" class="form-input" placeholder="Придумайте логин" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Пароль</label>
                        <div class="password-input">
                            <input type="password" class="form-input" id="register-password" placeholder="Придумайте пароль" required>
                            <button type="button" class="password-toggle" data-target="register-password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strength-fill" style="width: 0%; background: var(--danger);"></div>
                            </div>
                            <div class="strength-text" id="strength-text">Слабый пароль</div>
                        </div>
                        <div class="password-requirements">
                            <div class="requirement" id="req-length">Минимум 8 символов</div>
                            <div class="requirement" id="req-uppercase">Заглавные и строчные буквы</div>
                            <div class="requirement" id="req-numbers">Цифры</div>
                            <div class="requirement" id="req-special">Специальные символы</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Подтверждение пароля</label>
                        <div class="password-input">
                            <input type="password" class="form-input" id="confirm-password" placeholder="Повторите пароль" required>
                            <button type="button" class="password-toggle" data-target="confirm-password">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Должность</label>
                        <select class="form-input" required>
                            <option value="">Выберите должность</option>
                            <option value="manager">Менеджер проектов</option>
                            <option value="analyst">Аналитик</option>
                            <option value="admin">Администратор</option>
                            <option value="director">Руководитель</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Отдел</label>
                        <input type="text" class="form-input" placeholder="Введите ваш отдел" required>
                    </div>

                    <div class="agreement">
                        Нажимая "Зарегистрироваться", вы соглашаетесь с 
                        <a href="#">политикой конфиденциальности</a> и 
                        <a href="#">условиями использования</a> системы
                    </div>

                    <button type="submit" class="auth-button" id="register-button" disabled>Зарегистрироваться</button>

                    <div class="auth-footer">
                        Уже есть аккаунт? <a href="#" class="switch-to-login">Войти</a>
                    </div>
                </form>

                <!-- Двухфакторная аутентификация -->
                <form class="auth-form" id="two-factor-form">
                    <div class="two-factor-section">
                        <div class="two-factor-title">Двухфакторная аутентификация</div>
                        <div class="two-factor-description">
                            Для завершения входа введите 6-значный код, отправленный на ваш email
                        </div>
                        
                        <div class="code-inputs">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]">
                            <input type="text" class="code-input" maxlength="1" pattern="[0-9]">
                        </div>

                        <div class="resend-code" id="resend-code">
                            Отправить код повторно (60)
                        </div>
                    </div>

                    <button type="submit" class="auth-button">Подтвердить</button>

                    <div class="auth-footer">
                        <a href="#" class="switch-to-login">Вернуться к входу</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Переключение между вкладками
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                
                // Активируем выбранную вкладку
                document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                // Показываем соответствующую форму
                document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
                document.getElementById(`${targetTab}-form`).classList.add('active');
            });
        });

        // Переключение между формами через ссылки
        document.querySelector('.switch-to-register').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('[data-tab="register"]').click();
        });

        document.querySelector('.switch-to-login').addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('[data-tab="login"]').click();
        });

        // Показать/скрыть пароль
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('svg');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24" stroke="currentColor" stroke-width="2"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/>';
                } else {
                    passwordInput.type = 'password';
                    icon.innerHTML = '<path d="M1 12C1 12 5 4 12 4C19 4 23 12 23 12C23 12 19 20 12 20C5 20 1 12 1 12Z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/>';
                }
            });
        });

        // Проверка сложности пароля
        const passwordInput = document.getElementById('register-password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        const registerButton = document.getElementById('register-button');

        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            // Проверка требований
            const hasMinLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /\d/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Обновление индикаторов требований
            document.getElementById('req-length').classList.toggle('met', hasMinLength);
            document.getElementById('req-uppercase').classList.toggle('met', hasUpperCase && hasLowerCase);
            document.getElementById('req-numbers').classList.toggle('met', hasNumbers);
            document.getElementById('req-special').classList.toggle('met', hasSpecial);
            
            // Расчет силы пароля
            if (hasMinLength) strength += 25;
            if (hasUpperCase && hasLowerCase) strength += 25;
            if (hasNumbers) strength += 25;
            if (hasSpecial) strength += 25;
            
            // Обновление индикатора силы
            strengthFill.style.width = `${strength}%`;
            
            if (strength < 50) {
                strengthFill.style.background = 'var(--danger)';
                strengthText.textContent = 'Слабый пароль';
            } else if (strength < 75) {
                strengthFill.style.background = 'var(--warning)';
                strengthText.textContent = 'Средний пароль';
            } else {
                strengthFill.style.background = 'var(--success)';
                strengthText.textContent = 'Сильный пароль';
            }
            
            // Проверка подтверждения пароля
            checkPasswordMatch();
        });

        // Проверка совпадения паролей
        document.getElementById('confirm-password').addEventListener('input', checkPasswordMatch);

        function checkPasswordMatch() {
            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const passwordMatch = password === confirmPassword && password.length > 0;
            
            registerButton.disabled = !passwordMatch;
        }

        // Обработка ввода кода двухфакторной аутентификации
        const codeInputs = document.querySelectorAll('.code-input');
        codeInputs.forEach((input, index) => {
            input.addEventListener('input', function() {
                if (this.value.length === 1 && index < codeInputs.length - 1) {
                    codeInputs[index + 1].focus();
                }
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                    codeInputs[index - 1].focus();
                }
            });
        });

        // Таймер для повторной отправки кода
        let countdown = 60;
        const resendElement = document.getElementById('resend-code');
        
        function updateResendTimer() {
            if (countdown > 0) {
                resendElement.textContent = `Отправить код повторно (${countdown})`;
                resendElement.classList.add('disabled');
                countdown--;
                setTimeout(updateResendTimer, 1000);
            } else {
                resendElement.textContent = 'Отправить код повторно';
                resendElement.classList.remove('disabled');
            }
        }

        resendElement.addEventListener('click', function() {
            if (!this.classList.contains('disabled')) {
                countdown = 60;
                updateResendTimer();
                // Здесь будет запрос на сервер для повторной отправки кода
            }
        });

        // Имитация успешного входа
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // В реальном приложении здесь будет запрос к серверу
            document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
            document.getElementById('two-factor-form').classList.add('active');
            updateResendTimer();
        });

        document.getElementById('two-factor-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // В реальном приложении здесь будет проверка кода
            window.location.href = 'index.php';
        });

        document.getElementById('register-form').addEventListener('submit', function(e) {
            e.preventDefault();
            // В реальном приложении здесь будет отправка данных регистрации
            alert('Регистрация успешно завершена! Проверьте вашу почту для подтверждения.');
            document.querySelector('[data-tab="login"]').click();
        });

        // Инициализация таймера при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            updateResendTimer();
        });
    </script>
</body>
</html>