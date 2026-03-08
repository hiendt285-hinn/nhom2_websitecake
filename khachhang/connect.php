<?php

$host = 'localhost'; 
$dbname = 'ban_banh'; 
$username = 'root';  
$password = ''; 

// Kết nối sử dụng mysqli
$conn = new mysqli($host, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Set charset để hỗ trợ tiếng Việt
$conn->set_charset("utf8mb4");

