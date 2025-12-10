<?php
require_once __DIR__ . '/../config/config.php';

class DB {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance == null) {
            self::$instance = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
            if (self::$instance->connect_error) {
                die("Connection failed: " . self::$instance->connect_error);
            }
        }
        return self::$instance;
    }
}
?>