<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "╔══════════════════════════════════════════╗\n";
echo "║     DATABASE MIGRATION TOOL              ║\n";
echo "║     Nilai Mahasiswa System               ║\n";
echo "╚══════════════════════════════════════════╝\n\n";

// Load config terlebih dahulu
$configPath = __DIR__ . '/app/config/config.php';
if (!file_exists($configPath)) {
    die("❌ ERROR: File config.php tidak ditemukan di: $configPath\n");
}

require_once $configPath;

echo "🔧 Konfigurasi Database:\n";
echo "   Host: " . DB_HOST . "\n";
echo "   Database: " . DB_NAME . "\n";
echo "   User: " . DB_USER . "\n";
echo "   Pass: " . (DB_PASS ? '***' : '(kosong)') . "\n\n";

// Cek apakah class Database dan MigrationSeeder sudah ada
// Jika tidak, include manual
$databaseClassPath = __DIR__ . '/app/database/Database.php';
$migrationClassPath = __DIR__ . '/app/database/MigrationSeeder.php';

if (!file_exists($databaseClassPath)) {
    die("❌ ERROR: File Database.php tidak ditemukan di: $databaseClassPath\n");
}

if (!file_exists($migrationClassPath)) {
    die("❌ ERROR: File MigrationSeeder.php tidak ditemukan di: $migrationClassPath\n");
}

// Include class secara manual untuk memastikan
require_once $databaseClassPath;
require_once $migrationClassPath;

try {
    // Tampilkan menu
    echo "📋 MENU:\n";
    echo "   1. Setup Database Lengkap\n";
    echo "   2. Reset Database (Hapus semua + Setup ulang)\n";
    echo "   3. Jalankan Migrasi saja\n";
    echo "   4. Jalankan Seeder saja\n";
    echo "   5. Keluar\n\n";

    // Baca input
    echo "➤ Pilihan Anda [1-5]: ";
    $choice = trim(fgets(STDIN));

    // Buat instance migrator
    echo "\n🔄 Membuat instance MigrationSeeder...\n";
    $migrator = new MigrationSeeder();

    switch ($choice) {
        case '1':
            echo "\n══════════════════════════════════════════════\n";
            echo "          SETUP DATABASE LENGKAP\n";
            echo "══════════════════════════════════════════════\n";
            $migrator->runAll();
            break;

        case '2':
            echo "\n⚠️  ⚠️  ⚠️   PERINGATAN!   ⚠️  ⚠️  ⚠️\n";
            echo "Ini akan menghapus SEMUA data di database!\n\n";
            echo "➤ Ketik 'YA' untuk melanjutkan: ";
            $confirm = trim(fgets(STDIN));

            if (strtoupper($confirm) === 'YA') {
                echo "\n══════════════════════════════════════════════\n";
                echo "          RESET DATABASE\n";
                echo "══════════════════════════════════════════════\n";
                $migrator->fresh();
            } else {
                echo "\n❌ Dibatalkan!\n";
            }
            break;

        case '3':
            echo "\n══════════════════════════════════════════════\n";
            echo "          JALANKAN MIGRASI\n";
            echo "══════════════════════════════════════════════\n";
            $migrator->runMigrationsOnly();
            break;

        case '4':
            echo "\n══════════════════════════════════════════════\n";
            echo "          JALANKAN SEEDER\n";
            echo "══════════════════════════════════════════════\n";
            $migrator->runSeedersOnly();
            break;

        case '5':
            echo "\n👋 Sampai jumpa!\n";
            exit(0);
            break;

        default:
            echo "\n❌ Pilihan tidak valid!\n";
            exit(1);
    }
} catch (Exception $e) {
    echo "\n❌ ERROR TERJADI:\n";
    echo "   Pesan: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}

// Verifikasi akhir
echo "\n🔍 Verifikasi database:\n";
echo "────────────────────────────────────────\n";

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        echo "❌ Tidak bisa koneksi ke database: " . $conn->connect_error . "\n";
    } else {
        $result = $conn->query("SHOW TABLES");

        if ($result && $result->num_rows > 0) {
            echo "✅ Tabel yang berhasil dibuat:\n";
            while ($row = $result->fetch_array()) {
                $table = $row[0];
                $countResult = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
                if ($countResult) {
                    $count = $countResult->fetch_assoc()['cnt'];
                    echo "   • {$table}: {$count} records\n";
                }
            }
        } else {
            echo "⚠️  Tidak ada tabel di database\n";
        }

        $conn->close();
    }
} catch (Exception $e) {
    echo "⚠️  Error verifikasi: " . $e->getMessage() . "\n";
}

echo "\n🎯 Proses selesai!\n";
