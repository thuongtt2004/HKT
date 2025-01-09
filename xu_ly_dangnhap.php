<?php
session_start();
require_once 'config/connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không được phép']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin']);
    exit();
}

// Kiểm tra tài khoản admin cố định
if ($username === 'admin' && $password === '123456') {
    $_SESSION['admin_id'] = 1;
    $_SESSION['admin_username'] = 'admin';
    echo json_encode(['success' => true, 'is_admin' => true]);
    exit();
}

// Kiểm tra tài khoản user thường
$sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    echo json_encode(['success' => true, 'is_admin' => false]);
} else {
    echo json_encode(['success' => false, 'message' => 'Tên đăng nhập hoặc mật khẩu không đúng']);
} 