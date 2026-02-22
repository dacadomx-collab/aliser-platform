<?php
/**
 * ALISER - Clase de Conexión PDO Singleton (Versión Oro)
 */
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/db_config.php';

class Database {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        try {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("ALISER_DB_ERROR: " . $e->getMessage());
            die("Error de sistema: La conexión no pudo establecerse.");
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

    // MÉTODO QUE FALTA Y CAUSA EL ERROR
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // MÉTODO PARA OBTENER UNA SOLA FILA
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    // MÉTODO PARA OBTENER MÚLTIPLES FILAS
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    private function __clone() {}
    public function __wakeup() { throw new Exception("No se puede deserializar."); }
}