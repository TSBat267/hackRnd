<?php
// export_report.php
require_once 'session.php';
require_once 'auth.php';
require_once 'database.php';

Auth::requireAuth();

// Получаем данные из сессии или POST
$report_data = $_SESSION['report_data'] ?? [];
$selected_fields = $_SESSION['selected_fields'] ?? [];
$report_name = $_SESSION['report_name'] ?? 'report';
$available_fields = $_SESSION['available_fields'] ?? [];

if (empty($report_data)) {
    header('Location: reports.php');
    exit;
}

$format = $_POST['export_format'] ?? 'excel';

switch ($format) {
    case 'excel':
        exportExcel($report_data, $selected_fields, $available_fields, $report_name);
        break;
    case 'pdf':
        exportPDF($report_data, $selected_fields, $available_fields, $report_name);
        break;
    case 'csv':
        exportCSV($report_data, $selected_fields, $available_fields, $report_name);
        break;
}

function exportExcel($data, $fields, $available_fields, $filename) {
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
    
    echo '<html><head><meta charset="UTF-8"></head><body>';
    echo '<table border="1">';
    
    // Заголовки
    echo '<tr>';
    foreach ($fields as $field) {
        if (isset($available_fields[$field])) {
            echo '<th>' . htmlspecialchars($available_fields[$field]) . '</th>';
        }
    }
    echo '</tr>';
    
    // Данные
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($fields as $field) {
            $value = $row[$field] ?? '';
            if ($field === 'probability' && is_numeric($value)) {
                $value = round($value * 100) . '%';
            } elseif ($field === 'creation_date' && $value) {
                $value = date('d.m.Y H:i', strtotime($value));
            }
            echo '<td>' . htmlspecialchars($value) . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table></body></html>';
    exit;
}

function exportCSV($data, $fields, $available_fields, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputs($output, "\xEF\xBB\xBF"); // BOM для UTF-8
    
    // Заголовки
    $headers = [];
    foreach ($fields as $field) {
        if (isset($available_fields[$field])) {
            $headers[] = $available_fields[$field];
        }
    }
    fputcsv($output, $headers, ';');
    
    // Данные
    foreach ($data as $row) {
        $csv_row = [];
        foreach ($fields as $field) {
            $value = $row[$field] ?? '';
            if ($field === 'probability' && is_numeric($value)) {
                $value = round($value * 100) . '%';
            } elseif ($field === 'creation_date' && $value) {
                $value = date('d.m.Y H:i', strtotime($value));
            }
            $csv_row[] = $value;
        }
        fputcsv($output, $csv_row, ';');
    }
    
    fclose($output);
    exit;
}

function exportPDF($data, $fields, $available_fields, $filename) {
    // Для PDF потребуется библиотека вроде TCPDF или Dompdf
    // Здесь простой HTML-to-PDF через браузер
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
    
    $html = '<html><head><meta charset="UTF-8"><style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style></head><body>';
    $html .= '<h1>' . htmlspecialchars($filename) . '</h1>';
    $html .= '<table>';
    
    // Заголовки
    $html .= '<tr>';
    foreach ($fields as $field) {
        if (isset($available_fields[$field])) {
            $html .= '<th>' . htmlspecialchars($available_fields[$field]) . '</th>';
        }
    }
    $html .= '</tr>';
    
    // Данные
    foreach ($data as $row) {
        $html .= '<tr>';
        foreach ($fields as $field) {
            $value = $row[$field] ?? '';
            if ($field === 'probability' && is_numeric($value)) {
                $value = round($value * 100) . '%';
            } elseif ($field === 'creation_date' && $value) {
                $value = date('d.m.Y H:i', strtotime($value));
            }
            $html .= '<td>' . htmlspecialchars($value) . '</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table></body></html>';
    
    echo $html;
    exit;
}
?>