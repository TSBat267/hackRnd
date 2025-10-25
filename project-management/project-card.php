<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карточка проекта - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php require_once 'blocks/head.php'; ?>


    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <div class="back-link">
                    <a href="projects.php">← Назад к реестру проектов</a>
                </div>
                <div class="page-title-row">
                    <h1>Волна Коммуникаций</h1>
                    <div class="page-actions">
                        <button class="btn btn-secondary">История изменений</button>
                        <button class="btn btn-primary">Сохранить</button>
                    </div>
                </div>
                <div class="project-meta">
                    <span class="project-id">ID: PRJ-2025-001</span>
                    <span class="project-status status-active">Активный</span>
                    <span class="project-date">Создан: 19.07.2025</span>
                </div>
            </div>
        </div>
    </section>

    <section class="project-card">
        <div class="container">
            <div class="card-tabs">
                <button class="tab-btn active" data-tab="general">Общая информация</button>
                <button class="tab-btn" data-tab="revenue">Выручка</button>
                <button class="tab-btn" data-tab="costs">Затраты</button>
                <button class="tab-btn" data-tab="additional">Дополнительная информация</button>
            </div>

            <div class="tab-content active" id="general">
                <form class="project-form">
                    <div class="form-section">
                        <h3>Общая информация по проекту</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Название организации *</label>
                                <input type="text" value="Альфа-Рост" required>
                            </div>
                            <div class="form-group">
                                <label>ИНН организации *</label>
                                <input type="text" value="123456789012" required>
                            </div>
                            <div class="form-group">
                                <label>Название проекта *</label>
                                <input type="text" value="Волна Коммуникаций" required>
                            </div>
                            <div class="form-group">
                                <label>Услуга *</label>
                                <select required>
                                    <option>Интернет</option>
                                    <option selected>Телефония</option>
                                    <option>Инфобез</option>
                                    <option>Цифровые сервисы</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Тип платежа *</label>
                                <select required>
                                    <option>Инсталляции</option>
                                    <option>Сервисная</option>
                                    <option selected>Разовые</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Этап проекта *</label>
                                <select required>
                                    <option>Лид (10%)</option>
                                    <option selected>Проработка лида (20%)</option>
                                    <option>КП (30%)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Вероятность реализации</label>
                                <input type="text" value="20%" disabled>
                                <small>Заполняется автоматически</small>
                            </div>
                            <div class="form-group">
                                <label>Менеджер *</label>
                                <select required>
                                    <option selected>Иванов И.</option>
                                    <option>Смирнов С.</option>
                                    <option>Кузнецов К.</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3>Дополнительные параметры</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Сегмент бизнеса</label>
                                <select>
                                    <option selected>Средний сегмент</option>
                                    <option>Крупный сегмент</option>
                                    <option>Госсектор</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Год реализации</label>
                                <input type="number" value="2025">
                            </div>
                            <div class="checkbox-group">
                                <label class="checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    Отраслевое решение
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox" checked>
                                    <span class="checkmark"></span>
                                    Принимаемый к прогнозу
                                </label>
                                <label class="checkbox">
                                    <input type="checkbox">
                                    <span class="checkmark"></span>
                                    Реализация через ДЗО
                                </label>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="tab-content" id="revenue">
                <div class="revenue-section">
                    <h3>Информация по выручке проекта</h3>
                    <div class="table-actions">
                        <button class="btn btn-primary">Добавить период</button>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Год</th>
                                <th>Месяц</th>
                                <th>Сумма</th>
                                <th>Статус начисления</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2025</td>
                                <td>Январь</td>
                                <td>₽484,100</td>
                                <td>
                                    <select>
                                        <option>начислена</option>
                                        <option>прогнозное начисление</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="btn-icon btn-danger">Удалить</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Остальные вкладки -->
        </div>
    </section>

    <footer>
        <!-- Футер -->
    </footer>

    <script>
        // Табы
        document.querySelectorAll('.tab-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Убираем активный класс у всех кнопок и контента
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                
                // Добавляем активный класс текущей кнопке и соответствующему контенту
                button.classList.add('active');
                document.getElementById(button.dataset.tab).classList.add('active');
            });
        });
    </script>
</body>
</html>