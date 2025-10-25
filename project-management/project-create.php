<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание проекта - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .project-create {
            padding: 2rem 0;
        }

        .create-header {
            background: linear-gradient(135deg, #f7f0ff 0%, #ede6ff 100%);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .create-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            gap: 2rem;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.5rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            cursor: pointer;
            transition: var(--transition);
        }

        .step.active {
            background: var(--primary);
            color: white;
        }

        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--light);
            font-weight: 600;
        }

        .step.active .step-number {
            background: rgba(255, 255, 255, 0.2);
        }

        .step-title {
            font-weight: 500;
        }

        .create-form {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .form-section {
            padding: 2rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--dark);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        .form-label .required {
            color: var(--danger);
        }

        .form-input, .form-select, .form-textarea {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: var(--transition);
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(119, 0, 255, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
        }

        .checkbox-label input {
            display: none;
        }

        .checkmark {
            width: 20px;
            height: 20px;
            border: 2px solid var(--gray-light);
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .checkbox-label input:checked + .checkmark {
            background: var(--primary);
            border-color: var(--primary);
        }

        .checkbox-label input:checked + .checkmark::after {
            content: '✓';
            color: white;
            font-size: 14px;
        }

        .finance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .finance-table th,
        .finance-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        .finance-table th {
            background: var(--light);
            font-weight: 600;
            color: var(--dark);
        }

        .table-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: var(--light);
            border-top: 1px solid var(--gray-light);
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
        }

        .form-progress {
            flex: 1;
            max-width: 300px;
        }

        .progress-bar {
            height: 6px;
            background: var(--gray-light);
            border-radius: 3px;
            margin-bottom: 0.5rem;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--primary);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 0.875rem;
            color: var(--gray);
            text-align: center;
        }

        .conditional-field {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .conditional-field.show {
            display: block;
        }

        .history-preview {
            background: var(--light);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .history-item {
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }

        .history-change {
            font-weight: 500;
        }

        .auto-calculated {
            background: var(--light);
            color: var(--gray);
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

        @media (max-width: 768px) {
            .create-steps {
                flex-direction: column;
                gap: 1rem;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            .form-progress {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once 'blocks/head.php'; ?>


    <!-- Create Header -->
    <section class="create-header">
        <div class="container">
            <div class="page-header-content">
                <div class="back-link">
                    <a href="projects.php">← Назад к реестру проектов</a>
                </div>
                <h1>Создание нового проекта</h1>
                <p>Заполните информацию о новом проекте коммерческого подразделения</p>
            </div>
        </div>
    </section>

    <!-- Create Steps -->
    <section class="project-create">
        <div class="container">
            <div class="create-steps">
                <div class="step active" data-step="general">
                    <div class="step-number">1</div>
                    <div class="step-title">Общая информация</div>
                </div>
                <div class="step" data-step="finance">
                    <div class="step-number">2</div>
                    <div class="step-title">Финансы</div>
                </div>
                <div class="step" data-step="additional">
                    <div class="step-number">3</div>
                    <div class="step-title">Дополнительно</div>
                </div>
            </div>

            <form class="create-form" id="project-form">
                <!-- Общая информация -->
                <div class="form-section active" data-section="general">
                    <h2 class="section-title">Общая информация по проекту</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Название организации <span class="required">*</span></label>
                            <input type="text" class="form-input" name="organization_name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">ИНН организации <span class="required">*</span></label>
                            <input type="text" class="form-input" name="organization_inn" pattern="[0-9]{10,12}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Название проекта <span class="required">*</span></label>
                            <input type="text" class="form-input" name="project_name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Услуга <span class="required">*</span></label>
                            <select class="form-select" name="service_id" required>
                                <option value="">Выберите услугу</option>
                                <option value="1">Интернет</option>
                                <option value="2">Телефония</option>
                                <option value="3">Инфобез</option>
                                <option value="4">Цифровые сервисы</option>
                                <option value="5">Облачные сервисы</option>
                                <option value="6">Отраслевые решения</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Тип платежа <span class="required">*</span></label>
                            <select class="form-select" name="payment_type_id" required>
                                <option value="">Выберите тип платежа</option>
                                <option value="1">Инсталляции</option>
                                <option value="2">Сервисная</option>
                                <option value="3">Оборудование</option>
                                <option value="4">Разовые</option>
                                <option value="5">Интеграционные проекты</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Этап проекта <span class="required">*</span></label>
                            <select class="form-select" name="stage_id" required>
                                <option value="">Выберите этап</option>
                                <option value="1">1. Лид (10%)</option>
                                <option value="2">2. Проработка лида (20%)</option>
                                <option value="3">3. КП (30%)</option>
                                <option value="4">4. Пилот (40%)</option>
                                <option value="5">5. Выделение финансирования (40%)</option>
                                <option value="6">6. Закупка/торги (50%)</option>
                                <option value="7">7. Заключение Д Д (70%)</option>
                                <option value="8">8. Заключение РД (80%)</option>
                                <option value="9">9. Реализация (90%)</option>
                                <option value="10">10. Успех (100%)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Вероятность реализации</label>
                            <input type="text" class="form-input auto-calculated" name="probability" readonly>
                            <small>Заполняется автоматически на основе этапа</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Менеджер <span class="required">*</span></label>
                            <select class="form-select" name="manager_id" required>
                                <option value="">Выберите менеджера</option>
                                <option value="1">Иванов И.И.</option>
                                <option value="2">Смирнов С.С.</option>
                                <option value="3">Кузнецов К.К.</option>
                                <option value="4">Попов П.П.</option>
                                <option value="5">Васильев В.В.</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Сегмент бизнеса</label>
                            <select class="form-select" name="segment_id">
                                <option value="">Выберите сегмент</option>
                                <option value="1">Крупный сегмент</option>
                                <option value="2">Госсектор</option>
                                <option value="3">Средний сегмент</option>
                                <option value="4">Малые предприятия</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Год реализации</label>
                            <input type="number" class="form-input" name="implementation_year" min="2023" max="2030" value="2025">
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_industry_solution">
                            <span class="checkmark"></span>
                            Отраслевое решение
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_forecast_accepted">
                            <span class="checkmark"></span>
                            Принимаемый к прогнозу
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_dzo_implementation">
                            <span class="checkmark"></span>
                            Реализация через ДЗО
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="needs_management_control">
                            <span class="checkmark"></span>
                            Требуется контроль статуса на уровне руководства
                        </label>
                    </div>

                    <!-- Условные поля -->
                    <div class="conditional-field" id="industry-fields">
                        <div class="form-grid" style="margin-top: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Отраслевой менеджер</label>
                                <select class="form-select" name="industry_manager_id">
                                    <option value="">Выберите отраслевого менеджера</option>
                                    <option value="1">Петров А.А.</option>
                                    <option value="2">Сидоров Б.Б.</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Номер проекта</label>
                                <input type="text" class="form-input" name="project_number" placeholder="Формат: XXX/XXX/XXXX">
                            </div>
                        </div>
                    </div>

                    <div class="conditional-field" id="evaluation-field">
                        <div class="form-group" style="margin-top: 1.5rem; max-width: 300px;">
                            <label class="form-label">Принимаемый к оценке</label>
                            <select class="form-select" name="accepted_for_evaluation_id">
                                <option value="">Выберите статус</option>
                                <option value="1">ОЦЕНКА</option>
                                <option value="2">ПКМ</option>
                                <option value="3">ОТТОК</option>
                                <option value="4">ДАШ_ПКМ</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Финансовая информация -->
                <div class="form-section" data-section="finance">
                    <h2 class="section-title">Информация по выручке проекта</h2>
                    
                    <div class="table-actions">
                        <button type="button" class="btn btn-primary" id="add-revenue">Добавить период</button>
                    </div>

                    <table class="finance-table" id="revenue-table">
                        <thead>
                            <tr>
                                <th>Год</th>
                                <th>Месяц</th>
                                <th>Сумма (руб.)</th>
                                <th>Статус начисления</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="revenue-body">
                            <!-- Данные будут добавляться динамически -->
                        </tbody>
                    </table>

                    <h2 class="section-title" style="margin-top: 2rem;">Информация по затратам проекта</h2>
                    
                    <div class="table-actions">
                        <button type="button" class="btn btn-primary" id="add-cost">Добавить период</button>
                    </div>

                    <table class="finance-table" id="cost-table">
                        <thead>
                            <tr>
                                <th>Год</th>
                                <th>Месяц</th>
                                <th>Сумма (руб.)</th>
                                <th>Вид затрат</th>
                                <th>Статус отражения</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody id="cost-body">
                            <!-- Данные будут добавляться динамически -->
                        </tbody>
                    </table>
                </div>

                <!-- Дополнительная информация -->
                <div class="form-section" data-section="additional">
                    <h2 class="section-title">Дополнительная информация</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Текущий статус по проекту</label>
                        <textarea class="form-textarea" name="current_status" placeholder="Опишите текущий статус проекта (максимум 1000 символов)" maxlength="1000"></textarea>
                        <small class="char-count">0/1000 символов</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Что сделано за период</label>
                        <textarea class="form-textarea" name="period_achievements" placeholder="Опишите выполненные работы за отчетный период (максимум 1000 символов)" maxlength="1000"></textarea>
                        <small class="char-count">0/1000 символов</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Планы на следующий период</label>
                        <textarea class="form-textarea" name="next_period_plans" placeholder="Опишите планы на следующий отчетный период (максимум 1000 символов)" maxlength="1000"></textarea>
                        <small class="char-count">0/1000 символов</small>
                    </div>

                    <div class="history-preview">
                        <h3 style="margin-bottom: 1rem;">История изменений</h3>
                        <div class="history-item">
                            <div class="history-meta">
                                <span>Система</span>
                                <span>Только что</span>
                            </div>
                            <div class="history-change">Карточка проекта создана</div>
                        </div>
                    </div>
                </div>

                <!-- Действия формы -->
                <div class="form-actions">
                    <div class="action-buttons">
                        <button type="button" class="btn btn-secondary" id="prev-step">Назад</button>
                        <button type="button" class="btn btn-primary" id="next-step">Далее</button>
                    </div>
                    
                    <div class="form-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-fill" style="width: 33%"></div>
                        </div>
                        <div class="progress-text">Шаг 1 из 3</div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="submit-btn" style="display: none;">Создать проект</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Ростелеком</h3>
                    <ul>
                        <li><a href="#">О компании</a></li>
                        <li><a href="#">Новости</a></li>
                        <li><a href="#">Карьера</a></li>
                        <li><a href="#">Контакты</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Продукты</h3>
                    <ul>
                        <li><a href="#">Интернет</a></li>
                        <li><a href="#">Телефония</a></li>
                        <li><a href="#">Инфобезопасность</a></li>
                        <li><a href="#">Облачные сервисы</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Поддержка</h3>
                    <ul>
                        <li><a href="#">Помощь</a></li>
                        <li><a href="#">Документация</a></li>
                        <li><a href="#">Форум</a></li>
                        <li><a href="#">Сообщить о проблеме</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Контакты</h3>
                    <ul>
                        <li>8 800 100 0 800</li>
                        <li>support@rtk.ru</li>
                        <li>г. Москва, ул. Примерная, д. 1</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="copyright">© 2023 ПАО «Ростелеком». Все права защищены.</div>
                <div class="social-links">
                    <!-- Социальные ссылки -->
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Текущий шаг
        let currentStep = 1;
        const totalSteps = 3;

        // Элементы DOM
        const steps = document.querySelectorAll('.step');
        const sections = document.querySelectorAll('.form-section');
        const prevBtn = document.getElementById('prev-step');
        const nextBtn = document.getElementById('next-step');
        const submitBtn = document.getElementById('submit-btn');
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.querySelector('.progress-text');

        // Переключение шагов
        function updateStep(newStep) {
            currentStep = newStep;
            
            // Обновление шагов
            steps.forEach((step, index) => {
                if (index + 1 === currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });
            
            // Обновление секций
            sections.forEach((section, index) => {
                if (index + 1 === currentStep) {
                    section.classList.add('active');
                } else {
                    section.classList.remove('active');
                }
            });
            
            // Обновление прогресса
            const progress = (currentStep / totalSteps) * 100;
            progressFill.style.width = `${progress}%`;
            progressText.textContent = `Шаг ${currentStep} из ${totalSteps}`;
            
            // Обновление кнопок
            prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
            nextBtn.style.display = currentStep === totalSteps ? 'none' : 'block';
            submitBtn.style.display = currentStep === totalSteps ? 'block' : 'none';
        }

        // Обработчики кнопок
        nextBtn.addEventListener('click', function() {
            if (validateStep(currentStep)) {
                updateStep(currentStep + 1);
            }
        });

        prevBtn.addEventListener('click', function() {
            updateStep(currentStep - 1);
        });

        // Валидация шага
        function validateStep(step) {
            const currentSection = document.querySelector(`[data-section]:nth-child(${step})`);
            const requiredFields = currentSection.querySelectorAll('[required]');
            
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = 'var(--danger)';
                    isValid = false;
                } else {
                    field.style.borderColor = '';
                }
            });
            
            if (!isValid) {
                alert('Пожалуйста, заполните все обязательные поля');
            }
            
            return isValid;
        }

        // Автоматическое вычисление вероятности
        const stageSelect = document.querySelector('select[name="stage_id"]');
        const probabilityInput = document.querySelector('input[name="probability"]');
        
        const stageProbabilities = {
            '1': '10%',
            '2': '20%',
            '3': '30%',
            '4': '40%',
            '5': '40%',
            '6': '50%',
            '7': '70%',
            '8': '80%',
            '9': '90%',
            '10': '100%'
        };
        
        stageSelect.addEventListener('change', function() {
            const probability = stageProbabilities[this.value] || '';
            probabilityInput.value = probability;
        });

        // Условные поля
        const industryCheckbox = document.querySelector('input[name="is_industry_solution"]');
        const forecastCheckbox = document.querySelector('input[name="is_forecast_accepted"]');
        const industryFields = document.getElementById('industry-fields');
        const evaluationField = document.getElementById('evaluation-field');

        industryCheckbox.addEventListener('change', function() {
            if (this.checked) {
                industryFields.classList.add('show');
            } else {
                industryFields.classList.remove('show');
            }
        });

        forecastCheckbox.addEventListener('change', function() {
            if (this.checked) {
                evaluationField.classList.add('show');
            } else {
                evaluationField.classList.remove('show');
            }
        });

        // Подсчет символов в textarea
        const textareas = document.querySelectorAll('textarea');
        textareas.forEach(textarea => {
            const charCount = textarea.nextElementSibling;
            
            textarea.addEventListener('input', function() {
                const count = this.value.length;
                charCount.textContent = `${count}/1000 символов`;
                
                if (count > 1000) {
                    charCount.style.color = 'var(--danger)';
                } else {
                    charCount.style.color = 'var(--gray)';
                }
            });
        });

        // Управление финансовыми таблицами
        let revenueCounter = 0;
        let costCounter = 0;

        document.getElementById('add-revenue').addEventListener('click', function() {
            revenueCounter++;
            const tbody = document.getElementById('revenue-body');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select class="form-input" name="revenue[${revenueCounter}][year]">
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025" selected>2025</option>
                        <option value="2026">2026</option>
                    </select>
                </td>
                <td>
                    <select class="form-input" name="revenue[${revenueCounter}][month]">
                        ${Array.from({length: 12}, (_, i) => 
                            `<option value="${i + 1}">${['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'][i]}</option>`
                        ).join('')}
                    </select>
                </td>
                <td><input type="number" class="form-input" name="revenue[${revenueCounter}][amount]" placeholder="0.00" step="0.01"></td>
                <td>
                    <select class="form-input" name="revenue[${revenueCounter}][status]">
                        <option value="1">начислена</option>
                        <option value="2">прогнозное начисление</option>
                        <option value="3">начисление планируется</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">Удалить</button></td>
            `;
            tbody.appendChild(row);
        });

        document.getElementById('add-cost').addEventListener('click', function() {
            costCounter++;
            const tbody = document.getElementById('cost-body');
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select class="form-input" name="cost[${costCounter}][year]">
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025" selected>2025</option>
                        <option value="2026">2026</option>
                    </select>
                </td>
                <td>
                    <select class="form-input" name="cost[${costCounter}][month]">
                        ${Array.from({length: 12}, (_, i) => 
                            `<option value="${i + 1}">${['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'][i]}</option>`
                        ).join('')}
                    </select>
                </td>
                <td><input type="number" class="form-input" name="cost[${costCounter}][amount]" placeholder="0.00" step="0.01"></td>
                <td>
                    <select class="form-input" name="cost[${costCounter}][type]">
                        <option value="1">Прямые</option>
                        <option value="2">Коммерческие</option>
                        <option value="3">РСД</option>
                        <option value="4">Штрафы</option>
                    </select>
                </td>
                <td>
                    <select class="form-input" name="cost[${costCounter}][status]">
                        <option value="1">начислены</option>
                        <option value="2">создан резерв</option>
                        <option value="3">отражение планируется</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">Удалить</button></td>
            `;
            tbody.appendChild(row);
        });

        // Отправка формы
        document.getElementById('project-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateStep(currentStep)) {
                // Сбор данных формы
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                
                // Добавление финансовых данных
                const revenueData = [];
                const costData = [];
                
                document.querySelectorAll('#revenue-body tr').forEach((row, index) => {
                    const inputs = row.querySelectorAll('select, input');
                    revenueData.push({
                        year: inputs[0].value,
                        month: inputs[1].value,
                        amount: inputs[2].value,
                        status: inputs[3].value
                    });
                });
                
                document.querySelectorAll('#cost-body tr').forEach((row, index) => {
                    const inputs = row.querySelectorAll('select, input');
                    costData.push({
                        year: inputs[0].value,
                        month: inputs[1].value,
                        amount: inputs[2].value,
                        type: inputs[3].value,
                        status: inputs[4].value
                    });
                });
                
                data.revenue = revenueData;
                data.cost = costData;
                data.created_by = 1; // ID текущего пользователя
                data.creation_date = new Date().toISOString();
                
                console.log('Данные для отправки:', data);
                
                // Имитация отправки на сервер
                setTimeout(() => {
                    alert('Проект успешно создан!');
                    window.location.href = 'projects.php';
                }, 1000);
            }
        });

        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            updateStep(1);
        });
    </script>
</body>
</html>