<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}
?>
<div class="header" style="
    background:#FFFFFF;
    color:#000000;
    padding:12px 30px;
    font-size:18px;
    font-weight:600;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    border-radius:0;
    position:relative;
    display:flex;
    align-items:center;
    justify-content:space-between;
    font-family:'Open Sans',sans-serif;">
    <div style="display:flex;align-items:center;gap:10px;">
        <img src="../images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Logo" style="height:38px;">
        <a href="admin_dashboard.php" style="color:#8B6F47; text-decoration:none; font-weight:700; letter-spacing:0.5px;">
            Savor Cake Admin
        </a>
    </div>
    <div style="font-size:14px;">
        <a href="admin_dashboard.php" style="margin-right:20px; color:#555; text-decoration:none; font-weight:600;">Trang chủ</a>
        <a href="logout_admin.php" style="color:#8B6F47; text-decoration:none; font-weight:600;">Đăng xuất</a>
    </div>
</div>