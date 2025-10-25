<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ростелеком - Управление проектами</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Дополнительные стили для интерактивных элементов */
        .project-row {
            cursor: pointer;
            transition: var(--transition);
        }

        .project-row:hover {
            background: #f8faff !important;
            transform: translateX(5px);
        }

        .stat-card {
            cursor: pointer;
        }

        .stat-card:hover .stat-value {
            color: var(--primary);
        }

        .chart-card {
            position: relative;
            overflow: hidden;
        }

        .chart-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(119, 0, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            opacity: 0;
            transition: var(--transition);
        }

        .chart-card:hover .chart-overlay {
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once 'header.php'; ?>


    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Управление проектами коммерческого подразделения</h1>
            <p>Единая платформа для сбора, обработки и анализа информации по количеству и качеству проектов с прогностическо-аналитическим модулем</p>
            <div class="hero-buttons">
                <a href="project-card.html" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Создать проект
                </a>
                <a href="analytics.html" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Смотреть аналитику
                </a>
            </div>
        </div>
    </section>

    <!-- Dashboard -->
    <section class="dashboard">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Обзор проектов</h2>
                <div class="filters">
                    <select onchange="location.href='projects.html'">
                        <option>Все проекты</option>
                        <option>Активные</option>
                        <option>Завершенные</option>
                        <option>На паузе</option>
                    </select>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card" onclick="location.href='projects.html'">
                    <div class="stat-value">142</div>
                    <div class="stat-label">Всего проектов</div>
                    <div class="stat-trend trend-up">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        +12% с прошлого месяца
                    </div>
                </div>
                <div class="stat-card" onclick="location.href='analytics.html'">
                    <div class="stat-value">₽24.8М</div>
                    <div class="stat-label">Общая выручка</div>
                    <div class="stat-trend trend-up">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        +8% с прошлого месяца
                    </div>
                </div>
                <div class="stat-card" onclick="location.href='analytics.html'">
                    <div class="stat-value">87%</div>
                    <div class="stat-label">Средняя вероятность</div>
                    <div class="stat-trend trend-down">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        -2% с прошлого месяца
                    </div>
                </div>
                <div class="stat-card" onclick="location.href='projects.html?filter=new'">
                    <div class="stat-value">32</div>
                    <div class="stat-label">Новых в этом месяце</div>
                    <div class="stat-trend trend-up">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        +5 с прошлого месяца
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card" onclick="location.href='analytics.html'">
                    <div class="chart-header">
                        <h3 class="chart-title">Динамика проектов по этапам</h3>
                        <select>
                            <option>Последние 6 месяцев</option>
                            <option>Последний год</option>
                            <option>Все время</option>
                        </select>
                    </div>
                    <div class="chart-placeholder">
                        График динамики проектов по этапам
                        <div class="chart-overlay">
                            <div class="overlay-content">
                                <h4>Перейти к аналитике</h4>
                                <p>Детальная аналитика по этапам проектов</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chart-card" onclick="location.href='analytics.html'">
                    <div class="chart-header">
                        <h3 class="chart-title">Распределение по услугам</h3>
                        <select>
                            <option>Текущий месяц</option>
                            <option>Квартал</option>
                            <option>Год</option>
                        </select>
                    </div>
                    <div class="chart-placeholder">
                        Круговая диаграмма распределения
                        <div class="chart-overlay">
                            <div class="overlay-content">
                                <h4>Перейти к аналитике</h4>
                                <p>Детальная аналитика по услугам</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="projects-table">
                <div class="table-header">
                    <h3 class="table-title">Реестр проектов</h3>
                    <div class="table-controls">
                        <div class="search-box">
                            <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 21L16.514 16.506M19 10.5C19 15.194 15.194 19 10.5 19C5.806 19 2 15.194 2 10.5C2 5.806 5.806 2 10.5 2C15.194 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <input type="text" placeholder="Поиск проектов...">
                        </div>
                        <a href="reports.html" class="btn btn-primary">Экспорт</a>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Название проекта</th>
                            <th>Менеджер</th>
                            <th>Этап</th>
                            <th>Вероятность</th>
                            <th>Статус</th>
                            <th>Выручка</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="project-row" onclick="location.href='project-card.html'">
                            <td class="project-name">
                                Волна Коммуникаций
                                <span class="new-badge" style="background: var(--success); color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 8px;">Новый</span>
                            </td>
                            <td class="project-manager">
                                <div class="manager-avatar">ИИ</div>
                                Иванов И.
                            </td>
                            <td>Проработка лида</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 20%"></div>
                                </div>
                                <div>20%</div>
                            </td>
                            <td><span class="status-badge status-pending">В работе</span></td>
                            <td>₽484,100</td>
                        </tr>
                        <tr class="project-row" onclick="location.href='project-card.html'">
                            <td class="project-name">Цифровой Мост</td>
                            <td class="project-manager">
                                <div class="manager-avatar">СС</div>
                                Смирнов С.
                            </td>
                            <td>Успех</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 100%"></div>
                                </div>
                                <div>100%</div>
                            </td>
                            <td><span class="status-badge status-completed">Завершен</span></td>
                            <td>₽2,477,000</td>
                        </tr>
                        <tr class="project-row" onclick="location.href='project-card.html'">
                            <td class="project-name">Сеть Будущего</td>
                            <td class="project-manager">
                                <div class="manager-avatar">КК</div>
                                Кузнецов К.
                            </td>
                            <td>КП</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 30%"></div>
                                </div>
                                <div>30%</div>
                            </td>
                            <td><span class="status-badge status-active">Активный</span></td>
                            <td>₽42,000</td>
                        </tr>
                        <tr class="project-row" onclick="location.href='project-card.html'">
                            <td class="project-name">Эхо Соединения</td>
                            <td class="project-manager">
                                <div class="manager-avatar">ПП</div>
                                Попов П.
                            </td>
                            <td>КП</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 30%"></div>
                                </div>
                                <div>30%</div>
                            </td>
                            <td><span class="status-badge status-active">Активный</span></td>
                            <td>₽1,517,000</td>
                        </tr>
                        <tr class="project-row" onclick="location.href='project-card.html'">
                            <td class="project-name">Голос Онлайн</td>
                            <td class="project-manager">
                                <div class="manager-avatar">ВВ</div>
                                Васильев В.
                            </td>
                            <td>КП</td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 30%"></div>
                                </div>
                                <div>30%</div>
                            </td>
                            <td><span class="status-badge status-active">Активный</span></td>
                            <td>₽482,000</td>
                        </tr>
                    </tbody>
                </table>
                <div class="table-footer">
                    <div>Показано 5 из 142 проектов</div>
                    <div class="pagination">
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn">...</button>
                        <button class="page-btn">29</button>
                    </div>
                </div>
            </div>

            <!-- Быстрые действия -->
            <div class="quick-actions">
                <h3 class="section-title">Быстрые действия</h3>
                <div class="actions-grid">
                    <div class="action-card" onclick="location.href='project-card.html'">
                        <div class="action-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 4V20M20 12H4" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h4>Создать проект</h4>
                        <p>Добавить новый проект в систему</p>
                    </div>
                    <div class="action-card" onclick="location.href='reports.html'">
                        <div class="action-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 17L9 12M12 17L12 7M15 17L15 14M6 21H18C18.5304 21 19.0391 20.7893 19.4142 20.4142C19.7893 20.0391 20 19.5304 20 19V5C20 4.46957 19.7893 3.96086 19.4142 3.58579C19.0391 3.21071 18.5304 3 18 3H6C5.46957 3 4.96086 3.21071 4.58579 3.58579C4.21071 3.96086 4 4.46957 4 5V19C4 19.5304 4.21071 20.0391 4.58579 20.4142C4.96086 20.7893 5.46957 21 6 21Z" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h4>Создать отчет</h4>
                        <p>Сгенерировать пользовательский отчет</p>
                    </div>
                    <div class="action-card" onclick="location.href='dictionaries.html'">
                        <div class="action-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 6.25278V19.2528M12 6.25278C10.8321 5.47686 9.24649 5 7.5 5C5.75351 5 4.16789 5.47686 3 6.25278V19.2528C4.16789 18.4769 5.75351 18 7.5 18C9.24649 18 10.8321 18.4769 12 19.2528M12 6.25278C13.1679 5.47686 14.7535 5 16.5 5C18.2465 5 19.8321 5.47686 21 6.25278V19.2528C19.8321 18.4769 18.2465 18 16.5 18C14.7535 18 13.1679 18.4769 12 19.2528" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h4>Справочники</h4>
                        <p>Управление справочными данными</p>
                    </div>
                    <div class="action-card" onclick="location.href='analytics.html'">
                        <div class="action-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 19V13M15 19V9M3 21H21M5 19V11C5 10.4477 5.44772 10 6 10H9C9.55228 10 10 10.4477 10 11V19C10 19.5523 9.55228 20 9 20H6C5.44772 20 5 19.5523 5 19ZM14 19V7C14 6.44772 14.4477 6 15 6H18C18.5523 6 19 6.44772 19 7V19C19 19.5523 18.5523 20 18 20H15C14.4477 20 14 19.5523 14 19Z" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <h4>Аналитика</h4>
                        <p>Детальная аналитика и визуализация</p>
                    </div>
                </div>
            </div>
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
                    <a href="#" class="social-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 2H15C13.6739 2 12.4021 2.52678 11.4645 3.46447C10.5268 4.40215 10 5.67392 10 7V10H7V14H10V22H14V14H17L18 10H14V7C14 6.73478 14.1054 6.48043 14.2929 6.29289C14.4804 6.10536 14.7348 6 15 6H18V2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 4.01C21.0424 4.48488 20.0151 4.80741 19 5C20.0151 4.49559 20.823 3.61453 21.2 2.5C20.2406 2.96995 19.2013 3.29144 18.15 3.45C17.237 2.49994 15.9159 1.97691 14.54 2C11.7736 2 9.53999 4.23361 9.53999 7C9.53999 7.36861 9.57999 7.72721 9.65999 8.07C6.53999 7.875 3.70999 6.3225 1.66999 3.96C1.24864 4.67357 1.01392 5.48392 1.00999 6.31C1.00999 7.9375 1.84999 9.36 3.09999 10.16C2.36276 10.138 1.64472 9.93651 1.00999 9.58V9.63C1.00999 11.795 2.54999 13.605 4.59999 14.035C4.1863 14.1513 3.75542 14.209 3.32249 14.205C3.01049 14.2047 2.69944 14.1759 2.39399 14.12C2.70299 15.9195 4.13199 17.305 5.86199 17.34C4.49999 18.63 2.73999 19.3575 0.869995 19.35C0.579995 19.35 0.289995 19.3325 0 19.2975C1.77999 20.64 3.87249 21.3575 6.06749 21.35C13.62 21.35 17.67 14.2775 17.67 8.0325C17.67 7.8375 17.665 7.6425 17.6575 7.4525C18.62 6.7825 19.4575 5.9475 20.12 5C19.215 5.37 18.2475 5.6025 17.25 5.695C18.27 5.0925 19.0425 4.12 19.44 2.945C19.47 2.87 19.5 2.795 19.5275 2.72C18.5175 3.3075 17.4075 3.7125 16.24 3.9075C15.2525 2.9225 13.86 2.3375 12.3175 2.3375C9.43749 2.3375 7.10749 4.6675 7.10749 7.5475C7.10749 8.0075 7.16249 8.4525 7.26749 8.88C4.79999 8.7375 2.57499 7.5475 1.10999 5.68C0.602489 6.5225 0.322489 7.495 0.322489 8.5225C0.322489 10.48 1.35999 12.195 2.87999 13.1475C2.23749 13.1275 1.62749 12.95 1.09999 12.65C1.09999 12.6675 1.09999 12.6825 1.09999 12.7C1.09999 15.195 2.99999 17.245 5.44749 17.6975C4.95249 17.8225 4.44499 17.885 3.93499 17.8825C3.63249 17.8825 3.33249 17.8575 3.03749 17.8125C3.64749 19.7625 5.47749 21.1625 7.57749 21.2C5.84999 22.495 3.63999 23.2575 1.24999 23.2575C0.829994 23.2575 0.414994 23.235 0 23.19C2.22749 24.555 4.85249 25.3375 7.64749 25.3375C16.69 25.3375 21.5 17.9725 21.5 8.5075C21.5 8.2175 21.495 7.93 21.485 7.6425C22.34 6.9925 23.06 6.185 23.62 5.2675Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="#" class="social-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16 8C17.5913 8 19.1174 8.63214 20.2426 9.75736C21.3679 10.8826 22 12.4087 22 14V21H18V14C18 13.4696 17.7893 12.9609 17.4142 12.5858C17.0391 12.2107 16.5304 12 16 12C15.4696 12 14.9609 12.2107 14.5858 12.5858C14.2107 12.9609 14 13.4696 14 14V21H10V14C10 12.4087 10.6321 10.8826 11.7574 9.75736C12.8826 8.63214 14.4087 8 16 8Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M6 9H2V21H6V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M4 6C5.10457 6 6 5.10457 6 4C6 2.89543 5.10457 2 4 2C2.89543 2 2 2.89543 2 4C2 5.10457 2.89543 6 4 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Добавляем обработчики для навигации
        document.addEventListener('DOMContentLoaded', function() {
            // Обработчики для строк таблицы
            document.querySelectorAll('.project-row').forEach(row => {
                row.addEventListener('click', function() {
                    window.location.href = 'project-card.html';
                });
            });

            // Обработчики для карточек статистики
            document.querySelectorAll('.stat-card').forEach(card => {
                card.addEventListener('click', function() {
                    if (this.onclick) {
                        this.onclick();
                    }
                });
            });

            // Обработчики для карточек графиков
            document.querySelectorAll('.chart-card').forEach(card => {
                card.addEventListener('click', function() {
                    window.location.href = 'analytics.html';
                });
            });

            // Обработчики для быстрых действий
            document.querySelectorAll('.action-card').forEach(card => {
                card.addEventListener('click', function() {
                    if (this.onclick) {
                        this.onclick();
                    }
                });
            });
        });
    </script>
</body>
</html>