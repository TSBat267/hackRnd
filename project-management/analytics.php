<?php
require_once 'database.php';
require_once 'session.php';
require_once 'auth.php';

Auth::requireAuth();

// Получаем данные из БД для аналитики
try {
    $conn = Database::connect();

    // Общее количество проектов
    $total_projects_result = pg_query($conn, "SELECT COUNT(*) as count FROM projects");
    $total_projects = pg_fetch_assoc($total_projects_result)['count'];

    // Общая выручка (используем project_revenues если есть, иначе 0)
    $total_revenue = 0;
    $revenue_result = pg_query($conn, "
        SELECT SUM(pr.amount) as total 
        FROM project_revenues pr 
        JOIN projects p ON p.id = pr.project_id
    ");
    if ($revenue_result) {
        $revenue_data = pg_fetch_assoc($revenue_result);
        $total_revenue = $revenue_data['total'] ?: 0;
    }

    // Средняя вероятность
    $avg_probability_result = pg_query($conn, "SELECT AVG(probability) as avg FROM projects WHERE probability IS NOT NULL");
    $avg_probability = round(pg_fetch_assoc($avg_probability_result)['avg'] ?: 0);

    // Проекты по этапам (stage_id -> нужно получить названия этапов)
    $stages_result = pg_query($conn, "SELECT stage_id, COUNT(*) as count FROM projects GROUP BY stage_id");
    $stages = pg_fetch_all($stages_result) ?: [];

    // Проекты по менеджерам
    $managers_result = pg_query($conn, "
        SELECT manager_id, COUNT(*) as count 
        FROM projects 
        WHERE manager_id IS NOT NULL 
        GROUP BY manager_id
    ");
    $managers = pg_fetch_all($managers_result) ?: [];

    // Проекты по годам реализации
    $years_result = pg_query($conn, "
        SELECT implementation_year, COUNT(*) as count 
        FROM projects 
        WHERE implementation_year IS NOT NULL 
        GROUP BY implementation_year 
        ORDER BY implementation_year
    ");
    $years = pg_fetch_all($years_result) ?: [];

    // Последние проекты для таблицы
    $recent_projects_result = pg_query($conn, "
        SELECT 
            id,
            organization_name,
            project_name,
            stage_id,
            probability,
            implementation_year,
            creation_date
        FROM projects 
        ORDER BY created_at DESC 
        LIMIT 10
    ");

    // Основные метрики
    $stats_query = "
        SELECT 
            COUNT(*) as total_projects,
            COUNT(CASE WHEN stage_id NOT IN (SELECT id FROM dictionaries WHERE name LIKE '%Успех%') THEN 1 END) as active_projects,
            COUNT(CASE WHEN DATE_TRUNC('month', creation_date) = DATE_TRUNC('month', CURRENT_DATE) THEN 1 END) as new_this_month,
            COALESCE(SUM((SELECT SUM(amount) FROM project_revenues pr WHERE pr.project_id = p.id)), 0) as total_revenue,
            AVG(probability) as avg_probability,
            COUNT(CASE WHEN stage_id IN (SELECT id FROM dictionaries WHERE name LIKE '%Успех%') THEN 1 END) as completed_projects
        FROM projects p
    ";
    $stats_result = pg_query($conn, $stats_query);
    $stats = pg_fetch_assoc($stats_result);

    // Проекты по этапам
    $stages_query = "
        SELECT d.name as stage_name, COUNT(p.id) as project_count,
               ROUND(COALESCE(AVG(p.probability), 0) * 100) as avg_probability_percent,
               COALESCE(SUM(pr.amount), 0) as total_revenue
        FROM dictionaries d
        LEFT JOIN projects p ON d.id = p.stage_id AND d.type = 'stage'
        LEFT JOIN project_revenues pr ON p.id = pr.project_id
        WHERE d.type = 'stage'
        GROUP BY d.id, d.name, d.sort_order
        ORDER BY d.sort_order
    ";
    $stages_result = pg_query($conn, $stages_query);
    $stages = pg_fetch_all($stages_result) ?: [];

    // Выручка по услугам
    $services_query = "
        SELECT d.name as service_name, 
               COALESCE(SUM(pr.amount), 0) as total_revenue,
               COUNT(p.id) as project_count,
               ROUND(COALESCE(AVG(p.probability), 0) * 100) as avg_probability
        FROM dictionaries d
        LEFT JOIN projects p ON d.id = p.service_id AND d.type = 'service'
        LEFT JOIN project_revenues pr ON p.id = pr.project_id
        WHERE d.type = 'service'
        GROUP BY d.id, d.name
        ORDER BY total_revenue DESC
    ";
    $services_result = pg_query($conn, $services_query);
    $services = pg_fetch_all($services_result) ?: [];

    // Эффективность менеджеров
    $managers_query = "
        SELECT u.full_name as manager_name,
               COUNT(p.id) as project_count,
               COALESCE(SUM(pr.amount), 0) as total_revenue,
               ROUND(COALESCE(AVG(p.probability), 0) * 100) as avg_probability,
               COUNT(CASE WHEN p.stage_id IN (SELECT id FROM dictionaries WHERE name LIKE '%Успех%') THEN 1 END) as completed_projects
        FROM users u
        LEFT JOIN projects p ON u.id = p.manager_id
        LEFT JOIN project_revenues pr ON p.id = pr.project_id
        WHERE u.is_active = true
        GROUP BY u.id, u.full_name
        HAVING COUNT(p.id) > 0
        ORDER BY total_revenue DESC
        LIMIT 10
    ";
    $managers_result = pg_query($conn, $managers_query);
    $managers = pg_fetch_all($managers_result) ?: [];

    // Динамика по месяцам
    $monthly_query = "
        SELECT 
            DATE_TRUNC('month', p.creation_date) as month,
            COUNT(p.id) as new_projects,
            COALESCE(SUM(pr.amount), 0) as monthly_revenue
        FROM projects p
        LEFT JOIN project_revenues pr ON p.id = pr.project_id 
            AND EXTRACT(YEAR FROM p.creation_date) = pr.year 
            AND EXTRACT(MONTH FROM p.creation_date) = pr.month
        WHERE p.creation_date >= CURRENT_DATE - INTERVAL '6 months'
        GROUP BY DATE_TRUNC('month', p.creation_date)
        ORDER BY month DESC
        LIMIT 6
    ";
    $monthly_result = pg_query($conn, $monthly_query);
    $monthly_data = pg_fetch_all($monthly_result) ?: [];

    // Последние проекты для таблицы
    $recent_projects_query = "
        SELECT p.*, 
               d_stage.name as stage_name,
               d_service.name as service_name,
               u.full_name as manager_name,
               (SELECT SUM(amount) FROM project_revenues pr WHERE pr.project_id = p.id) as total_revenue
        FROM projects p
        LEFT JOIN dictionaries d_stage ON p.stage_id = d_stage.id
        LEFT JOIN dictionaries d_service ON p.service_id = d_service.id
        LEFT JOIN users u ON p.manager_id = u.id
        ORDER BY p.updated_at DESC
        LIMIT 10
    ";
    $recent_projects_result = pg_query($conn, $recent_projects_query);
    $recent_projects = pg_fetch_all($recent_projects_result) ?: [];

} catch (Exception $e) {
    error_log("Analytics data loading error: " . $e->getMessage());
    $stats = [];
    $stages = $services = $managers = $monthly_data = $recent_projects = [];
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Аналитика - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 3rem 0;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
        }

        .metric-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .chart-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .chart-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--light);
            border-radius: 8px;
        }

        .stat-label {
            color: var(--gray);
            font-size: 0.9rem;
        }

        .stat-value {
            font-weight: 600;
            color: var(--dark);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once 'blocks/head.php'; ?>

    <!-- Analytics Header -->
    <section class="analytics-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Аналитика проектов</h1>
                <p>Обзор ключевых метрик и показателей эффективности</p>
            </div>
        </div>
    </section>

    <!-- Main Analytics Content -->
    <section class="analytics-content">
        <div class="container">
            <!-- Key Metrics -->
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-value"><?= $stats['total_projects'] ?? 0 ?></div>
                    <div class="metric-label">Всего проектов</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?= $stats['active_projects'] ?? 0 ?></div>
                    <div class="metric-label">Активных проектов</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?= number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') ?> ₽</div>
                    <div class="metric-label">Общая выручка</div>
                </div>
                <div class="metric-card">
                    <div class="metric-value"><?= round(($stats['avg_probability'] ?? 0) * 100) ?>%</div>
                    <div class="metric-label">Средняя вероятность</div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="chart-grid">
                <!-- Projects by Stage -->
                <div class="chart-card">
                    <div class="chart-title">Проекты по этапам</div>
                    <canvas id="stagesChart" height="250"></canvas>
                </div>

                <!-- Revenue by Service -->
                <div class="chart-card">
                    <div class="chart-title">Выручка по услугам</div>
                    <canvas id="servicesChart" height="250"></canvas>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="chart-grid">
                <!-- Manager Performance -->
                <div class="chart-card">
                    <div class="chart-title">Топ-10 менеджеров по выручке</div>
                    <canvas id="managersChart" height="300"></canvas>
                </div>

                <!-- Monthly Dynamics -->
                <div class="chart-card">
                    <div class="chart-title">Динамика за 6 месяцев</div>
                    <canvas id="monthlyChart" height="300"></canvas>
                </div>
            </div>

            <!-- Recent Projects -->
            <div class="table-card" style="margin-top: 2rem;">
                <div class="table-header">
                    <h3>Последние проекты</h3>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Проект</th>
                                <th>Организация</th>
                                <th>Услуга</th>
                                <th>Менеджер</th>
                                <th>Этап</th>
                                <th>Вероятность</th>
                                <th>Выручка</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_projects as $project): ?>
                            <tr>
                                <td><?= htmlspecialchars($project['project_name']) ?></td>
                                <td><?= htmlspecialchars($project['organization_name']) ?></td>
                                <td><?= htmlspecialchars($project['service_name']) ?></td>
                                <td><?= htmlspecialchars($project['manager_name']) ?></td>
                                <td>
                                    <span class="status-badge"><?= htmlspecialchars($project['stage_name']) ?></span>
                                </td>
                                <td>
                                    <div class="progress-bar small">
                                        <div class="progress-fill" style="width: <?= round($project['probability'] * 100) ?>%"></div>
                                    </div>
                                    <span style="font-size: 0.8rem;"><?= round($project['probability'] * 100) ?>%</span>
                                </td>
                                <td><?= number_format($project['total_revenue'] ?? 0, 0, ',', ' ') ?> ₽</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Projects by Stage Chart
        const stagesCtx = document.getElementById('stagesChart').getContext('2d');
        const stagesChart = new Chart(stagesCtx, {
            type: 'doughnut',
            data: {
                labels: [<?= implode(',', array_map(function($stage) { return "'" . addslashes($stage['stage_name']) . "'"; }, $stages)) ?>],
                datasets: [{
                    data: [<?= implode(',', array_column($stages, 'project_count')) ?>],
                    backgroundColor: [
                        '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                        '#06b6d4', '#84cc16', '#f97316', '#6366f1', '#ec4899'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Revenue by Service Chart
        const servicesCtx = document.getElementById('servicesChart').getContext('2d');
        const servicesChart = new Chart(servicesCtx, {
            type: 'bar',
            data: {
                labels: [<?= implode(',', array_map(function($service) { return "'" . addslashes($service['service_name']) . "'"; }, $services)) ?>],
                datasets: [{
                    label: 'Выручка (руб.)',
                    data: [<?= implode(',', array_column($services, 'total_revenue')) ?>],
                    backgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('ru-RU') + ' ₽';
                            }
                        }
                    }
                }
            }
        });

        // Manager Performance Chart
        const managersCtx = document.getElementById('managersChart').getContext('2d');
        const managersChart = new Chart(managersCtx, {
            type: 'bar',
            data: {
                labels: [<?= implode(',', array_map(function($manager) { return "'" . addslashes($manager['manager_name']) . "'"; }, $managers)) ?>],
                datasets: [{
                    label: 'Выручка',
                    data: [<?= implode(',', array_column($managers, 'total_revenue')) ?>],
                    backgroundColor: '#10b981',
                    order: 2
                }, {
                    label: 'Средняя вероятность',
                    data: [<?= implode(',', array_column($managers, 'avg_probability')) ?>],
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    yAxisID: 'y1',
                    order: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('ru-RU') + ' ₽';
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });

        // Monthly Dynamics Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [<?= implode(',', array_map(function($month) { 
                    $date = date_create($month['month']);
                    return "'" . date_format($date, 'M Y') . "'"; 
                }, array_reverse($monthly_data))) ?>],
                datasets: [{
                    label: 'Новые проекты',
                    data: [<?= implode(',', array_column(array_reverse($monthly_data), 'new_projects')) ?>],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    yAxisID: 'y',
                    fill: true
                }, {
                    label: 'Выручка',
                    data: [<?= implode(',', array_column(array_reverse($monthly_data), 'monthly_revenue')) ?>],
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left'
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('ru-RU') + ' ₽';
                            }
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>