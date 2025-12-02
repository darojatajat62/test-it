<?php
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Nilai Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .whatsapp-status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .connected {
            background-color: #d4edda;
            color: #155724;
        }

        .disconnected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .qrcode-container {
            text-align: center;
            padding: 20px;
        }

        #qrcode {
            max-width: 300px;
            margin: 0 auto;
        }

        .badge {
            font-size: 0.9em;
        }

        .table th {
            background-color: #f8f9fa;
        }

        .btn-hidden {
            display: none !important;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center mb-4">
                    <i class="bi bi-journal-text"></i> Laporan Nilai Mahasiswa
                </h2>
                <div id="whatsappStatus" class="whatsapp-status">
                    <div class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    Checking WhatsApp connection...
                </div>
                <div class="modal fade" id="qrModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Scan WhatsApp QR Code</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body qrcode-container">
                                <div id="qrcode">Loading QR code...</div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary" onclick="checkWhatsAppStatus()">
                                    <i class="bi bi-arrow-clockwise"></i> Refresh Status
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-8">
                                        <select name="jurusan" class="form-select" onchange="this.form.submit()">
                                            <option value="">Semua Jurusan</option>
                                            <?php foreach ($jurusan_list as $j): ?>
                                                <option value="<?= htmlspecialchars($j) ?>" <?= (isset($_GET['jurusan']) && $_GET['jurusan'] == $j) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($j) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <a href="?" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="?action=exportExcel" class="btn btn-success">
                                    <i class="bi bi-file-excel"></i> Export Excel
                                </a>
                                <div id="connectWhatsAppContainer" class="d-inline">
                                    <button onclick="showQRCode()" class="btn btn-info" id="connectWhatsAppBtn">
                                        <i class="bi bi-whatsapp"></i> Connect WhatsApp
                                    </button>
                                </div>
                                <div id="sendWhatsAppContainer" class="d-inline btn-hidden">
                                    <button onclick="sendWhatsApp()" class="btn btn-warning" id="sendBtn">
                                        <i class="bi bi-send"></i> Kirim ke WhatsApp
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-table"></i> Data Mahasiswa
                        <span class="badge bg-light text-dark float-end">
                            Total: <?= count($mahasiswa_data) ?> mahasiswa
                        </span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($mahasiswa_data)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                Tidak ada data mahasiswa ditemukan.
                                <?php if (!empty($jurusan)): ?>
                                    Untuk jurusan: <strong><?= htmlspecialchars($jurusan) ?></strong>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead class="table-dark text-black">
                                        <tr>
                                            <th>#</th>
                                            <th>M#</th>
                                            <th>NIM</th>
                                            <th>Nama Mahasiswa</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Jurusan</th>
                                            <th>IPK</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        foreach ($mahasiswa_data as $row):
                                        ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td><?= htmlspecialchars($row['m_id']) ?></td>
                                                <td><?= htmlspecialchars($row['nim']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                                                <td><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?= htmlspecialchars($row['jurusan']) ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge <?= $row['ipk'] >= 3.0 ? 'bg-success' : ($row['ipk'] >= 2.0 ? 'bg-warning' : 'bg-danger') ?>">
                                                        <?= number_format($row['ipk'], 2) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let whatsappConnected = false;
        document.addEventListener('DOMContentLoaded', function() {
            checkWhatsAppStatus();
            setInterval(checkWhatsAppStatus, 30000);
        });

        function checkWhatsAppStatus() {
            fetch('?action=whatsapp_status')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    const statusDiv = document.getElementById('whatsappStatus');
                    const connectContainer = document.getElementById('connectWhatsAppContainer');
                    const sendContainer = document.getElementById('sendWhatsAppContainer');
                    const connectBtn = document.getElementById('connectWhatsAppBtn');
                    const sendBtn = document.getElementById('sendBtn');

                    if (data.status === 'connected') {
                        whatsappConnected = true;
                        statusDiv.innerHTML = `<i class="bi bi-check-circle"></i> WhatsApp Connected: ${data.message}`;
                        statusDiv.className = 'whatsapp-status connected';
                        connectContainer.classList.add('btn-hidden');
                        sendContainer.classList.remove('btn-hidden');
                        sendBtn.disabled = false;

                    } else {
                        whatsappConnected = false;
                        if (data.status === 'disconnected') {
                            statusDiv.innerHTML = `<i class="bi bi-x-circle"></i> WhatsApp Disconnected: ${data.message}`;
                        } else {
                            statusDiv.innerHTML = `<i class="bi bi-question-circle"></i> Status: ${data.message || 'Unknown'}`;
                        }
                        statusDiv.className = 'whatsapp-status disconnected';
                        connectContainer.classList.remove('btn-hidden');
                        sendContainer.classList.add('btn-hidden');
                        connectBtn.disabled = false;
                    }
                })
                .catch(error => {
                    whatsappConnected = false;
                    const statusDiv = document.getElementById('whatsappStatus');
                    const connectContainer = document.getElementById('connectWhatsAppContainer');
                    const sendContainer = document.getElementById('sendWhatsAppContainer');
                    statusDiv.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Error checking WhatsApp status: ${error.message}`;
                    statusDiv.className = 'whatsapp-status disconnected';
                    connectContainer.classList.remove('btn-hidden');
                    sendContainer.classList.add('btn-hidden');
                });
        }

        function showQRCode() {
            const qrcodeDiv = document.getElementById('qrcode');
            qrcodeDiv.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div><p>Loading QR code...</p></div>';
            fetch('?action=whatsapp_qr')
                .then(response => response.json())
                .then(data => {
                    if (data.qr) {
                        qrcodeDiv.innerHTML = `
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i> Scan QR code ini dengan WhatsApp
                            </div>
                            <img src="${data.qr}" alt="QR Code" class="img-fluid">
                            <p class="mt-2 text-muted">1. Buka WhatsApp di ponsel<br>2. Ketuk menu ⋮ → Perangkat tertaut<br>3. Pindai kode QR ini</p>
                        `;
                        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
                        modal.show();
                        setTimeout(checkWhatsAppStatus, 5000);
                    } else {
                        qrcodeDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> ${data.error || 'Failed to load QR code'}</div>`;
                        const modal = new bootstrap.Modal(document.getElementById('qrModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    qrcodeDiv.innerHTML = `<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Error: ${error.message}</div>`;
                    const modal = new bootstrap.Modal(document.getElementById('qrModal'));
                    modal.show();
                });
        }

        function sendWhatsApp() {
            if (!whatsappConnected) {
                alert('⚠️ WhatsApp belum terhubung!\nSilakan klik "Connect WhatsApp" terlebih dahulu.');
                showQRCode();
                return;
            }

            if (confirm('Kirim laporan ke WhatsApp 0812-8776-53?\nPastikan WhatsApp sudah terhubung.')) {
                const sendBtn = document.getElementById('sendBtn');
                const originalText = sendBtn.innerHTML;
                sendBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';
                sendBtn.disabled = true;

                fetch('?action=send_whatsapp')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ ' + data.message);
                        } else {
                            alert('❌ ' + data.message);
                        }
                        sendBtn.innerHTML = originalText;
                        sendBtn.disabled = false;
                        setTimeout(checkWhatsAppStatus, 2000);
                    })
                    .catch(error => {
                        alert('❌ Error: ' + error.message);
                        sendBtn.innerHTML = originalText;
                        sendBtn.disabled = false;
                    });
            }
        }
        function ensureConnectButtonVisible() {
            if (!whatsappConnected) {
                const connectContainer = document.getElementById('connectWhatsAppContainer');
                const sendContainer = document.getElementById('sendWhatsAppContainer');

                connectContainer.classList.remove('btn-hidden');
                sendContainer.classList.add('btn-hidden');
            }
        }
        setTimeout(ensureConnectButtonVisible, 1000);
    </script>
</body>

</html>