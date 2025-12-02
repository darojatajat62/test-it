<?php
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/controllers/MahasiswaController.php';

$controller = new MahasiswaController();

// Routing sederhana
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

switch ($action) {
    case 'export-excel':
        $controller->exportExcel();
        break;
    case 'whatsapp-qr':
        $controller->getWhatsAppQR();
        break;
    case 'send-whatsapp':
        $controller->sendWhatsApp();
        break;
    case 'whatsapp-status':
        $controller->checkWhatsAppStatus();
        break;
    default:
        $controller->index();
        break;
}
