<?php
// database.php - работа с базой данных
require_once 'config.php';

class Database {
    private static $connection = null;
    
    public static function connect() {
        if (self::$connection === null) {
            self::$connection = pg_connect(Config::getDbConnectionString());
            
            if (!self::$connection) {
                throw new Exception("Ошибка подключения к БД: " . pg_last_error());
            }
        }
        return self::$connection;
    }
    
    public static function testConnection() {
        try {
            $conn = self::connect();
            $result = pg_query($conn, "SELECT version()");
            $version = pg_fetch_result($result, 0);
            return "✅ PostgreSQL подключен: " . $version;
        } catch (Exception $e) {
            return "❌ Ошибка: " . $e->getMessage();
        }
    }
}
?>