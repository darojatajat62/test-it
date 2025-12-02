<?php
require_once __DIR__ . '/Database.php';

class MigrationSeeder
{
    private $db;

    public function __construct()
    {
        echo "   🔗 Membuat koneksi database... ";

        try {
            // Pastikan konstanta database sudah didefinisikan
            if (!defined('DB_HOST') || !defined('DB_USER') || !defined('DB_PASS')) {
                die("❌ Konstanta database belum didefinisikan. Pastikan config.php sudah diinclude.\n");
            }

            $this->db = Database::getInstance()->getConnection();
            echo "✅\n";
        } catch (Exception $e) {
            die("❌ Gagal membuat koneksi: " . $e->getMessage() . "\n");
        }
    }

    public function runAll()
    {
        $this->createDatabase();
        $this->runMigrations();
        $this->runSeeders();
        echo "\n✨ Semua migration dan seeder berhasil dijalankan!\n";
    }

    public function fresh()
    {
        $this->createDatabase();
        $this->dropAllTables();
        $this->runMigrations();
        $this->runSeeders();
        echo "\n🎉 Database berhasil di-refresh!\n";
    }

    public function runMigrationsOnly()
    {
        $this->createDatabase();
        $this->runMigrations();
        echo "\n✅ Migrations berhasil dijalankan!\n";
    }

    public function runSeedersOnly()
    {
        $this->createDatabase();
        $this->runSeeders();
        echo "\n✅ Seeders berhasil dijalankan!\n";
    }

    private function createDatabase()
    {
        echo "\n📦 Membuat database...\n";

        // Buat koneksi tanpa database terpilih
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

        if ($conn->connect_error) {
            die("❌ Koneksi MySQL gagal: " . $conn->connect_error);
        }

        // Buat database jika belum ada
        $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

        if ($conn->query($sql)) {
            echo "✅ Database '" . DB_NAME . "' siap\n";
        } else {
            die("❌ Gagal membuat database: " . $conn->error);
        }

        $conn->close();

        // Pilih database
        if (!$this->db->select_db(DB_NAME)) {
            die("❌ Tidak bisa memilih database '" . DB_NAME . "': " . $this->db->error);
        }
    }

    private function dropAllTables()
    {
        echo "\n🗑️  Menghapus tabel lama...\n";

        // Pilih database dulu
        $this->db->select_db(DB_NAME);

        // Nonaktifkan foreign key checks
        $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

        // Dapatkan semua tabel di database
        $result = $this->db->query("SHOW TABLES");
        $tables = [];

        if ($result) {
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
        }

        // Hapus semua tabel
        foreach ($tables as $table) {
            echo "   Hapus tabel `$table`... ";
            if ($this->db->query("DROP TABLE IF EXISTS `$table`")) {
                echo "✅\n";
            } else {
                echo "❌ Error: " . $this->db->error . "\n";
            }
        }

        // Aktifkan kembali foreign key checks
        $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
    }

    private function runMigrations()
    {
        $migrationsPath = __DIR__ . '/migrations/';

        echo "\n🚀 Menjalankan migrations...\n";
        echo "────────────────────────────────────────\n";

        // Cek apakah folder migrations ada
        if (!is_dir($migrationsPath)) {
            die("❌ Folder migrations tidak ditemukan: $migrationsPath\n");
        }

        $migrationFiles = glob($migrationsPath . '*.php');

        if (empty($migrationFiles)) {
            die("❌ Tidak ada file migration ditemukan di: $migrationsPath\n");
        }

        // Urutkan berdasarkan nama file
        sort($migrationFiles);

        $successCount = 0;
        foreach ($migrationFiles as $file) {
            $filename = basename($file);
            echo "📄 $filename... ";

            // Include file migration
            require_once $file;

            // Cek apakah variable $migration ada dan punya method up
            if (isset($migration) && is_object($migration) && method_exists($migration, 'up')) {
                try {
                    $migration->up($this->db);
                    echo "✅\n";
                    $successCount++;
                } catch (Exception $e) {
                    echo "❌ Error: " . $e->getMessage() . "\n";
                }
            } else {
                echo "⚠️  Format migration tidak valid\n";
            }

            // Reset variable untuk file berikutnya
            unset($migration);
        }

        echo "────────────────────────────────────────\n";
        echo "📊 Migrations: {$successCount}/" . count($migrationFiles) . " berhasil\n";
    }

    private function runSeeders()
    {
        $seedsPath = __DIR__ . '/seeds/';

        echo "\n🌱 Menjalankan seeders...\n";
        echo "────────────────────────────────────────\n";

        // Cek apakah folder seeds ada
        if (!is_dir($seedsPath)) {
            die("❌ Folder seeds tidak ditemukan: $seedsPath\n");
        }

        $seedFiles = glob($seedsPath . '*.php');

        if (empty($seedFiles)) {
            die("❌ Tidak ada file seeder ditemukan di: $seedsPath\n");
        }

        // Urutkan berdasarkan nama file
        sort($seedFiles);

        $successCount = 0;
        foreach ($seedFiles as $file) {
            $filename = basename($file);
            echo "📄 $filename... ";

            // Include file seeder
            require_once $file;

            // Cek apakah variable $seeder ada dan punya method run
            if (isset($seeder) && is_object($seeder) && method_exists($seeder, 'run')) {
                try {
                    $seeder->run($this->db);
                    echo "✅\n";
                    $successCount++;
                } catch (Exception $e) {
                    echo "❌ Error: " . $e->getMessage() . "\n";
                }
            } else {
                echo "⚠️  Format seeder tidak valid\n";
            }

            // Reset variable untuk file berikutnya
            unset($seeder);
        }

        echo "────────────────────────────────────────\n";
        echo "📊 Seeders: {$successCount}/" . count($seedFiles) . " berhasil\n";
    }
}
