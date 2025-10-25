<?php
// project-card.php
require_once 'session.php';
require_once 'auth.php';
require_once 'database.php';

Auth::requireAuth();

// Получаем ID проекта из URL
$project_id = $_GET['id'] ?? null;

// Отладочная информация
error_log("Loading project card for ID: " . $project_id);

if (!$project_id) {
    header('Location: projects.php');
    exit;
}

// Проверяем успешное сохранение
if (isset($_GET['success'])) {
    error_log("Success parameter detected in project card");
}


// Загружаем данные проекта из БД
try {
    $project = Database::fetchOne("
        SELECT 
            p.*,
            d_stage.name as stage_name,
            d_service.name as service_name,
            d_payment.name as payment_type_name,
            d_segment.name as segment_name,
            d_evaluation.name as evaluation_status_name,
            u_manager.full_name as manager_name,
            u_industry.full_name as industry_manager_name,
            u_created.full_name as created_by_name,
            u_updated.full_name as updated_by_name,
            COALESCE((
                SELECT SUM(pr.amount) 
                FROM project_revenues pr 
                WHERE pr.project_id = p.id
            ), 0) as total_revenue,
            COALESCE((
                SELECT SUM(pc.amount) 
                FROM project_costs pc 
                WHERE pc.project_id = p.id
            ), 0) as total_costs
        FROM projects p
        LEFT JOIN dictionaries d_stage ON p.stage_id = d_stage.id AND d_stage.type = 'stage'
        LEFT JOIN dictionaries d_service ON p.service_id = d_service.id AND d_service.type = 'service'
        LEFT JOIN dictionaries d_payment ON p.payment_type_id = d_payment.id AND d_payment.type = 'payment_type'
        LEFT JOIN dictionaries d_segment ON p.segment_id = d_segment.id AND d_segment.type = 'segment'
        LEFT JOIN dictionaries d_evaluation ON p.accepted_for_evaluation_id = d_evaluation.id AND d_evaluation.type = 'evaluation_status'
        LEFT JOIN users u_manager ON p.manager_id = u_manager.id
        LEFT JOIN users u_industry ON p.industry_manager_id = u_industry.id
        LEFT JOIN users u_created ON p.created_by = u_created.id
        LEFT JOIN users u_updated ON p.updated_by = p.updated_by
        WHERE p.id = $1
    ", [$project_id]);

    // Если проект не найден
    if (empty($project)) {
        header('Location: projects.php?error=project_not_found');
        exit;
    }

    // Загружаем выручку проекта
    $revenues = Database::fetchAll("
        SELECT pr.*, d.name as status_name
        FROM project_revenues pr
        LEFT JOIN dictionaries d ON pr.revenue_status_id = d.id AND d.type = 'revenue_status'
        WHERE pr.project_id = $1
        ORDER BY pr.year DESC, pr.month DESC
    ", [$project_id]);

    // Загружаем затраты проекта
    $costs = Database::fetchAll("
        SELECT pc.*, 
               d_type.name as cost_type_name,
               d_status.name as cost_status_name
        FROM project_costs pc
        LEFT JOIN dictionaries d_type ON pc.cost_type_id = d_type.id AND d_type.type = 'cost_type'
        LEFT JOIN dictionaries d_status ON pc.cost_status_id = d_status.id AND d_status.type = 'cost_status'
        WHERE pc.project_id = $1
        ORDER BY pc.year DESC, pc.month DESC
    ", [$project_id]);

    // Загружаем историю изменений
    $history = Database::fetchAll("
        SELECT ph.*, u.full_name as user_name
        FROM project_history ph
        LEFT JOIN users u ON ph.user_id = u.id
        WHERE ph.project_id = $1
        ORDER BY ph.change_timestamp DESC
        LIMIT 50
    ", [$project_id]);

} catch (Exception $e) {
    error_log("Project card load error: " . $e->getMessage());
    header('Location: projects.php?error=load_error');
    exit;
}

// Если БД пустая, создаем демо-данные для отладки
if (empty($project) && $project_id == 2) {
    $project = [
        'id' => 2,
        'project_name' => 'Цифровой Мост для Госсектора',
        'organization_name' => 'Минцифры России',
        'organization_inn' => '007456789012',
        'project_number' => 'GOV/DIG/2024/001',
        'stage_name' => 'Успех',
        'service_name' => 'Отраслевые решения',
        'payment_type_name' => 'Интеграционные проекты',
        'segment_name' => 'Госсектор',
        'manager_name' => 'Смирнов Сергей Сергеевич',
        'industry_manager_name' => 'Петров Алексей Алексеевич',
        'probability' => 1.00,
        'implementation_year' => 2024,
        'is_industry_solution' => true,
        'is_forecast_accepted' => true,
        'is_dzo_implementation' => true,
        'needs_management_control' => true,
        'evaluation_status_name' => 'ОЦЕНКА',
        'current_status' => 'Проект успешно завершен. Все работы выполнены в срок, клиент доволен результатом.',
        'period_achievements' => 'Завершена интеграция с государственными системами, проведено обучение пользователей.',
        'next_period_plans' => 'Подготовка отчетности и закрытие проекта.',
        'total_revenue' => 2500000,
        'total_costs' => 450000,
        'creation_date' => '2024-11-20 14:15:00',
        'created_by_name' => 'Администратор Системы'
    ];
    
    $revenues = [
        [
            'year' => 2024,
            'month' => 11,
            'amount' => 1500000,
            'status_name' => 'начислена'
        ],
        [
            'year' => 2024,
            'month' => 12,
            'amount' => 1000000,
            'status_name' => 'начислена'
        ]
    ];
    
    $costs = [
        [
            'year' => 2024,
            'month' => 11,
            'amount' => 250000,
            'cost_type_name' => 'Прямые',
            'cost_status_name' => 'начислены'
        ],
        [
            'year' => 2024,
            'month' => 12,
            'amount' => 200000,
            'cost_type_name' => 'Коммерческие',
            'cost_status_name' => 'начислены'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Карточка проекта - <?= htmlspecialchars($project['project_name']) ?> - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .project-header {
            background: linear-gradient(135deg, #f7f0ff 0%, #ede6ff 100%);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .project-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            font-size: 0.875rem;
            color: var(--gray);
            margin-bottom: 0.25rem;
        }
        
        .meta-value {
            font-weight: 500;
            color: var(--dark);
        }
        
        .project-flags {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .flag {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .flag-industry {
            background: #f0f8ff;
            color: #0066cc;
            border: 1px solid #0066cc;
        }
        
        .flag-forecast {
            background: #f0fff0;
            color: #00a000;
            border: 1px solid #00a000;
        }
        
        .flag-dzo {
            background: #fff0f0;
            color: #cc0000;
            border: 1px solid #cc0000;
        }
        
        .flag-control {
            background: #fff8f0;
            color: #cc6600;
            border: 1px solid #cc6600;
        }
        
        .financial-summary {
            background: var(--light);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .financial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .financial-item {
            text-align: center;
        }
        
        .financial-value {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .financial-revenue {
            color: var(--success);
        }
        
        .financial-costs {
            color: var(--danger);
        }
        
        .financial-profit {
            color: var(--primary);
        }
        
        .financial-label {
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        .history-item {
            padding: 1rem;
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
            margin-bottom: 0.5rem;
        }
        
        .history-change {
            font-weight: 500;
        }
        
        .history-field {
            color: var(--primary);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once 'blocks/head.php'; ?>

    <!-- Project Header -->
    <section class="project-header">
        <div class="container">
            <div class="page-header-content">
                <div class="back-link">
                    <a href="projects.php">← Назад к реестру проектов</a>
                </div>
                <div class="page-title-row">
                    <h1><?= htmlspecialchars($project['project_name']) ?></h1>
                    <div class="page-actions">
                        <button class="btn btn-secondary" onclick="window.print()">Печать</button>
<a href="project-edit.php?id=<?= $project['id'] ?>" class="btn btn-primary">Редактировать</a>
                    </div>
                </div>
                <div class="project-meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Организация</span>
                        <span class="meta-value"><?= htmlspecialchars($project['organization_name']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">ИНН</span>
                        <span class="meta-value"><?= $project['organization_inn'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">ID проекта</span>
                        <span class="meta-value">PRJ-<?= str_pad($project['id'], 4, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Статус</span>
                        <span class="meta-value status-active"><?= htmlspecialchars($project['stage_name']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Вероятность</span>
                        <span class="meta-value"><?= round($project['probability'] * 100) ?>%</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Дата создания</span>
                        <span class="meta-value"><?= date('d.m.Y H:i', strtotime($project['creation_date'])) ?></span>
                    </div>
                </div>
                
                <div class="project-flags">
                    <?php if ($project['is_industry_solution']): ?>
                        <span class="flag flag-industry">Отраслевое решение</span>
                    <?php endif; ?>
                    <?php if ($project['is_forecast_accepted']): ?>
                        <span class="flag flag-forecast">Принимаемый к прогнозу</span>
                    <?php endif; ?>
                    <?php if ($project['is_dzo_implementation']): ?>
                        <span class="flag flag-dzo">Реализация через ДЗО</span>
                    <?php endif; ?>
                    <?php if ($project['needs_management_control']): ?>
                        <span class="flag flag-control">Контроль руководства</span>
                    <?php endif; ?>
                    <?php if ($project['project_number']): ?>
                        <span class="flag" style="background: #f0f0f0; color: #666; border: 1px solid #ddd;">
                            № <?= $project['project_number'] ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Project Content -->
    <section class="project-card">
        <div class="container">
            <!-- Финансовая сводка -->
            <div class="financial-summary">
                <h3 style="margin-bottom: 1rem;">Финансовая сводка</h3>
                <div class="financial-grid">
                    <div class="financial-item">
                        <div class="financial-value financial-revenue">
                            +₽<?= number_format($project['total_revenue'], 0, ',', ' ') ?>
                        </div>
                        <div class="financial-label">Выручка</div>
                    </div>
                    <div class="financial-item">
                        <div class="financial-value financial-costs">
                            -₽<?= number_format($project['total_costs'], 0, ',', ' ') ?>
                        </div>
                        <div class="financial-label">Затраты</div>
                    </div>
                    <div class="financial-item">
                        <div class="financial-value financial-profit">
                            ₽<?= number_format($project['total_revenue'] - $project['total_costs'], 0, ',', ' ') ?>
                        </div>
                        <div class="financial-label">Прибыль</div>
                    </div>
                    <div class="financial-item">
                        <div class="financial-value">
                            <?= $project['total_revenue'] > 0 ? round((($project['total_revenue'] - $project['total_costs']) / $project['total_revenue']) * 100, 1) : 0 ?>%
                        </div>
                        <div class="financial-label">Маржа</div>
                    </div>
                </div>
            </div>

            <!-- Табы -->
            <div class="card-tabs">
                <button class="tab-btn active" data-tab="general">Общая информация</button>
                <button class="tab-btn" data-tab="revenue">Выручка</button>
                <button class="tab-btn" data-tab="costs">Затраты</button>
                <button class="tab-btn" data-tab="additional">Дополнительная информация</button>
                <button class="tab-btn" data-tab="history">История изменений</button>
            </div>

            <!-- Общая информация -->
            <div class="tab-content active" id="general">
                <div class="project-form">
                    <div class="form-section">
                        <h3>Основная информация</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Название организации</label>
                                <input type="text" value="<?= htmlspecialchars($project['organization_name']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>ИНН организации</label>
                                <input type="text" value="<?= $project['organization_inn'] ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Название проекта</label>
                                <input type="text" value="<?= htmlspecialchars($project['project_name']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Услуга</label>
                                <input type="text" value="<?= htmlspecialchars($project['service_name']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Тип платежа</label>
                                <input type="text" value="<?= htmlspecialchars($project['payment_type_name']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Этап проекта</label>
                                <input type="text" value="<?= htmlspecialchars($project['stage_name']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Вероятность реализации</label>
                                <input type="text" value="<?= round($project['probability'] * 100) ?>%" readonly>
                            </div>
                            <div class="form-group">
                                <label>Менеджер</label>
                                <input type="text" value="<?= htmlspecialchars($project['manager_name']) ?>" readonly>
                            </div>
                            <?php if ($project['industry_manager_name']): ?>
                            <div class="form-group">
                                <label>Отраслевой менеджер</label>
                                <input type="text" value="<?= htmlspecialchars($project['industry_manager_name']) ?>" readonly>
                            </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label>Сегмент бизнеса</label>
                                <input type="text" value="<?= htmlspecialchars($project['segment_name']) ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Год реализации</label>
                                <input type="text" value="<?= $project['implementation_year'] ?>" readonly>
                            </div>
                            <?php if ($project['evaluation_status_name']): ?>
                            <div class="form-group">
                                <label>Принимаемый к оценке</label>
                                <input type="text" value="<?= htmlspecialchars($project['evaluation_status_name']) ?>" readonly>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Выручка -->
            <div class="tab-content" id="revenue">
                <div class="revenue-section">
                    <h3>Информация по выручке проекта</h3>
                    <?php if (!empty($revenues)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Год</th>
                                    <th>Месяц</th>
                                    <th>Сумма</th>
                                    <th>Статус начисления</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($revenues as $revenue): ?>
                                <tr>
                                    <td><?= $revenue['year'] ?></td>
                                    <td><?= DateTime::createFromFormat('!m', $revenue['month'])->format('F') ?></td>
                                    <td>₽<?= number_format($revenue['amount'], 0, ',', ' ') ?></td>
                                    <td>
                                        <span class="status-badge"><?= $revenue['status_name'] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div style="margin-top: 1rem; font-weight: 600; text-align: right;">
                            Итого: ₽<?= number_format($project['total_revenue'], 0, ',', ' ') ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--gray);">
                            Нет данных о выручке
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Затраты -->
            <div class="tab-content" id="costs">
                <div class="revenue-section">
                    <h3>Информация по затратам проекта</h3>
                    <?php if (!empty($costs)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Год</th>
                                    <th>Месяц</th>
                                    <th>Сумма</th>
                                    <th>Вид затрат</th>
                                    <th>Статус отражения</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($costs as $cost): ?>
                                <tr>
                                    <td><?= $cost['year'] ?></td>
                                    <td><?= DateTime::createFromFormat('!m', $cost['month'])->format('F') ?></td>
                                    <td>₽<?= number_format($cost['amount'], 0, ',', ' ') ?></td>
                                    <td><?= $cost['cost_type_name'] ?></td>
                                    <td>
                                        <span class="status-badge"><?= $cost['cost_status_name'] ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div style="margin-top: 1rem; font-weight: 600; text-align: right;">
                            Итого: ₽<?= number_format($project['total_costs'], 0, ',', ' ') ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: var(--gray);">
                            Нет данных о затратах
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Дополнительная информация -->
            <div class="tab-content" id="additional">
                <div class="project-form">
                    <div class="form-section">
                        <h3>Дополнительная информация</h3>
                        <div class="form-group">
                            <label>Текущий статус по проекту</label>
                            <textarea readonly style="min-height: 120px;"><?= htmlspecialchars($project['current_status'] ?? 'Не указано') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Что сделано за период</label>
                            <textarea readonly style="min-height: 120px;"><?= htmlspecialchars($project['period_achievements'] ?? 'Не указано') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Планы на следующий период</label>
                            <textarea readonly style="min-height: 120px;"><?= htmlspecialchars($project['next_period_plans'] ?? 'Не указано') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- История изменений -->
            <div class="tab-content" id="history">
                <div class="history-preview">
                    <h3 style="margin-bottom: 1rem;">История изменений</h3>
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $item): ?>
                        <div class="history-item">
                            <div class="history-meta">
                                <span><?= $item['user_name'] ?? 'Система' ?></span>
                                <span><?= date('d.m.Y H:i', strtotime($item['change_timestamp'])) ?></span>
                            </div>
                            <div class="history-change">
                                Изменено поле <span class="history-field"><?= $item['changed_field'] ?></span>
                                <?php if ($item['old_value']): ?>
                                    с "<em><?= $item['old_value'] ?></em>" на "<em><?= $item['new_value'] ?></em>"
                                <?php else: ?>
                                    : <em><?= $item['new_value'] ?></em>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="history-item">
                            <div class="history-meta">
                                <span>Система</span>
                                <span><?= date('d.m.Y H:i') ?></span>
                            </div>
                            <div class="history-change">Карточка проекта создана</div>
                        </div>
                    <?php endif; ?>
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
            </div>
        </div>
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