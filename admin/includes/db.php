<?php
if (!defined('ALISER_ADMIN')) {
    define('ALISER_ADMIN', true);
}

require_once __DIR__ . '/db_config.php';

final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . DB_CHARSET,
            ]);
        } catch (PDOException $e) {
            error_log('ALISER_DB_ERROR: ' . $e->getMessage());
            die('Ocurrio un error interno.');
        }
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private function __clone() {}

    public function __wakeup(): void
    {
        throw new Exception('No se permite deserializar la conexion.');
    }
}

function getDB(): Database
{
    return Database::getInstance();
}
