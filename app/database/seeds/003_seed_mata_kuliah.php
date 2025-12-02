<?php
class SeedMataKuliah
{
    public function run($db)
    {
        echo "    Seeding mata_kuliah... ";

        $data = [
            ['W1', 'Sistem Basis Data', 'Trihono', 'TI'],
            ['W2', 'Struktur Data', 'Ida', 'AK'],
            ['W3', 'Bahasa Pemrograman', 'Hernadi', 'TI'],
            ['W4', 'Kalkulus', 'Chandra', 'AK']
        ];

        // Kosongkan tabel
        $db->query("DELETE FROM mata_kuliah");

        $stmt = $db->prepare("INSERT INTO mata_kuliah (kode_mata_kuliah, nama_mata_kuliah, dosen, jurusan) VALUES (?, ?, ?, ?)");

        $count = 0;
        foreach ($data as $row) {
            $stmt->bind_param("ssss", $row[0], $row[1], $row[2], $row[3]);
            if ($stmt->execute()) {
                $count++;
            }
        }

        $stmt->close();
        echo "✅ {$count} records inserted\n";
        return true;
    }
}

$seeder = new SeedMataKuliah();
