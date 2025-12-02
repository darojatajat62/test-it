<?php
class CreateKrsTable
{
    public function up($db)
    {
        echo "    Creating krs table... ";

        $sql = "CREATE TABLE IF NOT EXISTS krs (
            m_id VARCHAR(10),
            kode_mata_kuliah VARCHAR(10),
            sks INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (m_id, kode_mata_kuliah),
            FOREIGN KEY (m_id) 
                REFERENCES mahasiswa(m_id)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            FOREIGN KEY (kode_mata_kuliah) 
                REFERENCES mata_kuliah(kode_mata_kuliah)
                ON DELETE CASCADE
                ON UPDATE CASCADE
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
        return $db->query("DROP TABLE IF EXISTS krs");
    }
}

$migration = new CreateKrsTable();
