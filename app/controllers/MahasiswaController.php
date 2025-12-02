<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/MahasiswaModel.php';

class MahasiswaController {
    private $conn;
    private $model;

    public function __construct() {
        try {
            $database = Database::getInstance();
            $this->conn = $database->getConnection();
            if (!$this->conn) {
                throw new Exception("Database connection is null");
            }
            $this->model = new MahasiswaModel($this->conn);
        } catch (Exception $e) {
            die("❌ Error connecting to database: " . $e->getMessage());
        }
    }

    public function index() {
        $jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : '';
        
        try {
            $jurusan_list = $this->model->getAllJurusan();
            $mahasiswa_data = [];
            if (!empty($jurusan)) {
                $stmt = $this->model->getByJurusan($jurusan);
                if ($stmt) {
                    $mahasiswa_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor(); 
                }
            } else {
                $stmt = $this->model->getAll();
                if ($stmt) {
                    $mahasiswa_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $stmt->closeCursor();
                }
            }
            include_once __DIR__ . '/../views/mahasiswa/index.php';
        } catch (Exception $e) {
            $this->showError("Failed to load data: " . $e->getMessage());
        }
    }

    public function indexSafe() {
        $jurusan = isset($_GET['jurusan']) ? $_GET['jurusan'] : '';
        
        try {
            $jurusan_list = $this->model->getAllJurusan();
            if (!empty($jurusan)) {
                $mahasiswa_data = $this->model->getByJurusanAsArray($jurusan);
            } else {
                $mahasiswa_data = $this->model->getAllAsArray();
            }
            include_once __DIR__ . '/../views/mahasiswa/index_array.php';
            
        } catch (Exception $e) {
            $this->showError("Failed to load data: " . $e->getMessage());
        }
    }

    public function exportExcel() {
        try {
            $data = $this->model->getForExcel();
            if (empty($data)) {
                throw new Exception("No data available for export");
            }
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="laporan_nilai_mahasiswa_' . date('Y-m-d') . '.xls"');
            header('Pragma: no-cache');
            header('Expires: 0');
            echo '<table border="1">';
            echo '<tr>';
            echo '<th>No</th>';
            echo '<th>M#</th>';
            echo '<th>NIM</th>';
            echo '<th>Nama Mahasiswa</th>';
            echo '<th>Jenis Kelamin</th>';
            echo '<th>Jurusan</th>';
            echo '<th>IPK</th>';
            echo '<th>Mata Kuliah</th>';
            echo '<th>Total SKS</th>';
            echo '</tr>';
            $no = 1;
            foreach ($data as $row) {
                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td>' . htmlspecialchars($row['m_id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nim']) . '</td>';
                echo '<td>' . htmlspecialchars($row['nama_mahasiswa']) . '</td>';
                echo '<td>' . htmlspecialchars($row['jenis_kelamin']) . '</td>';
                echo '<td>' . htmlspecialchars($row['jurusan']) . '</td>';
                echo '<td>' . htmlspecialchars($row['ipk']) . '</td>';
                echo '<td>' . htmlspecialchars($row['mata_kuliah'] ?: '-') . '</td>';
                echo '<td>' . htmlspecialchars($row['total_sks'] ?: '0') . '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
            exit;
            
        } catch (Exception $e) {
            $this->showError("Export failed: " . $e->getMessage());
        }
    }

    public function getWhatsAppQR() {
        $this->whatsapp_qr();
    }
    
    public function whatsapp_qr() {
        try {
            $api_url = WHATSAPP_API_URL . '/qr';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code == 200) {
                header('Content-Type: application/json');
                $data = json_decode($response, true);
                if ($data && isset($data['qr'])) {
                    echo json_encode(['qr' => $data['qr']]);
                } else {
                    echo json_encode(['error' => 'QR code not available']);
                }
            } else {
                echo json_encode(['error' => 'Cannot connect to WhatsApp API']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function sendWhatsApp() {
        $this->send_whatsapp();
    }
    
    public function send_whatsapp() {
        try {
            $data = $this->model->getForExcel();
            if (empty($data)) {
                throw new Exception("No data available to send");
            }
            $filename = 'laporan_nilai_' . date('Y-m-d_H-i-s') . '.xls';
            $tempDir = __DIR__ . '/../../temp/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0777, true);
            }
            $fullPath = $tempDir . $filename;
            $content = '<table border="1">';
            $content .= '<tr><th>M#</th><th>NIM</th><th>Nama</th><th>Jurusan</th><th>IPK</th><th>Mata Kuliah</th><th>SKS</th></tr>';
            foreach ($data as $row) {
                $content .= '<tr>';
                $content .= '<td>' . htmlspecialchars($row['m_id']) . '</td>';
                $content .= '<td>' . htmlspecialchars($row['nim']) . '</td>';
                $content .= '<td>' . htmlspecialchars($row['nama_mahasiswa']) . '</td>';
                $content .= '<td>' . htmlspecialchars($row['jurusan']) . '</td>';
                $content .= '<td>' . htmlspecialchars($row['ipk']) . '</td>';
                $content .= '<td>' . htmlspecialchars($row['mata_kuliah'] ?: '-') . '</td>';
                $content .= '<td>' . htmlspecialchars($row['total_sks'] ?: '0') . '</td>';
                $content .= '</tr>';
            }
            $content .= '</table>';
            
            file_put_contents($fullPath, $content);
            $api_url = WHATSAPP_API_URL . '/send';
            $phone = '083169238316';
            if (!file_exists($fullPath)) {
                throw new Exception("Failed to create Excel file");
            }
            $postData = [
                'phone' => $phone,
                'message' => 'Laporan Nilai Mahasiswa ' . date('d-m-Y'),
                'file' => curl_file_create($fullPath, 'application/vnd.ms-excel', $filename)
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            unlink($fullPath);
            header('Content-Type: application/json');
            if ($http_code == 200) {
                echo json_encode([
                    'success' => true,
                    'message' => 'File sent via WhatsApp successfully'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to send via WhatsApp. API returned code: ' . $http_code
                ]);
            }
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function checkWhatsAppStatus() {
        $this->whatsapp_status();
    }
    
    public function whatsapp_status() {
        try {
            $api_url = WHATSAPP_API_URL . '/status';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            header('Content-Type: application/json');
            if ($http_code == 200) {
                $data = json_decode($response, true);
                if ($data && isset($data['status']) && $data['status'] === 'connected') {
                    echo json_encode([
                        'status' => 'connected',
                        'message' => 'WhatsApp is connected and ready'
                    ]);
                } else {
                    echo json_encode([
                        'status' => 'disconnected',
                        'message' => 'WhatsApp is not connected'
                    ]);
                }
            } else {
                echo json_encode([
                    'status' => 'disconnected',
                    'message' => 'WhatsApp API not available'
                ]);
            }
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    private function showError($message) {
        echo '<div class="alert alert-danger" role="alert">';
        echo '<h4 class="alert-heading">Error!</h4>';
        echo '<p>' . htmlspecialchars($message) . '</p>';
        echo '<hr>';
        echo '<p class="mb-0"><a href="' . BASE_URL . '" class="btn btn-primary">Back to Home</a></p>';
        echo '</div>';
        
        error_log("MahasiswaController Error: " . $message);
    }
}
?>