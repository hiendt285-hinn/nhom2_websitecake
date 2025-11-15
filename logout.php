<?php
// logout.php - Xử lý đăng xuất (không cần CSS vì không có HTML)

// Bắt đầu session
if (!isset($_SESSION)) {
    session_start();
}

// Hủy tất cả session
$_SESSION = array();
session_destroy();

// Chuyển hướng về trang chủ
header("Location: index.php");
exit();
?>