<?php
// project-edit.php
require_once 'session.php';
require_once 'auth.php';
require_once 'database.php';

Auth::requireAuth();

// Получаем ID проекта из URL
$project_id = $_GET['id'] ?? null;

if (!$project_id) {
    header('Location: projects.php');
    exit;
}

// Загружаем данные проекта из БД для редактирования
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
            u_industry.full_name as industry_manager_name
        FROM projects p
        LEFT JOIN dictionaries d_stage ON p.stage_id = d_stage.id AND d_stage.type = 'stage'
        LEFT JOIN dictionaries d_service ON p.service_id = d_service.id AND d_service.type = 'service'
        LEFT JOIN dictionaries d_payment ON p.payment_type_id = d_payment.id AND d_payment.type = 'payment_type'
        LEFT JOIN dictionaries d_segment ON p.segment_id = d_segment.id AND d_segment.type = 'segment'
        LEFT JOIN dictionaries d_evaluation ON p.accepted_for_evaluation_id = d_evaluation.id AND d_evaluation.type = 'evaluation_status'
        LEFT JOIN users u_manager ON p.manager_id = u_manager.id
        LEFT JOIN users u_industry ON p.industry_manager_id = u_industry.id
        WHERE p.id = $1
    ", [$project_id]);

    // Если проект не найден
    if (empty($project)) {
        // Создаем демо-данные для отладки
        $project = [
            'id' => $project_id,
            'project_name' => 'Проект не найден',
            'organization_name' => 'Не указано',
            'organization_inn' => '0000000000',
            'project_number' => null,
            'stage_name' => 'Не определен',
            'service_name' => 'Не указано',
            'payment_type_name' => 'Не указано',
            'segment_name' => 'Не указано',
            'manager_name' => 'Не назначен',
            'industry_manager_name' => null,
            'probability' => 0.0,
            'implementation_year' => date('Y'),
            'is_industry_solution' => false,
            'is_forecast_accepted' => false,
            'is_dzo_implementation' => false,
            'needs_management_control' => false,
            'evaluation_status_name' => null,
            'current_status' => '',
            'period_achievements' => '',
            'next_period_plans' => '',
            'creation_date' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
    }

    // Загружаем справочники для формы
    $services = Database::fetchAll("SELECT id, name FROM dictionaries WHERE type = 'service' AND is_active = true ORDER BY sort_order");
    $payment_types = Database::fetchAll("SELECT id, name FROM dictionaries WHERE type = 'payment_type' AND is_active = true ORDER BY sort_order");
    $stages = Database::fetchAll("SELECT id, name, probability FROM dictionaries WHERE type = 'stage' AND is_active = true ORDER BY sort_order");
    $managers = Database::fetchAll("SELECT id, full_name FROM users WHERE is_active = true ORDER BY full_name");
    $segments = Database::fetchAll("SELECT id, name FROM dictionaries WHERE type = 'segment' AND is_active = true ORDER BY sort_order");
    $evaluation_statuses = Database::fetchAll("SELECT id, name FROM dictionaries WHERE type = 'evaluation_status' AND is_active = true ORDER BY sort_order");
    $industry_managers = Database::fetchAll("SELECT id, full_name FROM users WHERE is_active = true ORDER BY full_name");

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

} catch (Exception $e) {
    error_log("Project edit load error: " . $e->getMessage());
    // Создаем базовые данные для формы даже при ошибке
    $project = [
        'id' => $project_id,
        'project_name' => 'Ошибка загрузки',
        'organization_name' => '',
        'organization_inn' => '',
        'project_number' => null,
        'stage_name' => '',
        'service_name' => '',
        'payment_type_name' => '',
        'segment_name' => '',
        'manager_name' => '',
        'industry_manager_name' => null,
        'probability' => 0.0,
        'implementation_year' => date('Y'),
        'is_industry_solution' => false,
        'is_forecast_accepted' => false,
        'is_dzo_implementation' => false,
        'needs_management_control' => false,
        'evaluation_status_name' => null,
        'current_status' => '',
        'period_achievements' => '',
        'next_period_plans' => '',
        'creation_date' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    $services = $payment_types = $stages = $managers = $segments = $evaluation_statuses = $industry_managers = [];
    $revenues = $costs = [];
}

// Обработка формы сохранения
// Обработка формы сохранения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = Database::connect();
        pg_query($conn, "BEGIN");
        
        // Основные данные проекта
        $organization_name = pg_escape_string($_POST['organization_name']);
        $organization_inn = pg_escape_string($_POST['organization_inn']);
        $project_name = pg_escape_string($_POST['project_name']);
        $service_id = (int)$_POST['service_id'];
        $payment_type_id = (int)$_POST['payment_type_id'];
        $stage_id = (int)$_POST['stage_id'];
        $manager_id = (int)$_POST['manager_id'];
        $segment_id = !empty($_POST['segment_id']) ? (int)$_POST['segment_id'] : null;
        $implementation_year = (int)$_POST['implementation_year'];
        $current_status = pg_escape_string($_POST['current_status'] ?? '');
        $period_achievements = pg_escape_string($_POST['period_achievements'] ?? '');
        $next_period_plans = pg_escape_string($_POST['next_period_plans'] ?? '');
        
        // Булевы поля
        $is_industry_solution = isset($_POST['is_industry_solution']) ? 'true' : 'false';
        $is_forecast_accepted = isset($_POST['is_forecast_accepted']) ? 'true' : 'false';
        $is_dzo_implementation = isset($_POST['is_dzo_implementation']) ? 'true' : 'false';
        $needs_management_control = isset($_POST['needs_management_control']) ? 'true' : 'false';
        
        // Условные поля
        $industry_manager_id = !empty($_POST['industry_manager_id']) ? (int)$_POST['industry_manager_id'] : null;
        $project_number = !empty($_POST['project_number']) ? pg_escape_string($_POST['project_number']) : null;
        $accepted_for_evaluation_id = !empty($_POST['accepted_for_evaluation_id']) ? (int)$_POST['accepted_for_evaluation_id'] : null;
        
        // Получаем probability из этапа
        $probability_result = pg_query($conn, "SELECT probability FROM dictionaries WHERE id = $stage_id");
        if ($probability_row = pg_fetch_assoc($probability_result)) {
            $probability = (float)$probability_row['probability'];
        } else {
            $probability = 0.0;
        }
        
        // Текущий пользователь
        $current_user_id = $_SESSION['user_id'] ?? 1;
        
        // Обновление проекта
        $update_query = "
            UPDATE projects SET
                organization_name = '$organization_name',
                organization_inn = '$organization_inn',
                project_name = '$project_name',
                service_id = $service_id,
                payment_type_id = $payment_type_id,
                stage_id = $stage_id,
                probability = $probability,
                manager_id = $manager_id,
                segment_id = " . ($segment_id ?: 'NULL') . ",
                implementation_year = $implementation_year,
                is_industry_solution = $is_industry_solution,
                is_forecast_accepted = $is_forecast_accepted,
                is_dzo_implementation = $is_dzo_implementation,
                needs_management_control = $needs_management_control,
                accepted_for_evaluation_id = " . ($accepted_for_evaluation_id ?: 'NULL') . ",
                industry_manager_id = " . ($industry_manager_id ?: 'NULL') . ",
                project_number = " . ($project_number ? "'$project_number'" : 'NULL') . ",
                current_status = " . ($current_status ? "'$current_status'" : 'NULL') . ",
                period_achievements = " . ($period_achievements ? "'$period_achievements'" : 'NULL') . ",
                next_period_plans = " . ($next_period_plans ? "'$next_period_plans'" : 'NULL') . ",
                updated_by = $current_user_id,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = $project_id
        ";
        
        error_log("Executing update query: " . $update_query);
        $result = pg_query($conn, $update_query);
        if (!$result) {
            throw new Exception("Ошибка обновления проекта: " . pg_last_error($conn));
        }
        
// Удаляем старые финансовые данные
$delete_revenues = pg_query($conn, "DELETE FROM project_revenues WHERE project_id = $project_id");
if (!$delete_revenues) {
    throw new Exception("Ошибка удаления выручки: " . pg_last_error($conn));
}

$delete_costs = pg_query($conn, "DELETE FROM project_costs WHERE project_id = $project_id");
if (!$delete_costs) {
    throw new Exception("Ошибка удаления затрат: " . pg_last_error($conn));
}

// Сохранение выручки
if (isset($_POST['revenue']) && is_array($_POST['revenue'])) {
    foreach ($_POST['revenue'] as $revenue) {
        if (!empty($revenue['amount']) && $revenue['amount'] > 0) {
            $year = (int)$revenue['year'];
            $month = (int)$revenue['month'];
            $amount = (float)$revenue['amount'];
            $status_id = (int)$revenue['status'];
            
            // Используем INSERT ON CONFLICT для обновления существующих записей
            $revenue_query = "
                INSERT INTO project_revenues (project_id, year, month, amount, revenue_status_id)
                VALUES ($project_id, $year, $month, $amount, $status_id)
                ON CONFLICT (project_id, year, month) 
                DO UPDATE SET 
                    amount = EXCLUDED.amount,
                    revenue_status_id = EXCLUDED.revenue_status_id,
                    updated_at = CURRENT_TIMESTAMP
            ";
            $revenue_result = pg_query($conn, $revenue_query);
            if (!$revenue_result) {
                throw new Exception("Ошибка сохранения выручки: " . pg_last_error($conn));
            }
        }
    }
}

// Сохранение затрат
if (isset($_POST['cost']) && is_array($_POST['cost'])) {
    foreach ($_POST['cost'] as $cost) {
        if (!empty($cost['amount']) && $cost['amount'] > 0) {
            $year = (int)$cost['year'];
            $month = (int)$cost['month'];
            $amount = (float)$cost['amount'];
            $type_id = (int)$cost['type'];
            $status_id = (int)$cost['status'];
            
            // Используем INSERT ON CONFLICT для обновления существующих записей
            $cost_query = "
                INSERT INTO project_costs (project_id, year, month, amount, cost_type_id, cost_status_id)
                VALUES ($project_id, $year, $month, $amount, $type_id, $status_id)
                ON CONFLICT (project_id, year, month, cost_type_id) 
                DO UPDATE SET 
                    amount = EXCLUDED.amount,
                    cost_status_id = EXCLUDED.cost_status_id,
                    updated_at = CURRENT_TIMESTAMP
            ";
            $cost_result = pg_query($conn, $cost_query);
            if (!$cost_result) {
                throw new Exception("Ошибка сохранения затрат: " . pg_last_error($conn));
            }
        }
    }

        
    }



        

        
        pg_query($conn, "COMMIT");
        error_log("Project $project_id successfully updated");
        
        // Перенаправление на карточку проекта
        header('Location: project-card.php?id=' . $project_id . '&success=1');
        exit;
        
    } catch (Exception $e) {
        if (isset($conn)) {
            pg_query($conn, "ROLLBACK");
        }
        error_log("Project update error: " . $e->getMessage());
        $error = "Ошибка при сохранении проекта: " . $e->getMessage();
    }

}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование проекта - <?= htmlspecialchars($project['project_name']) ?> - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .edit-header {
            background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-bottom: 3px solid #ffc107;
        }
        
        .form-actions {
            position: sticky;
            bottom: 0;
            background: white;
            padding: 1.5rem 2rem;
            border-top: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .edit-notice {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #1565c0;
        }
        
        .financial-section {
            background: var(--light);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .financial-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .revenue-row, .cost-row {
            display: grid;
            grid-template-columns: 100px 120px 1fr 150px 80px;
            gap: 1rem;
            align-items: end;
            margin-bottom: 1rem;
            padding: 1rem;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-light);
        }
        
        .cost-row {
            grid-template-columns: 100px 120px 1fr 150px 150px 80px;
        }
        
        @media (max-width: 768px) {
            .revenue-row, .cost-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <?php require_once 'blocks/head.php'; ?>

    <!-- Edit Header -->
    <section class="edit-header">
        <div class="container">
            <div class="page-header-content">
                <div class="back-link">
                    <a href="project-card.php?id=<?= $project_id ?>">← Назад к карточке проекта</a>
                </div>
                <div class="page-title-row">
                    <h1>Редактирование проекта</h1>
                    <div class="page-actions">
                        <a href="project-card.php?id=<?= $project_id ?>" class="btn btn-secondary">Отмена</a>
                    </div>
                </div>
                <div class="project-meta">
                    <span class="project-id">ID: PRJ-<?= str_pad($project_id, 4, '0', STR_PAD_LEFT) ?></span>
                    <span class="project-date">
                        Последнее изменение: 
                        <?= !empty($project['updated_at']) ? date('d.m.Y H:i', strtotime($project['updated_at'])) : 'неизвестно' ?>
                    </span>
                </div>
            </div>
        </div>
    </section>

    <!-- Edit Form -->
    <section class="project-create">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" style="background: #fee; color: #c00; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    <?= $error ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success" style="background: #efe; color: #0a0; padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                    Проект успешно обновлен!
                </div>
            <?php endif; ?>
            
            <div class="edit-notice">
                <strong>Режим редактирования</strong> - вы можете изменить любые данные проекта. Все изменения будут сохранены в истории.
            </div>

            <form class="create-form" id="project-form" method="POST">
                <!-- Общая информация -->
                <div class="form-section active">
                    <h2 class="section-title">Общая информация по проекту</h2>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Название организации <span class="required">*</span></label>
                            <input type="text" class="form-input" name="organization_name" value="<?= htmlspecialchars($project['organization_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">ИНН организации <span class="required">*</span></label>
                            <input type="text" class="form-input" name="organization_inn" value="<?= $project['organization_inn'] ?>" pattern="[0-9]{10,12}" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Название проекта <span class="required">*</span></label>
                            <input type="text" class="form-input" name="project_name" value="<?= htmlspecialchars($project['project_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Услуга <span class="required">*</span></label>
                            <select class="form-select" name="service_id" required>
                                <option value="">Выберите услугу</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>" <?= isset($project['service_id']) && $service['id'] == $project['service_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($service['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Тип платежа <span class="required">*</span></label>
                            <select class="form-select" name="payment_type_id" required>
                                <option value="">Выберите тип платежа</option>
                                <?php foreach ($payment_types as $payment_type): ?>
                                    <option value="<?= $payment_type['id'] ?>" <?= isset($project['payment_type_id']) && $payment_type['id'] == $project['payment_type_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($payment_type['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Этап проекта <span class="required">*</span></label>
                            <select class="form-select" name="stage_id" required id="stage-select">
                                <option value="">Выберите этап</option>
                                <?php foreach ($stages as $stage): ?>
                                    <option value="<?= $stage['id'] ?>" data-probability="<?= $stage['probability'] ?>" <?= isset($project['stage_id']) && $stage['id'] == $project['stage_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($stage['name']) ?> (<?= round($stage['probability'] * 100) ?>%)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Вероятность реализации</label>
                            <input type="text" class="form-input auto-calculated" name="probability" value="<?= round(($project['probability'] ?? 0) * 100) ?>%" readonly>
                            <small>Заполняется автоматически на основе этапа</small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Менеджер <span class="required">*</span></label>
                            <select class="form-select" name="manager_id" required>
                                <option value="">Выберите менеджера</option>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?= $manager['id'] ?>" <?= isset($project['manager_id']) && $manager['id'] == $project['manager_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($manager['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Сегмент бизнеса</label>
                            <select class="form-select" name="segment_id">
                                <option value="">Выберите сегмент</option>
                                <?php foreach ($segments as $segment): ?>
                                    <option value="<?= $segment['id'] ?>" <?= isset($project['segment_id']) && $segment['id'] == $project['segment_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($segment['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Год реализации</label>
                            <input type="number" class="form-input" name="implementation_year" value="<?= $project['implementation_year'] ?>" min="2023" max="2030">
                        </div>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_industry_solution" <?= $project['is_industry_solution'] ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Отраслевое решение
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_forecast_accepted" <?= $project['is_forecast_accepted'] ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Принимаемый к прогнозу
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="is_dzo_implementation" <?= $project['is_dzo_implementation'] ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Реализация через ДЗО
                        </label>

                        <label class="checkbox-label">
                            <input type="checkbox" name="needs_management_control" <?= $project['needs_management_control'] ? 'checked' : '' ?>>
                            <span class="checkmark"></span>
                            Требуется контроль статуса на уровне руководства
                        </label>
                    </div>

                    <!-- Условные поля -->
                    <div class="conditional-field <?= $project['is_industry_solution'] ? 'show' : '' ?>" id="industry-fields">
                        <div class="form-grid" style="margin-top: 1.5rem;">
                            <div class="form-group">
                                <label class="form-label">Отраслевой менеджер</label>
                                <select class="form-select" name="industry_manager_id">
                                    <option value="">Выберите отраслевого менеджера</option>
                                    <?php foreach ($industry_managers as $manager): ?>
                                        <option value="<?= $manager['id'] ?>" <?= isset($project['industry_manager_id']) && $manager['id'] == $project['industry_manager_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($manager['full_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Номер проекта</label>
                                <input type="text" class="form-input" name="project_number" value="<?= htmlspecialchars($project['project_number'] ?? '') ?>" placeholder="Формат: XXX/XXX/XXXX">
                            </div>
                        </div>
                    </div>

                    <div class="conditional-field <?= $project['is_forecast_accepted'] ? 'show' : '' ?>" id="evaluation-field">
                        <div class="form-group" style="margin-top: 1.5rem; max-width: 300px;">
                            <label class="form-label">Принимаемый к оценке</label>
                            <select class="form-select" name="accepted_for_evaluation_id">
                                <option value="">Выберите статус</option>
                                <?php foreach ($evaluation_statuses as $status): ?>
                                    <option value="<?= $status['id'] ?>" <?= isset($project['accepted_for_evaluation_id']) && $status['id'] == $project['accepted_for_evaluation_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($status['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Финансовая информация -->
                <div class="form-section">
                    <h2 class="section-title">Информация по выручке проекта</h2>
                    
                    <div class="financial-section">
                        <div class="financial-header">
                            <h3 style="margin: 0;">Периоды выручки</h3>
                            <button type="button" class="btn btn-primary" id="add-revenue">+ Добавить период</button>
                        </div>
                        
                        <div id="revenue-container">
                            <?php if (!empty($revenues)): ?>
                                <?php foreach ($revenues as $index => $revenue): ?>
                                <div class="revenue-row">
                                    <div class="form-group">
                                        <label>Год</label>
                                        <select class="form-input" name="revenue[<?= $index ?>][year]">
                                            <?php for ($year = 2023; $year <= 2030; $year++): ?>
                                                <option value="<?= $year ?>" <?= $year == $revenue['year'] ? 'selected' : '' ?>><?= $year ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Месяц</label>
                                        <select class="form-input" name="revenue[<?= $index ?>][month]">
                                            <?php 
                                            $months = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
                                            foreach ($months as $key => $month): ?>
                                                <option value="<?= $key + 1 ?>" <?= ($key + 1) == $revenue['month'] ? 'selected' : '' ?>><?= $month ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Сумма (руб.)</label>
                                        <input type="number" class="form-input" name="revenue[<?= $index ?>][amount]" value="<?= $revenue['amount'] ?>" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="form-group">
                                        <label>Статус</label>
                                        <select class="form-input" name="revenue[<?= $index ?>][status]">
                                            <option value="35" <?= $revenue['revenue_status_id'] == 35 ? 'selected' : '' ?>>начислена</option>
                                            <option value="36" <?= $revenue['revenue_status_id'] == 36 ? 'selected' : '' ?>>прогнозное начисление</option>
                                            <option value="37" <?= $revenue['revenue_status_id'] == 37 ? 'selected' : '' ?>>начисление планируется</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-danger" onclick="this.closest('.revenue-row').remove()">Удалить</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h2 class="section-title" style="margin-top: 2rem;">Информация по затратам проекта</h2>
                    
                    <div class="financial-section">
                        <div class="financial-header">
                            <h3 style="margin: 0;">Периоды затрат</h3>
                            <button type="button" class="btn btn-primary" id="add-cost">+ Добавить период</button>
                        </div>
                        
                        <div id="cost-container">
                            <?php if (!empty($costs)): ?>
                                <?php foreach ($costs as $index => $cost): ?>
                                <div class="cost-row">
                                    <div class="form-group">
                                        <label>Год</label>
                                        <select class="form-input" name="cost[<?= $index ?>][year]">
                                            <?php for ($year = 2023; $year <= 2030; $year++): ?>
                                                <option value="<?= $year ?>" <?= $year == $cost['year'] ? 'selected' : '' ?>><?= $year ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Месяц</label>
                                        <select class="form-input" name="cost[<?= $index ?>][month]">
                                            <?php 
                                            $months = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
                                            foreach ($months as $key => $month): ?>
                                                <option value="<?= $key + 1 ?>" <?= ($key + 1) == $cost['month'] ? 'selected' : '' ?>><?= $month ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Сумма (руб.)</label>
                                        <input type="number" class="form-input" name="cost[<?= $index ?>][amount]" value="<?= $cost['amount'] ?>" step="0.01" placeholder="0.00">
                                    </div>
                                    <div class="form-group">
                                        <label>Вид затрат</label>
                                        <select class="form-input" name="cost[<?= $index ?>][type]">
                                            <option value="31" <?= $cost['cost_type_id'] == 31 ? 'selected' : '' ?>>Прямые</option>
                                            <option value="32" <?= $cost['cost_type_id'] == 32 ? 'selected' : '' ?>>Коммерческие</option>
                                            <option value="33" <?= $cost['cost_type_id'] == 33 ? 'selected' : '' ?>>РСД</option>
                                            <option value="34" <?= $cost['cost_type_id'] == 34 ? 'selected' : '' ?>>Штрафы</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Статус</label>
                                        <select class="form-input" name="cost[<?= $index ?>][status]">
                                            <option value="38" <?= $cost['cost_status_id'] == 38 ? 'selected' : '' ?>>начислены</option>
                                            <option value="39" <?= $cost['cost_status_id'] == 39 ? 'selected' : '' ?>>создан резерв</option>
                                            <option value="40" <?= $cost['cost_status_id'] == 40 ? 'selected' : '' ?>>отражение планируется</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <button type="button" class="btn btn-danger" onclick="this.closest('.cost-row').remove()">Удалить</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Дополнительная информация -->
                <div class="form-section">
                    <h2 class="section-title">Дополнительная информация</h2>
                    
                    <div class="form-group">
                        <label class="form-label">Текущий статус по проекту</label>
                        <textarea class="form-textarea" name="current_status" placeholder="Опишите текущий статус проекта (максимум 1000 символов)" maxlength="1000"><?= htmlspecialchars($project['current_status'] ?? '') ?></textarea>
                        <small class="char-count"><?= strlen($project['current_status'] ?? '') ?>/1000 символов</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Что сделано за период</label>
                        <textarea class="form-textarea" name="period_achievements" placeholder="Опишите выполненные работы за отчетный период (максимум 1000 символов)" maxlength="1000"><?= htmlspecialchars($project['period_achievements'] ?? '') ?></textarea>
                        <small class="char-count"><?= strlen($project['period_achievements'] ?? '') ?>/1000 символов</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Планы на следующий период</label>
                        <textarea class="form-textarea" name="next_period_plans" placeholder="Опишите планы на следующий отчетный период (максимум 1000 символов)" maxlength="1000"><?= htmlspecialchars($project['next_period_plans'] ?? '') ?></textarea>
                        <small class="char-count"><?= strlen($project['next_period_plans'] ?? '') ?>/1000 символов</small>
                    </div>
                </div>

                <!-- Действия формы -->
                <div class="form-actions">
                    <div class="action-buttons">
                        <a href="project-card.php?id=<?= $project_id ?>" class="btn btn-secondary">Отмена</a>
                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                    
                    <div class="form-info">
                        <small>Все изменения будут записаны в историю проекта</small>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        // Текущие счетчики для финансовых данных
        let revenueCounter = <?= !empty($revenues) ? count($revenues) : 0 ?>;
        let costCounter = <?= !empty($costs) ? count($costs) : 0 ?>;

        // Автоматическое вычисление вероятности
        const stageSelect = document.getElementById('stage-select');
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
        document.querySelectorAll('textarea').forEach(textarea => {
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
        document.getElementById('add-revenue').addEventListener('click', function() {
            revenueCounter++;
            const container = document.getElementById('revenue-container');
            const row = document.createElement('div');
            row.className = 'revenue-row';
            row.innerHTML = `
                <div class="form-group">
                    <label>Год</label>
                    <select class="form-input" name="revenue[${revenueCounter}][year]">
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025" selected>2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Месяц</label>
                    <select class="form-input" name="revenue[${revenueCounter}][month]">
                        ${Array.from({length: 12}, (_, i) => 
                            `<option value="${i + 1}">${['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'][i]}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Сумма (руб.)</label>
                    <input type="number" class="form-input" name="revenue[${revenueCounter}][amount]" placeholder="0.00" step="0.01">
                </div>
                <div class="form-group">
                    <label>Статус</label>
                    <select class="form-input" name="revenue[${revenueCounter}][status]">
                        <option value="35">начислена</option>
                        <option value="36">прогнозное начисление</option>
                        <option value="37">начисление планируется</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-danger" onclick="this.closest('.revenue-row').remove()">Удалить</button>
                </div>
            `;
            container.appendChild(row);
        });

        document.getElementById('add-cost').addEventListener('click', function() {
            costCounter++;
            const container = document.getElementById('cost-container');
            const row = document.createElement('div');
            row.className = 'cost-row';
            row.innerHTML = `
                <div class="form-group">
                    <label>Год</label>
                    <select class="form-input" name="cost[${costCounter}][year]">
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025" selected>2025</option>
                        <option value="2026">2026</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Месяц</label>
                    <select class="form-input" name="cost[${costCounter}][month]">
                        ${Array.from({length: 12}, (_, i) => 
                            `<option value="${i + 1}">${['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'][i]}</option>`
                        ).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Сумма (руб.)</label>
                    <input type="number" class="form-input" name="cost[${costCounter}][amount]" placeholder="0.00" step="0.01">
                </div>
                <div class="form-group">
                    <label>Вид затрат</label>
                    <select class="form-input" name="cost[${costCounter}][type]">
                        <option value="31">Прямые</option>
                        <option value="32">Коммерческие</option>
                        <option value="33">РСД</option>
                        <option value="34">Штрафы</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Статус</label>
                    <select class="form-input" name="cost[${costCounter}][status]">
                        <option value="38">начислены</option>
                        <option value="39">создан резерв</option>
                        <option value="40">отражение планируется</option>
                    </select>
                </div>
                <div class="form-group">
                    <button type="button" class="btn btn-danger" onclick="this.closest('.cost-row').remove()">Удалить</button>
                </div>
            `;
            container.appendChild(row);
        });

        // Валидация формы
        document.getElementById('project-form').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
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
                e.preventDefault();
                alert('Пожалуйста, заполните все обязательные поля');
            }
        });
    </script>
</body>
</html>