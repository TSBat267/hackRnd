<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Создание проекта - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* ... существующие стили ... */
    </style>
</head>
<body>
    <!-- Header -->
    <?php 
    require_once 'blocks/head.php';
    require_once 'database.php';
    
    // Загрузка данных из БД для выпадающих списков
    try {
        $conn = Database::connect();
        
        // Загрузка услуг
        $services_result = pg_query($conn, "SELECT id, name FROM dictionaries WHERE type = 'service' AND is_active = true ORDER BY sort_order");
        $services = pg_fetch_all($services_result) ?: [];
        
        // Загрузка типов платежей
        $payment_types_result = pg_query($conn, "SELECT id, name FROM dictionaries WHERE type = 'payment_type' AND is_active = true ORDER BY sort_order");
        $payment_types = pg_fetch_all($payment_types_result) ?: [];
        
        // Загрузка этапов проекта
        $stages_result = pg_query($conn, "SELECT id, name, probability FROM dictionaries WHERE type = 'stage' AND is_active = true ORDER BY sort_order");
        $stages = pg_fetch_all($stages_result) ?: [];
        
        // Загрузка менеджеров
        $managers_result = pg_query($conn, "SELECT id, full_name FROM users WHERE is_active = true ORDER BY full_name");
        $managers = pg_fetch_all($managers_result) ?: [];
        
        // Загрузка сегментов бизнеса
        $segments_result = pg_query($conn, "SELECT id, name FROM dictionaries WHERE type = 'segment' AND is_active = true ORDER BY sort_order");
        $segments = pg_fetch_all($segments_result) ?: [];
        
        // Загрузка статусов оценки
        $evaluation_statuses_result = pg_query($conn, "SELECT id, name FROM dictionaries WHERE type = 'evaluation_status' AND is_active = true ORDER BY sort_order");
        $evaluation_statuses = pg_fetch_all($evaluation_statuses_result) ?: [];
        
        // Загрузка отраслевых менеджеров
        $industry_managers_result = pg_query($conn, "SELECT id, full_name FROM users WHERE is_active = true AND id IN (SELECT DISTINCT industry_manager_id FROM projects WHERE industry_manager_id IS NOT NULL) ORDER BY full_name");
        $industry_managers = pg_fetch_all($industry_managers_result) ?: [];
        
    } catch (Exception $e) {
        error_log("Error loading form data: " . $e->getMessage());
        $services = $payment_types = $stages = $managers = $segments = $evaluation_statuses = $industry_managers = [];
    }
    ?>

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

            <form class="create-form" id="project-form" action="project-save.php" method="POST">
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
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>"><?= htmlspecialchars($service['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Тип платежа <span class="required">*</span></label>
                            <select class="form-select" name="payment_type_id" required>
                                <option value="">Выберите тип платежа</option>
                                <?php foreach ($payment_types as $payment_type): ?>
                                    <option value="<?= $payment_type['id'] ?>"><?= htmlspecialchars($payment_type['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Этап проекта <span class="required">*</span></label>
                            <select class="form-select" name="stage_id" required>
                                <option value="">Выберите этап</option>
                                <?php foreach ($stages as $stage): ?>
                                    <option value="<?= $stage['id'] ?>" data-probability="<?= $stage['probability'] ?>">
                                        <?= htmlspecialchars($stage['name']) ?> (<?= round($stage['probability'] * 100) ?>%)
                                    </option>
                                <?php endforeach; ?>
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
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['full_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Сегмент бизнеса</label>
                            <select class="form-select" name="segment_id">
                                <option value="">Выберите сегмент</option>
                                <?php foreach ($segments as $segment): ?>
                                    <option value="<?= $segment['id'] ?>"><?= htmlspecialchars($segment['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Год реализации</label>
                            <input type="number" class="form-input" name="implementation_year" min="2023" max="2030" value="<?= date('Y') ?>">
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
                                    <?php foreach ($industry_managers as $manager): ?>
                                        <option value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['full_name']) ?></option>
                                    <?php endforeach; ?>
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
                                <?php foreach ($evaluation_statuses as $status): ?>
                                    <option value="<?= $status['id'] ?>"><?= htmlspecialchars($status['name']) ?></option>
                                <?php endforeach; ?>
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
        <!-- ... существующий футер ... -->
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
        
        stageSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const probability = selectedOption.getAttribute('data-probability');
            if (probability) {
                probabilityInput.value = Math.round(probability * 100) + '%';
            } else {
                probabilityInput.value = '';
            }
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
                        <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                        <option value="<?= date('Y') + 1 ?>"><?= date('Y') + 1 ?></option>
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
                        <option value="35">начислена</option>
                        <option value="36">прогнозное начисление</option>
                        <option value="37">начисление планируется</option>
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
                        <option value="<?= date('Y') ?>" selected><?= date('Y') ?></option>
                        <option value="<?= date('Y') + 1 ?>"><?= date('Y') + 1 ?></option>
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
                        <option value="31">Прямые</option>
                        <option value="32">Коммерческие</option>
                        <option value="33">РСД</option>
                        <option value="34">Штрафы</option>
                    </select>
                </td>
                <td>
                    <select class="form-input" name="cost[${costCounter}][status]">
                        <option value="38">начислены</option>
                        <option value="39">создан резерв</option>
                        <option value="40">отражение планируется</option>
                    </select>
                </td>
                <td><button type="button" class="btn btn-danger" onclick="this.closest('tr').remove()">Удалить</button></td>
            `;
            tbody.appendChild(row);
        });

        // Отправка формы
        document.getElementById('project-form').addEventListener('submit', function(e) {
            if (!validateStep(currentStep)) {
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля');
            }
        });

        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            updateStep(1);
        });
    </script>
</body>
</html>