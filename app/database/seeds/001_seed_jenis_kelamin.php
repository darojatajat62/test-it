<?php
class SeedJenisKelamin
{
    public function run($db)
    {
        echo "    Seeding jenis_kelamin... ";

        $data = [
            ['P', 'Perempuan'],
            ['L', 'Laki-Laki']
        ];

        // Kosongkan tabel
        $db->query("DELETE FROM jenis_kelamin");

        $stmt = $db->prepare("INSERT INTO jenis_kelamin (kode_jns_kelamin, description) VALUES (?, ?)");

        $count = 0;
        foreach ($data as $row) {
            $stmt->bind_param("ss", $row[0], $row[1]);
            if ($stmt->execute()) {
                $count++;
            }
        }

        $stmt->close();
        echo "✅ {$count} records inserted\n";
        return true;
    }
}

$seeder = new SeedJenisKelamin();
