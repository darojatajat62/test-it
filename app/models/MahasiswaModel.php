<?php
class MahasiswaModel
{
    private $conn;
    private $table = 'mahasiswa';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // Get all students with gender
    public function getAll()
    {
        $query = "SELECT m.*, j.description as jenis_kelamin 
                  FROM " . $this->table . " m
                  LEFT JOIN jenis_kelamin j ON m.kode_jns_kelamin = j.kode_jns_kelamin
                  ORDER BY m.m_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    // Get all as array
    public function getAllAsArray()
    {
        $query = "SELECT m.*, j.description as jenis_kelamin 
                  FROM " . $this->table . " m
                  LEFT JOIN jenis_kelamin j ON m.kode_jns_kelamin = j.kode_jns_kelamin
                  ORDER BY m.m_id";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    // Get by jurusan
    public function getByJurusan($jurusan)
    {
        $query = "SELECT m.*, j.description as jenis_kelamin 
                  FROM " . $this->table . " m
                  LEFT JOIN jenis_kelamin j ON m.kode_jns_kelamin = j.kode_jns_kelamin
                  WHERE m.jurusan = :jurusan
                  ORDER BY m.ipk DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':jurusan', $jurusan);
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    // Get by jurusan
    public function getByJurusanAsArray($jurusan)
    {
        $query = "SELECT m.*, j.description as jenis_kelamin 
                  FROM " . $this->table . " m
                  LEFT JOIN jenis_kelamin j ON m.kode_jns_kelamin = j.kode_jns_kelamin
                  WHERE m.jurusan = :jurusan
                  ORDER BY m.ipk DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':jurusan', $jurusan);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }

    // Get all jurusan
    public function getAllJurusan()
    {
        $query = "SELECT DISTINCT jurusan FROM " . $this->table . " ORDER BY jurusan";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Get for Excel export
    public function getForExcel()
    {
        $query = "SELECT m.m_id, m.nim, m.nama_mahasiswa, 
                         j.description as jenis_kelamin,
                         m.jurusan, m.ipk,
                         GROUP_CONCAT(DISTINCT mk.nama_mata_kuliah SEPARATOR ', ') as mata_kuliah,
                         COALESCE(SUM(k.sks), 0) as total_sks
                  FROM mahasiswa m
                  LEFT JOIN jenis_kelamin j ON m.kode_jns_kelamin = j.kode_jns_kelamin
                  LEFT JOIN krs k ON m.m_id = k.m_id
                  LEFT JOIN mata_kuliah mk ON k.kode_mata_kuliah = mk.kode_mata_kuliah
                  GROUP BY m.m_id, m.nim, m.nama_mahasiswa, j.description, m.jurusan, m.ipk
                  ORDER BY m.jurusan, m.ipk DESC";

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Query failed: " . $e->getMessage());
        }
    }
}
