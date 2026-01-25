<?php
/**
 * ALISER - Configuración de Base de Datos
 * Conexión PDO a MySQL con manejo de errores profesional
 * 
 * @package ALISER
 * @version 1.0.0
 */

// Definir constante para prevenir acceso directo (opcional)
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

// ============================================
// Configuración de Base de Datos
// ============================================
// IMPORTANTE: Actualizar estos valores según tu entorno XAMPP

define('DB_HOST', 'localhost');        // Host de MySQL (generalmente localhost en XAMPP)
define('DB_NAME', 'aliser_db');        // Nombre de la base de datos
define('DB_USER', 'root');             // Usuario de MySQL (por defecto 'root' en XAMPP)
define('DB_PASS', '');                 // Contraseña de MySQL (por defecto vacía en XAMPP)
define('DB_CHARSET', 'utf8mb4');       // Charset de la base de datos

// ============================================
// Clase de Conexión a Base de Datos
// ============================================

class Database {
    private static $instance = null;
    private $pdo = null;
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        try {
            // Construir DSN (Data Source Name)
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );
            
            // Opciones de PDO
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lanzar excepciones en errores
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Retornar arrays asociativos por defecto
                PDO::ATTR_EMULATE_PREPARES   => false,                   // Usar prepared statements nativos
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];
            
            // Crear conexión PDO
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            // Log del error (en producción, usar un sistema de logging)
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            
            // Mostrar mensaje amigable (en desarrollo)
            // En producción, mostrar un mensaje genérico al usuario
            die('Error de conexión a la base de datos. Por favor, contacte al administrador del sistema.');
        }
    }
    
    /**
     * Obtener instancia única de la conexión (Singleton)
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener objeto PDO
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
    
    /**
     * Prevenir clonación
     */
    private function __clone() {}
    
    /**
     * Prevenir deserialización
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
    
    /**
     * Ejecutar una consulta preparada
     * 
     * @param string $sql Consulta SQL con placeholders
     * @param array $params Parámetros para los placeholders
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log('Error en consulta SQL: ' . $e->getMessage());
            error_log('SQL: ' . $sql);
            error_log('Params: ' . print_r($params, true));
            throw $e;
        }
    }
    
    /**
     * Obtener un solo registro
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Obtener múltiples registros
     * 
     * @param string $sql Consulta SQL
     * @param array $params Parámetros
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtener el último ID insertado
     * 
     * @return string
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit() {
        return $this->pdo->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}

// ============================================
// Función Helper para Obtener Conexión
// ============================================

/**
 * Obtener instancia de la base de datos
 * 
 * @return Database
 */
function getDB() {
    return Database::getInstance();
}

/**
 * Obtener conexión PDO directamente
 * 
 * @return PDO
 */
function getPDO() {
    return Database::getInstance()->getConnection();
}
