<?php
// session.php - управление сессиями

// Настройки сессии ДО запуска
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Используйте 1 если есть HTTPS
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Регенерация ID сессии для безопасности
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} else if (time() - $_SESSION['created'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>