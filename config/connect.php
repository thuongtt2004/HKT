<?php
// Thiết lập timezone Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

$servername = "localhost";
$username = "root"; // Thay bằng username của bạn
$password = ""; // Thay bằng password của bạn
$dbname = "hkt";

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập timezone cho MySQL connection
$conn->query("SET time_zone = '+07:00'");

// Thiết lập charset UTF-8
$conn->set_charset("utf8mb4");
?>