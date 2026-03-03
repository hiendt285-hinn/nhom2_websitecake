<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}
?>
<div class="header" style="
    background:#fff;
    color:#2c2c2c;
    padding:12px 30px;
    font-size:18px;
    font-weight:600;
    box-shadow:0 1px 4px rgba(0,0,0,0.06);
    border-bottom:1px solid rgba(0,0,0,0.06);
    display:flex;
    align-items:center;
    justify-content:space-between;
    font-family:'Open Sans',sans-serif;">
    <div style="display:flex;align-items:center;gap:10px;">
        <img src="../images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Logo" style="height:38px;">
        <a href="admin_dashboard.php" style="color:#9a7b5a; text-decoration:none; font-weight:700;">Sweet Cake Admin</a>
    </div>
    <div style="font-size:14px;">
        <a href="admin_dashboard.php" style="margin-right:20px; color:#666; text-decoration:none; font-weight:600;">Trang chủ</a>
        <a href="logout_admin.php" style="color:#9a7b5a; text-decoration:none; font-weight:600;">Đăng xuất</a>
    </div>
</div>