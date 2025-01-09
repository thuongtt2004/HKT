<?php
session_start();
require_once '../config/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được phép']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

// Kiểm tra tài khoản admin cố định
if ($username === 'admin' && $password === '123456') {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_username'] = 'admin';
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng']);
?> 