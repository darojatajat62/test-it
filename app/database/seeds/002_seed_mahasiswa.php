<?php
class SeedMahasiswa
{
    public function run($db)
    {
        echo "    Seeding mahasiswa... ";

        $data = [
            ['M1', '2007.000075', 'P', 'Yanny', 'AK', 3.75],
            ['M2', '2007.000086', 'L', 'Andi', 'TI', 2.01],
            ['M3', '2007.000090', 'P', 'Stella', 'TI', 2.60],
            ['M4', '2007.000093', 'L', 'Budi', 'AK', 2.56],
            ['M5', '2007.000201', 'L', 'Risanto', 'AK', 3.16],
            ['M6', '2007.000210', 'P', 'Andriani', 'AK', 3.56],
            ['M7', '2007.000246', 'L', 'Dimas', 'TI', 2.75],
            ['M8', '2007.000259', 'L', 'Johan', 'TI', 1.85],
            ['M9', '2007.000270', 'P', 'Cristine', 'TI', 2.27],
            ['M10', '2007.000295', 'P', 'Melan', 'TI', 2.43]
        ];

        // Kosongkan tabel
        $db->query("DELETE FROM mahasiswa");

        $stmt = $db->prepare("INSERT INTO mahasiswa (m_id, nim, kode_jns_kelamin, nama_mahasiswa, jurusan, ipk) VALUES (?, ?, ?, ?, ?, ?)");

        $count = 0;
        foreach ($data as $row) {
            $stmt->bind_param("sssssd", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
            if ($stmt->execute()) {
                $count++;
            }
        }

        $stmt->close();
        echo "✅ {$count} records inserted\n";
        return true;
    }
}

$seeder = new SeedMahasiswa();
