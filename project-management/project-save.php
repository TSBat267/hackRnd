<?php
require_once 'database.php';
require_once 'session.php';
require_once 'auth.php';

Auth::requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = Database::connect();
        
        // Начало транзакции
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
        $probability_row = pg_fetch_assoc($probability_result);
        $probability = $probability_row['probability'];
        
        // Текущий пользователь
        $current_user_id = $_SESSION['user_id'] ?? 1;
        
        // Вставка проекта
        $project_query = "
            INSERT INTO projects (
                organization_name, organization_inn, project_name, service_id, payment_type_id,
                stage_id, probability, manager_id, segment_id, implementation_year,
                is_industry_solution, is_forecast_accepted, is_dzo_implementation,
                needs_management_control, accepted_for_evaluation_id, industry_manager_id,
                project_number, current_status, period_achievements, next_period_plans,
                created_by, updated_by
            ) VALUES (
                '$organization_name', '$organization_inn', '$project_name', $service_id, $payment_type_id,
                $stage_id, $probability, $manager_id, " . ($segment_id ?: 'NULL') . ", $implementation_year,
                $is_industry_solution, $is_forecast_accepted, $is_dzo_implementation,
                $needs_management_control, " . ($accepted_for_evaluation_id ?: 'NULL') . ", 
                " . ($industry_manager_id ?: 'NULL') . ", " . ($project_number ? "'$project_number'" : 'NULL') . ",
                " . ($current_status ? "'$current_status'" : 'NULL') . ", 
                " . ($period_achievements ? "'$period_achievements'" : 'NULL') . ",
                " . ($next_period_plans ? "'$next_period_plans'" : 'NULL') . ",
                $current_user_id, $current_user_id
            ) RETURNING id
        ";
        
        $project_result = pg_query($conn, $project_query);
        $project_id = pg_fetch_result($project_result, 0);
        
        // Сохранение выручки
        if (isset($_POST['revenue'])) {
            foreach ($_POST['revenue'] as $revenue) {
                $year = (int)$revenue['year'];
                $month = (int)$revenue['month'];
                $amount = (float)$revenue['amount'];
                $status_id = (int)$revenue['status'];
                
                if ($amount > 0) {
                    $revenue_query = "
                        INSERT INTO project_revenues (project_id, year, month, amount, revenue_status_id)
                        VALUES ($project_id, $year, $month, $amount, $status_id)
                    ";
                    pg_query($conn, $revenue_query);
                }
            }
        }
        
        // Сохранение затрат
        if (isset($_POST['cost'])) {
            foreach ($_POST['cost'] as $cost) {
                $year = (int)$cost['year'];
                $month = (int)$cost['month'];
                $amount = (float)$cost['amount'];
                $type_id = (int)$cost['type'];
                $status_id = (int)$cost['status'];
                
                if ($amount > 0) {
                    $cost_query = "
                        INSERT INTO project_costs (project_id, year, month, amount, cost_type_id, cost_status_id)
                        VALUES ($project_id, $year, $month, $amount, $type_id, $status_id)
                    ";
                    pg_query($conn, $cost_query);
                }
            }
        }
        
        // Завершение транзакции
        pg_query($conn, "COMMIT");
        
        // Перенаправление на страницу успеха
        header('Location: project-card.php?id=' . $project_id);
        exit;
        
    } catch (Exception $e) {
        // Откат транзакции в случае ошибки
        pg_query($conn, "ROLLBACK");
        error_log("Project save error: " . $e->getMessage());
        header('Location: project-create.php?error=1');
        exit;
    }
} else {
    header('Location: project-create.php');
    exit;
}
?>