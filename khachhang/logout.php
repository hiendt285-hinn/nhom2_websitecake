<?php

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