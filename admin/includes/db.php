<?php
/**
 * ALISER - Clase de Conexión PDO Singleton
 */

// Cargar configuración de credenciales
require_once __DIR__ . '/db_config.php';

class Database {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log('Error ALISER DB: ' . $e->getMessage());
            die('Error crítico de conexión. Contacte al administrador.');
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }

    // Métodos query, fetchOne, fetchAll permanecen iguales...
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}

// Helpers
function getDB() { return Database::getInstance(); }
function getPDO() { return Database::getInstance()->getConnection(); }