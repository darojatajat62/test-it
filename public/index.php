<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load config
require_once __DIR__ . '/../app/config/config.php';

// Router yang lebih baik
$action = isset($_GET['action']) ? $_GET['action'] : 'index';
$controller = 'MahasiswaController';

// Tentukan action berdasarkan URL
if (isset($_GET['action'])) {
    $action = $_GET['action'];
} else {
    $action = 'index';
}

// Mapping action untuk kompatibilitas
$actionMap = [
    'whatsapp-status' => 'whatsapp_status',
    'whatsapp-qr' => 'whatsapp_qr',
    'send-whatsapp' => 'send_whatsapp',
    'export-excel' => 'exportExcel',
    'index' => 'index'
];

// Gunakan mapping jika ada
if (isset($actionMap[$action])) {
    $action = $actionMap[$action];
}

// Load controller
$controllerFile = __DIR__ . '/../app/controllers/' . $controller . '.php';
if (!file_exists($controllerFile)) {
    die("Controller not found: " . $controller);
}

require_once $controllerFile;

// Buat instance controller
try {
    $controllerInstance = new $controller();

    // Cek jika method ada
    if (!method_exists($controllerInstance, $action)) {
        die("Action not found: " . $action);
    }

    // Jalankan method
    $controllerInstance->$action();
} catch (Exception $e) {
    echo '<div class="alert alert-danger m-4" role="alert">';
    echo '<h4>Application Error</h4>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><small>File: ' . $e->getFile() . ':' . $e->getLine() . '</small></p>';
    echo '</div>';
}
