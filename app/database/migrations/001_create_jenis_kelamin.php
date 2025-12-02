<?php
class CreateJenisKelaminTable
{
    public function up($db)
    {
        echo "    Creating jenis_kelamin table... ";

        $sql = "CREATE TABLE IF NOT EXISTS jenis_kelamin (
            kode_jns_kelamin VARCHAR(1) PRIMARY KEY,
            description VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        if ($db->query($sql) === TRUE) {
            echo "✅ Success\n";
            return true;
        } else {
            echo "❌ Failed: " . $db->error . "\n";
            return false;
        }
    }

    public function down($db)
    {
        return $db->query("DROP TABLE IF EXISTS jenis_kelamin");
    }
}

// Buat instance untuk migration system
$migration = new CreateJenisKelaminTable();
