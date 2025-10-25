<?php
// auth.php - упрощенная аутентификация для разработки
require_once 'database.php';

class Auth {
    
    public static function login($username, $password) {
        // Временная заглушка для разработки
        if ($username === 'admin' && $password === 'password') {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'admin';
            $_SESSION['full_name'] = 'Администратор Системы';
            $_SESSION['role_id'] = 1;
            return true;
        }
        return false;
    }
    
    public static function logout() {
        session_destroy();
        session_start();
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function requireAuth() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
    
    public static function getUser() {
        if (self::isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'full_name' => $_SESSION['full_name'],
                'role_id' => $_SESSION['role_id']
            ];
        }
        return null;
    }
    
    public static function hasRole($roleName) {
        if (!self::isLoggedIn()) return false;
        
        // Временная заглушка
        $userRoles = [
            1 => 'Администратор',
            2 => 'Аналитик', 
            3 => 'Пользователь'
        ];
        
        $currentRole = $userRoles[$_SESSION['role_id']] ?? 'Пользователь';
        return $currentRole === $roleName;
    }
}
?>