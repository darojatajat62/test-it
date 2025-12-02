<?php
class SeedKrs
{
    public function run($db)
    {
        echo "    Seeding krs... ";

        $data = [
            ['M1', 'W1', 24],
            ['M2', 'W1', 24],
            ['M3', 'W4', 18],
            ['M4', 'W2', 15],
            ['M5', 'W3', 12],
            ['M6', 'W3', 12],
            ['M7', 'W4', 15]
        ];

        // Kosongkan tabel
        $db->query("DELETE FROM krs");

        $stmt = $db->prepare("INSERT INTO krs (m_id, kode_mata_kuliah, sks) VALUES (?, ?, ?)");

        $count = 0;
        foreach ($data as $row) {
            $stmt->bind_param("ssi", $row[0], $row[1], $row[2]);
            if ($stmt->execute()) {
                $count++;
            }
        }

        $stmt->close();
        echo "✅ {$count} records inserted\n";
        return true;
    }
}

$seeder = new SeedKrs();
