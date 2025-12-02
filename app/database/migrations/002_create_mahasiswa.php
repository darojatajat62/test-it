<?php
class CreateMahasiswaTable
{
    public function up($db)
    {
        echo "    Creating mahasiswa table... ";

        $sql = "CREATE TABLE IF NOT EXISTS mahasiswa (
            m_id VARCHAR(10) PRIMARY KEY,
            nim VARCHAR(20) NOT NULL UNIQUE,
            kode_jns_kelamin VARCHAR(1),
            nama_mahasiswa VARCHAR(100) NOT NULL,
            jurusan VARCHAR(50) NOT NULL,
            ipk DECIMAL(3,2) DEFAULT 0.00,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (kode_jns_kelamin) 
                REFERENCES jenis_kelamin(kode_jns_kelamin)
                ON DELETE SET NULL
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
        return $db->query("DROP TABLE IF EXISTS mahasiswa");
    }
}

$migration = new CreateMahasiswaTable();
