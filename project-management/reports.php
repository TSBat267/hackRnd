<?php
// reports.php
require_once 'session.php';
require_once 'auth.php';
require_once 'database.php';

Auth::requireAuth();

// Загружаем доступные поля для отчетов
$available_fields = [
    'organization_name' => 'Название организации',
    'organization_inn' => 'ИНН организации',
    'project_name' => 'Название проекта',
    'service_name' => 'Услуга',
    'payment_type_name' => 'Тип платежа',
    'stage_name' => 'Этап проекта',
    'probability' => 'Вероятность реализации',
    'manager_name' => 'Менеджер',
    'segment_name' => 'Сегмент бизнеса',
    'implementation_year' => 'Год реализации',
    'total_revenue' => 'Сумма выручки',
    'total_costs' => 'Сумма затрат',
    'creation_date' => 'Дата создания',
    'current_status' => 'Текущий статус'
];

// Загружаем сохраненные отчеты пользователя
$user_reports = [];
try {
    $user_reports = Database::fetchAll(
        "SELECT * FROM user_reports WHERE user_id = $1 ORDER BY created_at DESC",
        [$_SESSION['user_id']]
    );
} catch (Exception $e) {
    error_log("User reports load error: " . $e->getMessage());
}

// Загружаем данные для фильтров
try {
    $services = Database::fetchAll("SELECT id, name FROM dictionaries WHERE type = 'service' AND is_active = true");
    $stages = Database::fetchAll("SELECT id, name FROM dictionaries WHERE type = 'stage' AND is_active = true");
    $managers = Database::fetchAll("SELECT id, full_name FROM users WHERE is_active = true");
    $segments = Database::fetchAll("SELECT id, name FROM dictionaries WHERE type = 'segment' AND is_active = true");
} catch (Exception $e) {
    error_log("Reports load error: " . $e->getMessage());
    $services = $stages = $managers = $segments = [];
}

// Обработка действий с отчетами
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_report'])) {
        // Сохранение отчета
        $report_name = $_POST['report_name'] ?? 'Без названия';
        $report_config = [
            'fields' => $_POST['fields'] ?? [],
            'filters' => $_POST['filters'] ?? [],
            'export_format' => $_POST['export_format'] ?? 'excel'
        ];
        
        try {
            Database::execute(
                "INSERT INTO user_reports (user_id, report_name, report_config) VALUES ($1, $2, $3)",
                [$_SESSION['user_id'], $report_name, json_encode($report_config, JSON_UNESCAPED_UNICODE)]
            );
            $success = "Отчет успешно сохранен!";
        } catch (Exception $e) {
            error_log("Save report error: " . $e->getMessage());
            $error = "Ошибка при сохранении отчета: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['load_report'])) {
        // Загрузка сохраненного отчета
        $report_id = (int)$_POST['report_id'];
        try {
            $saved_report = Database::fetchOne(
                "SELECT * FROM user_reports WHERE id = $1 AND user_id = $2",
                [$report_id, $_SESSION['user_id']]
            );
            
            if ($saved_report) {
                $config = json_decode($saved_report['report_config'], true);
                $selected_fields = $config['fields'] ?? [];
                $filters = $config['filters'] ?? [];
                $export_format = $config['export_format'] ?? 'excel';
                $report_name = $saved_report['report_name'];
            }
        } catch (Exception $e) {
            error_log("Load report error: " . $e->getMessage());
            $error = "Ошибка при загрузке отчета: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['delete_report'])) {
        // Удаление отчета
        $report_id = (int)$_POST['report_id'];
        try {
            Database::execute(
                "DELETE FROM user_reports WHERE id = $1 AND user_id = $2",
                [$report_id, $_SESSION['user_id']]
            );
            $success = "Отчет успешно удален!";
            // Обновляем список отчетов
            $user_reports = Database::fetchAll(
                "SELECT * FROM user_reports WHERE user_id = $1 ORDER BY created_at DESC",
                [$_SESSION['user_id']]
            );
        } catch (Exception $e) {
            error_log("Delete report error: " . $e->getMessage());
            $error = "Ошибка при удалении отчета: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['install_report'])) {
        // Установка отчета как шаблона
        $report_id = (int)$_POST['report_id'];
        try {
            $report = Database::fetchOne(
                "SELECT * FROM user_reports WHERE id = $1 AND user_id = $2",
                [$report_id, $_SESSION['user_id']]
            );
            
            if ($report) {
                // Создаем файл шаблона для скачивания
                $config = json_decode($report['report_config'], true);
                $template_data = [
                    'report_name' => $report['report_name'],
                    'config' => $config,
                    'created_at' => $report['created_at'],
                    'version' => '1.0'
                ];
                
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="' . $report['report_name'] . '.rtkreport"');
                echo json_encode($template_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                exit;
            }
        } catch (Exception $e) {
            error_log("Install report error: " . $e->getMessage());
            $error = "Ошибка при установке отчета: " . $e->getMessage();
        }
    }
    elseif (isset($_POST['import_report'])) {
        // Импорт отчета из файла
        if (isset($_FILES['report_file']) && $_FILES['report_file']['error'] === UPLOAD_ERR_OK) {
            $file_content = file_get_contents($_FILES['report_file']['tmp_name']);
            $template_data = json_decode($file_content, true);
            
            if ($template_data && isset($template_data['report_name']) && isset($template_data['config'])) {
                try {
                    Database::execute(
                        "INSERT INTO user_reports (user_id, report_name, report_config) VALUES ($1, $2, $3)",
                        [$_SESSION['user_id'], $template_data['report_name'], json_encode($template_data['config'], JSON_UNESCAPED_UNICODE)]
                    );
                    $success = "Отчет успешно импортирован!";
                    // Обновляем список отчетов
                    $user_reports = Database::fetchAll(
                        "SELECT * FROM user_reports WHERE user_id = $1 ORDER BY created_at DESC",
                        [$_SESSION['user_id']]
                    );
                } catch (Exception $e) {
                    error_log("Import report error: " . $e->getMessage());
                    $error = "Ошибка при импорте отчета: " . $e->getMessage();
                }
            } else {
                $error = "Неверный формат файла отчета";
            }
        } else {
            $error = "Ошибка загрузки файла";
        }
    }
}

// Генерация отчета (существующий код)
$report_data = [];
if (isset($_POST['generate']) || isset($_POST['export'])) {
    // ... существующий код генерации отчета ...
    // Сохраняем в сессии для экспорта
    if (!empty($report_data)) {
        $_SESSION['report_data'] = $report_data;
        $_SESSION['selected_fields'] = $selected_fields;
        $_SESSION['report_name'] = $report_name;
        $_SESSION['available_fields'] = $available_fields;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Конструктор отчетов - Ростелеком</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container {
            max-width: 1600px !important;
        }

        .reports-layout {
            display: grid;
            grid-template-columns: 450px 1fr;
            gap: 2.5rem;
            margin-top: 2rem;
        }
        
        .saved-reports-sidebar {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            max-height: 700px;
            overflow-y: auto;
        }
        
        .saved-reports-sidebar h3 {
            margin-bottom: 1.5rem;
            color: var(--dark);
            font-size: 1.3rem;
        }
        
        .report-item {
            padding: 1.25rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fafafa;
        }
        
        .report-item:hover {
            border-color: var(--primary);
            background: var(--light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .report-item.active {
            border-color: var(--primary);
            background: #f0f8ff;
        }
        
        .report-item-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 0.75rem;
            gap: 1rem;
        }
        
        .report-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
            flex: 1;
        }
        
        .report-date {
            font-size: 0.85rem;
            color: var(--gray);
            white-space: nowrap;
        }
        
        .report-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        
        .btn-small {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            border-radius: 6px;
        }
        
        .import-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-light);
        }
        
        .file-upload {
            border: 2px dashed var(--gray-light);
            border-radius: var(--radius);
            padding: 2rem;
            text-align: center;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: border-color 0.3s ease;
            background: #fafafa;
        }
        
        .file-upload:hover {
            border-color: var(--primary);
            background: #f0f8ff;
        }
        
        .file-upload input {
            display: none;
        }
        
        .upload-icon {
            font-size: 2.5rem;
            color: var(--gray);
            margin-bottom: 0.75rem;
        }

        /* Основной контент */
        .main-content-grid {
            display: grid;
            grid-template-columns: 500px 1fr;
            gap: 2.5rem;
            min-height: 800px;
        }
        
        .report-config {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            height: fit-content;
        }
        
        .config-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--gray-light);
        }

        .config-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .config-section h3 {
            margin-bottom: 1.5rem;
            color: var(--dark);
            font-size: 1.3rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .form-group input[type="text"] {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 1rem;
        }
        
        .fields-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            max-height: 450px;
            overflow-y: auto;
            padding: 1rem;
            background: #fafafa;
            border-radius: var(--radius);
        }
        
        .available-fields, .selected-fields {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-light);
        }
        
        .available-fields h4, .selected-fields h4 {
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .field-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .field-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .field-item:hover {
            background: var(--light);
            border-color: var(--primary);
        }
        
        .selected-list {
            min-height: 200px;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .selected-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: var(--light);
            border: 1px solid var(--primary-light);
            border-radius: 6px;
            color: var(--primary);
            font-weight: 500;
        }
        
        .remove-field {
            background: none;
            border: none;
            color: var(--danger);
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        
        .remove-field:hover {
            background: var(--danger);
            color: white;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 1fr 140px 1fr 50px;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: center;
            padding: 1rem;
            background: #fafafa;
            border-radius: var(--radius);
        }
        
        .filter-row select,
        .filter-row input {
            padding: 0.75rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 0.95rem;
        }
        
        .remove-filter {
            background: var(--danger);
            color: white;
            border: none;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .remove-filter:hover {
            background: #dc2626;
        }
        
        #add-filter {
            width: 100%;
            padding: 1rem;
            font-size: 1rem;
            margin-top: 1rem;
        }
        
        .export-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .radio {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .radio:hover {
            border-color: var(--primary);
            background: var(--light);
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-light);
            gap: 1rem;
        }
        
        .form-actions > div {
            display: flex;
            gap: 1rem;
        }
        
        .report-preview {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: fit-content;
        }
        
        .preview-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }
        
        .preview-header h3 {
            margin: 0;
            color: var(--dark);
            font-size: 1.3rem;
        }
        
        .preview-actions {
            display: flex;
            gap: 1rem;
        }
        
        .preview-content {
            flex: 1;
            overflow: auto;
            min-height: 600px;
        }
        
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }
        
        .preview-table th {
            background: var(--light);
            padding: 1.25rem;
            text-align: left;
            font-weight: 600;
            color: var(--dark);
            border-bottom: 2px solid var(--gray-light);
            white-space: nowrap;
        }
        
        .preview-table td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-light);
            vertical-align: top;
        }
        
        .preview-table tr:hover {
            background: #f8fafc;
        }
        
        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--gray);
        }
        
        .empty-state h4 {
            margin-bottom: 1rem;
            color: var(--dark);
        }

        /* Адаптивность */
        @media (max-width: 1400px) {
            .reports-layout {
                grid-template-columns: 400px 1fr;
                gap: 2rem;
            }
            
            .main-content-grid {
                grid-template-columns: 450px 1fr;
                gap: 2rem;
            }
        }
        
        @media (max-width: 1200px) {
            .reports-layout {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .main-content-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .saved-reports-sidebar {
                max-height: 300px;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'blocks/head.php'; ?>

    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Конструктор отчетов</h1>
                <p>Создавайте, сохраняйте и устанавливайте пользовательские отчеты</p>
            </div>
        </div>
    </section>

    <section class="reports-section">
        <div class="container">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <div class="reports-layout">
                <!-- Боковая панель с сохраненными отчетами -->
                <div class="saved-reports-sidebar">
                    <h3>📊 Мои отчеты</h3>
                    
                    <?php if (!empty($user_reports)): ?>
                        <?php foreach ($user_reports as $report): ?>
                            <div class="report-item" onclick="loadReport(<?= $report['id'] ?>)">
                                <div class="report-item-header">
                                    <div class="report-name"><?= htmlspecialchars($report['report_name']) ?></div>
                                    <div class="report-date">
                                        <?= date('d.m.Y', strtotime($report['created_at'])) ?>
                                    </div>
                                </div>
                                <div class="report-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                        <button type="submit" name="install_report" class="btn-small btn-primary" title="Установить на компьютер">
                                            ⬇️ Скачать
                                        </button>
                                    </form>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить этот отчет?')">
                                        <input type="hidden" name="report_id" value="<?= $report['id'] ?>">
                                        <button type="submit" name="delete_report" class="btn-small btn-danger" title="Удалить отчет">
                                            🗑️ Удалить
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="text-align: center; color: var(--gray); padding: 3rem;">
                            📝 Нет сохраненных отчетов
                        </div>
                    <?php endif; ?>
                    
                    <!-- Импорт отчетов -->
                    <div class="import-section">
                        <h4>📁 Импорт отчета</h4>
                        <form method="POST" enctype="multipart/form-data" id="import-form">
                            <div class="file-upload" onclick="document.getElementById('report_file').click()">
                                <div class="upload-icon">📁</div>
                                <div>Нажмите для выбора файла отчета</div>
                                <div style="font-size: 0.85rem; color: var(--gray); margin-top: 0.75rem;">
                                    Поддерживаемый формат: .rtkreport
                                </div>
                            </div>
                            <input type="file" name="report_file" id="report_file" accept=".rtkreport" 
                                   onchange="document.getElementById('import-form').submit()" style="display: none;">
                        </form>
                    </div>
                </div>

                <!-- Основной контент -->
                <div class="main-content-grid">
                    <div class="report-config">
                        <form method="POST" id="report-form">
                            <div class="config-section">
                                <h3>⚙️ Параметры отчета</h3>
                                
                                <div class="form-group">
                                    <label>Название отчета</label>
                                    <input type="text" name="report_name" placeholder="Введите название отчета" 
                                           value="<?= htmlspecialchars($report_name ?? '') ?>" required>
                                </div>

                                <div class="form-group">
                                    <label>Выберите поля для отчета</label>
                                    <div class="fields-selector">
                                        <div class="available-fields">
                                            <h4>Доступные поля</h4>
                                            <div class="field-list">
                                                <?php foreach ($available_fields as $key => $label): ?>
                                                    <label class="field-item">
                                                        <input type="checkbox" name="fields[]" value="<?= $key ?>" 
                                                               <?= isset($selected_fields) && in_array($key, $selected_fields) ? 'checked' : '' ?>>
                                                        <?= $label ?>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="selected-fields">
                                            <h4>Выбранные поля</h4>
                                            <div class="selected-list" id="selected-fields-list">
                                                <!-- Динамически заполняется через JS -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="config-section">
                                <h3>🔍 Фильтры</h3>
                                <div class="filters-container" id="filters-container">
                                    <!-- Фильтры будут добавляться динамически -->
                                </div>
                                <button type="button" class="btn btn-secondary" id="add-filter">+ Добавить фильтр</button>
                            </div>

                            <div class="config-section">
                                <h3>📤 Настройки экспорта</h3>
                                <div class="export-options">
                                    <label class="radio">
                                        <input type="radio" name="export_format" value="excel" 
                                               <?= ($export_format ?? 'excel') === 'excel' ? 'checked' : '' ?>>
                                        <span class="radiomark"></span>
                                        📊 Excel
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="export_format" value="pdf"
                                               <?= ($export_format ?? 'excel') === 'pdf' ? 'checked' : '' ?>>
                                        <span class="radiomark"></span>
                                        📄 PDF
                                    </label>
                                    <label class="radio">
                                        <input type="radio" name="export_format" value="csv"
                                               <?= ($export_format ?? 'excel') === 'csv' ? 'checked' : '' ?>>
                                        <span class="radiomark"></span>
                                        📝 CSV
                                    </label>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="save_report" class="btn btn-success">💾 Сохранить отчет</button>
                                <div>
                                    <button type="submit" name="generate" class="btn btn-primary">🔄 Сгенерировать</button>
                                    <button type="submit" name="export" class="btn btn-secondary">📤 Экспорт</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="report-preview">
                        <div class="preview-header">
                            <h3>👁️ Предпросмотр отчета</h3>
                            <div class="preview-actions">
                                <button type="submit" form="report-form" name="generate" class="btn btn-secondary">Обновить</button>
                                <button type="submit" form="report-form" name="export" class="btn btn-primary">Экспорт</button>
                            </div>
                        </div>
                        <div class="preview-content">
                            <?php if (!empty($report_data)): ?>
                                <table class="preview-table">
                                    <thead>
                                        <tr>
                                            <?php 
                                            $display_fields = [];
                                            foreach ($selected_fields as $field) {
                                                if (isset($available_fields[$field])) {
                                                    echo '<th>' . $available_fields[$field] . '</th>';
                                                    $display_fields[] = $field;
                                                }
                                            }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($report_data as $row): ?>
                                            <tr>
                                                <?php foreach ($display_fields as $field): ?>
                                                    <td>
                                                        <?php 
                                                        $value = $row[$field] ?? '';
                                                        if ($field === 'probability' && is_numeric($value)) {
                                                            echo round($value * 100) . '%';
                                                        } elseif ($field === 'creation_date' && $value) {
                                                            echo date('d.m.Y H:i', strtotime($value));
                                                        } else {
                                                            echo htmlspecialchars($value);
                                                        }
                                                        ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="empty-state">
                                    <h4>📋 Данные для отчета не выбраны</h4>
                                    <p>Выберите поля и настройте фильтры для генерации отчета</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Функция для загрузки отчета
        function loadReport(reportId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="report_id" value="${reportId}">
                             <input type="hidden" name="load_report" value="1">`;
            document.body.appendChild(form);
            form.submit();
        }

        // Обновление списка выбранных полей
        function updateSelectedFields() {
            const selectedList = document.getElementById('selected-fields-list');
            const checkboxes = document.querySelectorAll('input[name="fields[]"]:checked');
            
            selectedList.innerHTML = '';
            
            checkboxes.forEach(checkbox => {
                const label = checkbox.parentElement.textContent.trim();
                const div = document.createElement('div');
                div.className = 'selected-item';
                div.innerHTML = `
                    ${label}
                    <button type="button" class="remove-field">×</button>
                `;
                
                div.querySelector('.remove-field').addEventListener('click', function() {
                    checkbox.checked = false;
                    updateSelectedFields();
                });
                
                selectedList.appendChild(div);
            });
        }

        // Инициализация фильтров
        function initializeFilters() {
            const filtersContainer = document.getElementById('filters-container');
            filtersContainer.innerHTML = '';
            
            <?php if (!empty($filters)): ?>
                <?php foreach ($filters as $index => $filter): ?>
                    addFilter(<?= $index ?>, <?= json_encode($filter) ?>);
                <?php endforeach; ?>
            <?php else: ?>
                addFilter(0);
            <?php endif; ?>
        }

        // Добавление фильтра
        function addFilter(index, filterData = null) {
            const container = document.getElementById('filters-container');
            const filterRow = document.createElement('div');
            filterRow.className = 'filter-row';
            
            filterRow.innerHTML = `
                <select name="filters[${index}][field]">
                    <option value="">Выберите поле</option>
                    <option value="service_id">Услуга</option>
                    <option value="stage_id">Этап проекта</option>
                    <option value="manager_id">Менеджер</option>
                    <option value="segment_id">Сегмент</option>
                    <option value="implementation_year">Год реализации</option>
                    <option value="probability">Вероятность (мин.)</option>
                </select>
                <select name="filters[${index}][operator]">
                    <option value="=">равно</option>
                    <option value=">=">больше или равно</option>
                </select>
                <input type="text" name="filters[${index}][value]" placeholder="Значение" value="${filterData ? filterData.value : ''}">
                <button type="button" class="remove-filter">×</button>
            `;
            
            // Устанавливаем значения если есть данные
            if (filterData) {
                filterRow.querySelector('select[name="filters[' + index + '][field]"]').value = filterData.field;
                filterRow.querySelector('select[name="filters[' + index + '][operator]"]').value = filterData.operator;
            }
            
            filterRow.querySelector('.remove-filter').addEventListener('click', function() {
                filterRow.remove();
            });
            
            container.appendChild(filterRow);
        }

        // Инициализация
        document.addEventListener('DOMContentLoaded', function() {
            updateSelectedFields();
            initializeFilters();
            
            document.querySelectorAll('input[name="fields[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedFields);
            });
            
            document.getElementById('add-filter').addEventListener('click', function() {
                const existingFilters = document.querySelectorAll('.filter-row');
                addFilter(existingFilters.length);
            });
        });
    </script>
</body>
</html>