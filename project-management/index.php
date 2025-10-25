<?php
// index.php
require_once 'session.php';
require_once 'auth.php';
require_once 'database.php';

Auth::requireAuth();

// Получаем данные для дашборда из БД
try {
    // Основные метрики
    $stats = Database::fetchOne("
        SELECT 
            COUNT(*) as total_projects,
            COUNT(CASE WHEN stage_id NOT IN (SELECT id FROM dictionaries WHERE name LIKE '%Успех%') THEN 1 END) as active_projects,
            COUNT(CASE WHEN DATE_TRUNC('month', creation_date) = DATE_TRUNC('month', CURRENT_DATE) THEN 1 END) as new_this_month,
            COALESCE(SUM((SELECT SUM(amount) FROM project_revenues WHERE project_id = projects.id)), 0) as total_revenue,
            ROUND(AVG(probability) * 100) as avg_probability_percent,
            COUNT(CASE WHEN is_industry_solution = true THEN 1 END) as industry_projects,
            COUNT(CASE WHEN is_forecast_accepted = true THEN 1 END) as forecast_projects
        FROM projects
    ");

    // Проекты по этапам
    $projectsByStage = Database::fetchAll("
        SELECT 
            d.name as stage_name,
            COUNT(p.id) as project_count,
            ROUND(COALESCE(AVG(p.probability), 0) * 100) as avg_probability,
            COALESCE(SUM(pr.amount), 0) as total_revenue
        FROM dictionaries d
        LEFT JOIN projects p ON d.id = p.stage_id AND d.type = 'stage'
        LEFT JOIN project_revenues pr ON p.id = pr.project_id
        WHERE d.type = 'stage'
        GROUP BY d.id, d.name, d.sort_order
        ORDER BY d.sort_order
    ");

    // Выручка по услугам
    $revenueByService = Database::fetchAll("
        SELECT 
            d.name as service_name,
            COALESCE(SUM(pr.amount), 0) as total_revenue,
            COUNT(p.id) as project_count,
            ROUND(COALESCE(AVG(p.probability), 0) * 100) as avg_probability
        FROM dictionaries d
        LEFT JOIN projects p ON d.id = p.service_id AND d.type = 'service'
        LEFT JOIN project_revenues pr ON p.id = pr.project_id
        WHERE d.type = 'service'
        GROUP BY d.id, d.name
        ORDER BY total_revenue DESC
    ");

    // Последние проекты
    $recentProjects = Database::fetchAll("
        SELECT 
            p.id,
            p.project_name,
            p.organization_name,
            p.probability,
            p.creation_date,
            d_stage.name as stage_name,
            d_service.name as service_name,
            u.full_name as manager_name,
            COALESCE((
                SELECT SUM(pr.amount) 
                FROM project_revenues pr 
                WHERE pr.project_id = p.id
            ), 0) as project_revenue
        FROM projects p
        LEFT JOIN dictionaries d_stage ON p.stage_id = d_stage.id AND d_stage.type = 'stage'
        LEFT JOIN dictionaries d_service ON p.service_id = d_service.id AND d_service.type = 'service'
        LEFT JOIN users u ON p.manager_id = u.id
        ORDER BY p.creation_date DESC
        LIMIT 5
    ");

    // Статистика по менеджерам
    $managersStats = Database::fetchAll("
        SELECT 
            u.full_name as manager_name,
            COUNT(p.id) as project_count,
            COALESCE(SUM(pr.amount), 0) as total_revenue,
            ROUND(COALESCE(AVG(p.probability), 0) * 100) as avg_probability
        FROM users u
        LEFT JOIN projects p ON u.id = p.manager_id
        LEFT JOIN project_revenues pr ON p.id = pr.project_id
        WHERE u.is_active = true
        GROUP BY u.id, u.full_name
        HAVING COUNT(p.id) > 0
        ORDER BY total_revenue DESC
        LIMIT 5
    ");

} catch (Exception $e) {
    error_log("Dashboard data error: " . $e->getMessage());
    
    // Заглушки на случай ошибки БД
    $stats = [
        'total_projects' => 0,
        'active_projects' => 0,
        'new_this_month' => 0,
        'total_revenue' => 0,
        'avg_probability_percent' => 0,
        'industry_projects' => 0,
        'forecast_projects' => 0
    ];
    
    $projectsByStage = [];
    $revenueByService = [];
    $recentProjects = [];
    $managersStats = [];
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ростелеком - Управление проектами</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-mini-card {
            background: white;
            padding: 1.25rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #7700FF;
        }
        
        .stat-mini-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: #7700FF;
            margin-bottom: 0.25rem;
        }
        
        .stat-mini-label {
            font-size: 0.875rem;
            color: var(--gray);
            font-weight: 500;
        }
        
        .charts-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            height: 300px;
        }
        
        .managers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .manager-card {
            background: white;
            padding: 1.25rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .manager-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .manager-avatar-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #7700FF;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .manager-info h4 {
            margin: 0;
            font-size: 1rem;
        }
        
        .manager-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
        }
        
        .manager-stat {
            text-align: center;
        }
        
        .manager-stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #7700FF;
        }
        
        .manager-stat-label {
            font-size: 0.75rem;
            color: var(--gray);
        }
        
        @media (max-width: 768px) {
            .charts-row {
                grid-template-columns: 1fr;
            }
            
            .chart-container {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once 'blocks/head.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Управление проектами коммерческого подразделения</h1>
            <p>Единая платформа для сбора, обработки и анализа информации по количеству и качеству проектов</p>
            <div class="hero-buttons">
                <a href="project-create.php" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M12 4V20M20 12H4" stroke="currentColor" stroke-width="2"/>
                    </svg>
                    Создать проект
                </a>
                <a href="analytics.php" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2"/>
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
                <div class="last-update">
                    Обновлено: <?= date('d.m.Y H:i') ?>
                </div>
            </div>

            <!-- Быстрая статистика -->
            <div class="quick-stats">
                <div class="stat-mini-card">
                    <div class="stat-mini-value"><?= $stats['total_projects'] ?></div>
                    <div class="stat-mini-label">Всего проектов</div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-value"><?= $stats['active_projects'] ?></div>
                    <div class="stat-mini-label">Активных</div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-value">₽<?= number_format($stats['total_revenue'] / 1000000, 1) ?>М</div>
                    <div class="stat-mini-label">Общая выручка</div>
                </div>
                <div class="stat-mini-card">
                    <div class="stat-mini-value"><?= $stats['avg_probability_percent'] ?>%</div>
                    <div class="stat-mini-label">Средняя вероятность</div>
                </div>
            </div>

            <!-- Основные KPI -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_projects'] ?></div>
                    <div class="stat-label">Всего проектов</div>
                    <div class="stat-trend trend-up">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        +12%
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₽<?= number_format($stats['total_revenue'], 0, ',', ' ') ?></div>
                    <div class="stat-label">Общая выручка</div>
                    <div class="stat-trend trend-up">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        +8%
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['avg_probability_percent'] ?>%</div>
                    <div class="stat-label">Средняя вероятность</div>
                    <div class="stat-trend trend-down">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M6 9L12 15L18 9" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        -2%
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['new_this_month'] ?></div>
                    <div class="stat-label">Новых в этом месяце</div>
                    <div class="stat-trend trend-up">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                            <path d="M18 15L12 9L6 15" stroke="currentColor" stroke-width="2"/>
                        </svg>
                        +3
                    </div>
                </div>
            </div>

            <!-- Графики -->
            <div class="charts-row">
                <div class="chart-container">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Проекты по этапам</h3>
                    <canvas id="stageChart"></canvas>
                </div>
                <div class="chart-container">
                    <h3 style="margin-bottom: 1rem; font-size: 1.1rem;">Выручка по услугам</h3>
                    <canvas id="serviceChart"></canvas>
                </div>
            </div>

            <!-- Топ менеджеров -->
            <div class="managers-grid">
                <?php foreach ($managersStats as $manager): ?>
                <div class="manager-card">
                    <div class="manager-header">
                        <div class="manager-avatar-small">
                            <?= substr($manager['manager_name'], 0, 2) ?>
                        </div>
                        <div class="manager-info">
                            <h4><?= htmlspecialchars($manager['manager_name']) ?></h4>
                            <div style="font-size: 0.875rem; color: var(--gray);">
                                <?= $manager['project_count'] ?> проекта
                            </div>
                        </div>
                    </div>
                    <div class="manager-stats">
                        <div class="manager-stat">
                            <div class="manager-stat-value">₽<?= number_format($manager['total_revenue'] / 1000000, 1) ?>М</div>
                            <div class="manager-stat-label">Выручка</div>
                        </div>
                        <div class="manager-stat">
                            <div class="manager-stat-value"><?= $manager['avg_probability'] ?>%</div>
                            <div class="manager-stat-label">Вероятность</div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Таблица проектов -->
            <div class="projects-table">
                <div class="table-header">
                    <h3 class="table-title">Последние проекты</h3>
                    <div class="table-controls">
                        <a href="projects.php" class="btn btn-primary">Все проекты</a>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Название проекта</th>
                            <th>Организация</th>
                            <th>Менеджер</th>
                            <th>Этап</th>
                            <th>Вероятность</th>
                            <th>Выручка</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $project): ?>
                        <tr class="project-row" onclick="location.href='project-card.php?id=<?= $project['id'] ?>'">
                            <td class="project-name"><?= htmlspecialchars($project['project_name']) ?></td>
                            <td><?= htmlspecialchars($project['organization_name']) ?></td>
                            <td class="project-manager">
                                <div class="manager-avatar"><?= substr($project['manager_name'], 0, 2) ?></div>
                                <?= htmlspecialchars($project['manager_name']) ?>
                            </td>
                            <td>
                                <span class="status-badge status-active"><?= htmlspecialchars($project['stage_name']) ?></span>
                            </td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $project['probability'] * 100 ?>%"></div>
                                </div>
                                <div><?= round($project['probability'] * 100) ?>%</div>
                            </td>
                            <td>₽<?= number_format($project['project_revenue'] ?? 0, 0, ',', ' ') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>


    <script>
        // Инициализация графиков
        document.addEventListener('DOMContentLoaded', function() {
            // Projects by Stage Chart
            const stagesCtx = document.getElementById('stageChart').getContext('2d');
            new Chart(stagesCtx, {
                type: 'bar',
                data: {
                    labels: [<?= implode(',', array_map(function($item) { return "'" . addslashes($item['stage_name']) . "'"; }, $projectsByStage)) ?>],
                    datasets: [{
                        label: 'Количество проектов',
                        data: [<?= implode(',', array_column($projectsByStage, 'project_count')) ?>],
                        backgroundColor: '#7700FF',
                        borderColor: '#7700FF',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Revenue by Service Chart
            const servicesCtx = document.getElementById('serviceChart').getContext('2d');
            new Chart(servicesCtx, {
                type: 'doughnut',
                data: {
                    labels: [<?= implode(',', array_map(function($item) { return "'" . addslashes($item['service_name']) . "'"; }, $revenueByService)) ?>],
                    datasets: [{
                        data: [<?= implode(',', array_column($revenueByService, 'total_revenue')) ?>],
                        backgroundColor: [
                            '#7700FF', '#9D4EDD', '#C77DFF', '#E0AAFF', '#5A189A', '#7B2CBF'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });

            // Обработчики для строк таблицы
            document.querySelectorAll('.project-row').forEach(row => {
                row.addEventListener('click', function() {
                    const href = this.getAttribute('onclick')?.match(/location\.href='([^']+)'/)?.[1];
                    if (href) {
                        window.location.href = href;
                    }
                });
            });
        });
    </script>
</body>
</html>