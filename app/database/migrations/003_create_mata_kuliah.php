<?php
class CreateMataKuliahTable
{
    public function up($db)
    {
        echo "    Creating mata_kuliah table... ";

        $sql = "CREATE TABLE IF NOT EXISTS mata_kuliah (
            kode_mata_kuliah VARCHAR(10) PRIMARY KEY,
            nama_mata_kuliah VARCHAR(100) NOT NULL,
            dosen VARCHAR(100) NOT NULL,
            jurusan VARCHAR(50) NOT NULL,
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
        return $db->query("DROP TABLE IF EXISTS mata_kuliah");
    }
}

$migration = new CreateMataKuliahTable();
