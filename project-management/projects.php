<?php
// projects.php
require_once 'session.php';
require_once 'auth.php';
require_once 'database.php';

Auth::requireAuth();

// Получаем проекты из БД с правильной структурой
try {
    $projects = Database::fetchAll("
        SELECT 
            p.id,
            p.project_name,
            p.organization_name,
            p.organization_inn,
            p.probability,
            p.creation_date,
            p.implementation_year,
            p.is_industry_solution,
            p.is_forecast_accepted,
            p.is_dzo_implementation,
            p.needs_management_control,
            d_stage.name as stage_name,
            d_service.name as service_name,
            d_payment.name as payment_type_name,
            d_segment.name as segment_name,
            u_manager.full_name as manager_name,
            u_industry.full_name as industry_manager_name,
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
        LEFT JOIN users u_manager ON p.manager_id = u_manager.id
        LEFT JOIN users u_industry ON p.industry_manager_id = u_industry.id
        ORDER BY p.creation_date DESC, p.id DESC
    ");
} catch (Exception $e) {
    error_log("Projects load error: " . $e->getMessage());
    $projects = [];
}

// Если БД пустая, создаем демо-данные
if (empty($projects)) {
    $projects = [
        [
            'id' => 1,
            'project_name' => 'Волна Коммуникаций',
            'organization_name' => 'АО "Альфа-Телеком"',
            'organization_inn' => '1234567890',
            'stage_name' => 'Проработка лида',
            'service_name' => 'Интернет',
            'payment_type_name' => 'Сервисная',
            'segment_name' => 'Крупный сегмент',
            'manager_name' => 'Иванов Иван Иванович',
            'industry_manager_name' => null,
            'probability' => 0.20,
            'total_revenue' => 484100,
            'total_costs' => 125000,
            'implementation_year' => 2025,
            'is_industry_solution' => false,
            'is_forecast_accepted' => true,
            'is_dzo_implementation' => false,
            'needs_management_control' => false,
            'creation_date' => '2025-01-15 10:30:00'
        ],
        [
            'id' => 2,
            'project_name' => 'Цифровой Мост для Госсектора', 
            'organization_name' => 'Минцифры России',
            'organization_inn' => '007456789012',
            'stage_name' => 'Успех',
            'service_name' => 'Отраслевые решения',
            'payment_type_name' => 'Интеграционные проекты',
            'segment_name' => 'Госсектор',
            'manager_name' => 'Смирнов Сергей Сергеевич',
            'industry_manager_name' => 'Петров Алексей Алексеевич',
            'probability' => 1.00,
            'total_revenue' => 2500000,
            'total_costs' => 450000,
            'implementation_year' => 2024,
            'is_industry_solution' => true,
            'is_forecast_accepted' => true,
            'is_dzo_implementation' => true,
            'needs_management_control' => true,
            'creation_date' => '2024-11-20 14:15:00'
        ],
        [
            'id' => 3,
            'project_name' => 'Облачная плаформа для среднего бизнеса',
            'organization_name' => 'ООО "Гамма-ИТ"',
            'organization_inn' => '1234098765',
            'stage_name' => 'КП',
            'service_name' => 'Облачные сервисы',
            'payment_type_name' => 'Разовые',
            'segment_name' => 'Средний сегмент',
            'manager_name' => 'Кузнецов Константин Константинович',
            'industry_manager_name' => null,
            'probability' => 0.30,
            'total_revenue' => 750000,
            'total_costs' => 180000,
            'implementation_year' => 2025,
            'is_industry_solution' => false,
            'is_forecast_accepted' => true,
            'is_dzo_implementation' => false,
            'needs_management_control' => false,
            'creation_date' => '2025-01-08 09:45:00'
        ],
        [
            'id' => 4,
            'project_name' => 'Кибербезопасность для банковского сектора',
            'organization_name' => 'ПАО "Бета-Банк"',
            'organization_inn' => '1234509876',
            'stage_name' => 'Заключение Д Д',
            'service_name' => 'Инфобез',
            'payment_type_name' => 'Инсталляции',
            'segment_name' => 'Крупный сегмент',
            'manager_name' => 'Попова Мария Ивановна',
            'industry_manager_name' => 'Сидоров Борис Борисович',
            'probability' => 0.70,
            'total_revenue' => 1850000,
            'total_costs' => 320000,
            'implementation_year' => 2025,
            'is_industry_solution' => true,
            'is_forecast_accepted' => true,
            'is_dzo_implementation' => false,
            'needs_management_control' => true,
            'creation_date' => '2024-12-05 16:20:00'
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Реестр проектов - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .filters-section {
            background: white;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .filter-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }
        
        .project-meta {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: var(--gray);
        }
        
        .project-flags {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        
        .flag {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
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
        
        .revenue-positive {
            color: var(--success);
            font-weight: 600;
        }
        
        .revenue-negative {
            color: var(--danger);
            font-weight: 600;
        }
        
        .profit-margin {
            font-size: 0.75rem;
            color: var(--gray);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once 'blocks/head.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Реестр проектов</h1>
                <p>Управление всеми проектами коммерческого подразделения</p>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="dashboard">
        <div class="container">
            <!-- Фильтры -->
            <div class="filters-section">
                <h3 style="margin-bottom: 1rem;">Фильтры и поиск</h3>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label>Поиск по названию</label>
                        <input type="text" id="search-input" placeholder="Введите название проекта..." class="form-input">
                    </div>
                    <div class="filter-group">
                        <label>Этап проекта</label>
                        <select id="stage-filter" class="form-input">
                            <option value="">Все этапы</option>
                            <option value="Лид">Лид</option>
                            <option value="Проработка лида">Проработка лида</option>
                            <option value="КП">КП</option>
                            <option value="Пилот">Пилот</option>
                            <option value="Успех">Успех</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Услуга</label>
                        <select id="service-filter" class="form-input">
                            <option value="">Все услуги</option>
                            <option value="Интернет">Интернет</option>
                            <option value="Телефония">Телефония</option>
                            <option value="Инфобез">Инфобез</option>
                            <option value="Облачные сервисы">Облачные сервисы</option>
                            <option value="Отраслевые решения">Отраслевые решения</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Сегмент</label>
                        <select id="segment-filter" class="form-input">
                            <option value="">Все сегменты</option>
                            <option value="Крупный сегмент">Крупный сегмент</option>
                            <option value="Госсектор">Госсектор</option>
                            <option value="Средний сегмент">Средний сегмент</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button id="reset-filters" class="btn btn-secondary">Сбросить фильтры</button>
                    <button id="apply-filters" class="btn btn-primary">Применить фильтры</button>
                </div>
            </div>

            <!-- Projects Table -->
            <div class="projects-table">
                <div class="table-header">
                    <h3 class="table-title">
                        Все проекты 
                        <span style="font-size: 0.875rem; color: var(--gray); font-weight: normal; margin-left: 0.5rem;">
                            (<?= count($projects) ?> проекта)
                        </span>
                    </h3>
                    <div class="table-controls">
                        <div class="search-box">
                            <input type="text" id="quick-search" placeholder="Быстрый поиск...">
                            <div class="search-icon">🔍</div>
                        </div>
                        <a href="project-create.php" class="btn btn-primary">+ Создать проект</a>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Проект</th>
                                <th>Организация / ИНН</th>
                                <th>Услуга / Сегмент</th>
                                <th>Менеджер</th>
                                <th>Этап / Вероятность</th>
                                <th>Финансы</th>
                                <th>Дата создания</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): 
                                $profit = $project['total_revenue'] - $project['total_costs'];
                                $margin = $project['total_revenue'] > 0 ? ($profit / $project['total_revenue']) * 100 : 0;
                            ?>
                            <tr class="project-row" onclick="location.href='project-card.php?id=<?= $project['id'] ?>'">
                                <!-- Название проекта -->
                                <td>
                                    <div class="project-name"><?= htmlspecialchars($project['project_name']) ?></div>
                                    <div class="project-meta">
                                        <span><?= htmlspecialchars($project['payment_type_name']) ?></span>
                                        <span>•</span>
                                        <span><?= $project['implementation_year'] ?> год</span>
                                    </div>
                                    <div class="project-flags">
                                        <?php if ($project['is_industry_solution']): ?>
                                            <span class="flag flag-industry">Отраслевое</span>
                                        <?php endif; ?>
                                        <?php if ($project['is_forecast_accepted']): ?>
                                            <span class="flag flag-forecast">Прогноз</span>
                                        <?php endif; ?>
                                        <?php if ($project['is_dzo_implementation']): ?>
                                            <span class="flag flag-dzo">ДЗО</span>
                                        <?php endif; ?>
                                        <?php if ($project['needs_management_control']): ?>
                                            <span class="flag flag-control">Контроль</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                
                                <!-- Организация -->
                                <td>
                                    <div style="font-weight: 500;"><?= htmlspecialchars($project['organization_name']) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--gray);">ИНН: <?= $project['organization_inn'] ?></div>
                                    <?php if ($project['industry_manager_name']): ?>
                                        <div style="font-size: 0.75rem; color: var(--primary); margin-top: 0.25rem;">
                                            Отр. менеджер: <?= $project['industry_manager_name'] ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Услуга и сегмент -->
                                <td>
                                    <div style="font-weight: 500; color: var(--primary);"><?= htmlspecialchars($project['service_name']) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--gray);"><?= htmlspecialchars($project['segment_name']) ?></div>
                                </td>
                                
                                <!-- Менеджер -->
                                <td>
                                    <div class="project-manager">
                                        <div class="manager-avatar"><?= substr($project['manager_name'], 0, 2) ?></div>
                                        <?= htmlspecialchars($project['manager_name']) ?>
                                    </div>
                                </td>
                                
                                <!-- Этап и вероятность -->
                                <td>
                                    <div style="font-weight: 500; margin-bottom: 0.5rem;"><?= htmlspecialchars($project['stage_name']) ?></div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= $project['probability'] * 100 ?>%"></div>
                                    </div>
                                    <div style="text-align: center; font-size: 0.875rem; font-weight: 600; color: var(--primary);">
                                        <?= round($project['probability'] * 100) ?>%
                                    </div>
                                </td>
                                
                                <!-- Финансы -->
                                <td>
                                    <div class="revenue-positive" style="font-weight: 600;">
                                        +₽<?= number_format($project['total_revenue'], 0, ',', ' ') ?>
                                    </div>
                                    <div style="font-size: 0.875rem; color: var(--danger);">
                                        -₽<?= number_format($project['total_costs'], 0, ',', ' ') ?>
                                    </div>
                                    <div class="profit-margin">
                                        Прибыль: 
                                        <span style="color: <?= $profit >= 0 ? 'var(--success)' : 'var(--danger)' ?>; font-weight: 600;">
                                            ₽<?= number_format($profit, 0, ',', ' ') ?>
                                        </span>
                                        (<?= round($margin, 1) ?>%)
                                    </div>
                                </td>
                                
                                <!-- Дата создания -->
                                <td>
                                    <div style="font-weight: 500;"><?= date('d.m.Y', strtotime($project['creation_date'])) ?></div>
                                    <div style="font-size: 0.875rem; color: var(--gray);"><?= date('H:i', strtotime($project['creation_date'])) ?></div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="table-info">
                        Показано <?= count($projects) ?> из <?= count($projects) ?> проектов
                    </div>
                    <div class="pagination">
                        <button class="page-btn active">1</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Обработчики для строк таблицы
            document.querySelectorAll('.project-row').forEach(row => {
                row.addEventListener('click', function() {
                    const href = this.getAttribute('onclick')?.match(/location\.href='([^']+)'/)?.[1];
                    if (href) {
                        window.location.href = href;
                    }
                });
            });

            // Быстрый поиск
            const quickSearch = document.getElementById('quick-search');
            quickSearch.addEventListener('input', function() {
                filterProjects();
            });

            // Фильтрация проектов
            function filterProjects() {
                const searchTerm = quickSearch.value.toLowerCase();
                const stageFilter = document.getElementById('stage-filter').value;
                const serviceFilter = document.getElementById('service-filter').value;
                const segmentFilter = document.getElementById('segment-filter').value;

                document.querySelectorAll('.project-row').forEach(row => {
                    const projectName = row.querySelector('.project-name').textContent.toLowerCase();
                    const organization = row.cells[1].textContent.toLowerCase();
                    const service = row.cells[2].textContent.toLowerCase();
                    const stage = row.cells[4].textContent.toLowerCase();
                    const segment = row.cells[2].textContent.toLowerCase();

                    const matchesSearch = projectName.includes(searchTerm) || organization.includes(searchTerm);
                    const matchesStage = !stageFilter || stage.includes(stageFilter.toLowerCase());
                    const matchesService = !serviceFilter || service.includes(serviceFilter.toLowerCase());
                    const matchesSegment = !segmentFilter || segment.includes(segmentFilter.toLowerCase());

                    if (matchesSearch && matchesStage && matchesService && matchesSegment) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Обновляем счетчик
                const visibleCount = document.querySelectorAll('.project-row:not([style*="display: none"])').length;
                document.querySelector('.table-info').textContent = `Показано ${visibleCount} из ${visibleCount} проектов`;
            }

            // Применение фильтров
            document.getElementById('apply-filters').addEventListener('click', filterProjects);
            
            // Сброс фильтров
            document.getElementById('reset-filters').addEventListener('click', function() {
                document.getElementById('stage-filter').value = '';
                document.getElementById('service-filter').value = '';
                document.getElementById('segment-filter').value = '';
                quickSearch.value = '';
                filterProjects();
            });

            // Поиск по Enter
            quickSearch.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    filterProjects();
                }
            });
        });
    </script>
</body>
</html>