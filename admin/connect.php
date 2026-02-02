<?php
// Thông số kết nối database
$host = 'localhost';  // Thường là localhost nếu dùng local server
$dbname = 'ban_banh';  // Tên database
$username = 'root';  // Username MySQL (mặc định root)
$password = '';  // Password MySQL (mặc định rỗng nếu chưa set)

// Kết nối sử dụng mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Set charset để hỗ trợ tiếng Việt
$conn->set_charset("utf8mb4");

