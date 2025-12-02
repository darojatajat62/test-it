<?php
class Database
{
    private static $instance = null;
    private $connection;
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private function __construct()
    {
        try {
            // Coba koneksi dengan PDO
            $dsn = "mysql:host=" . $this->host . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->user, $this->pass);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Buat database jika belum ada
            $this->createDatabaseIfNotExists();

            // Pilih database
            $this->connection->exec("USE " . $this->dbname);

            // Set attribute tambahan
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            if (defined('DEBUG') && DEBUG) {
                error_log("PDO Database connected successfully to " . $this->dbname);
            }
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    private function createDatabaseIfNotExists()
    {
        try {
            // Cek apakah database sudah ada
            $stmt = $this->connection->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $this->dbname . "'");
            $databaseExists = $stmt->fetch();

            if (!$databaseExists) {
                // Buat database
                $sql = "CREATE DATABASE " . $this->dbname . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
                $this->connection->exec($sql);
            }
        } catch (Exception $e) {
            throw new Exception("Failed to create database: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function __destruct()
    {
        $this->connection = null;
    }
}
