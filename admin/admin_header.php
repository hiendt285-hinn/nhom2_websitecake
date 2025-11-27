<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}
?>
<div class="header" style="background:#5a8b56; color: #ff5f9e; padding:10px 30px; font-size: 20px; font-weight: bold; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); border-radius: 0 0 25px 25px; position:relative;">
    <img src="../images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Logo" style="height:40px; vertical-align:middle; margin-right:10px;">
    <a href="admin_dashboard.php" style="color: #ff5f9e; text-decoration: none; font-weight: 600; transition: 0.3s;">Trang chủ</a> 
    <span style="float:right; font-weight:normal;">
        <a href="logout_admin.php" style="color: #ff5f9e; text-decoration: none; font-weight: 600; transition: 0.3s;">Đăng xuất</a>
    </span>
</div>