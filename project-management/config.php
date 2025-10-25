<?php
// config.php - настройки подключения к PostgreSQL
class Config {
    const DB_HOST = 'localhost';
    const DB_PORT = '5432';
    const DB_NAME = 'rtk_project_management';     // спросите у товарища
    const DB_USER = 'superadmin';       // спросите у товарища  
    const DB_PASS = '1111';       // спросите у товарища
    
    public static function getDbConnectionString() {
        return "host=" . self::DB_HOST . 
               " port=" . self::DB_PORT . 
               " dbname=" . self::DB_NAME . 
               " user=" . self::DB_USER . 
               " password=" . self::DB_PASS;
    }
}
?>