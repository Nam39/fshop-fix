<?php
// assets/function/add/handle_add.php
session_start();
header('Content-Type: application/json');

// Check admin login
if (!isset($_SESSION['idtk'])) {
    echo json_encode(['success' => false, 'errors' => ['Unauthorized']]);
    exit;
}

require_once __DIR__ . '/../../../connect_DB/connect_db.php';
require_once __DIR__ . '/add_account.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_account') {
    $conn = connectData();
    
    $data = [
        'Ten_user' => trim($_POST['Ten_user'] ?? ''),
        'username' => trim($_POST['username'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'status' => (int)($_POST['status'] ?? 1),
        'note' => trim($_POST['note'] ?? '')
    ];
    
    $result = addAccount($conn, $data);
    echo json_encode($result);
    exit;
}

echo json_encode(['success' => false, 'errors' => ['Invalid request']]);
?>