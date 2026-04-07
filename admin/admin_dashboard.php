<?php ob_start(); ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
mysqli_report(MYSQLI_REPORT_OFF);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản trị - Sweet Cake</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&family=Noto+Serif:wght@700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin_style.css">
<?php if ($currentPage === 'reports'): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<?php endif; ?>
<style>
html, body { margin: 0; min-height: 100%; background: #fbf9f7; font-family: 'Be Vietnam Pro', sans-serif; color: #1b1c1b; }
.admin-shell { min-height: 100vh; display: flex; }
.admin-sidebar {
    width: 248px; background: #f5f3f1; padding: 20px 14px; border-right: 1px solid #ece7e4;
    display: flex; flex-direction: column; gap: 12px;
}
.admin-brand { padding: 14px 12px 8px; }
.admin-brand-title { font-family: 'Noto Serif', serif; font-size: 28px; color: #76553e; font-weight: 700; line-height: 1.1; }
.admin-brand-sub { margin-top: 4px; font-size: 10px; letter-spacing: 1.8px; text-transform: uppercase; color: #7f716a; }
.admin-user {
    margin: 4px 8px 10px; background: #fff; border-radius: 12px; padding: 10px;
    display: flex; gap: 10px; align-items: center;
}
.admin-avatar { width: 40px; height: 40px; border-radius: 50%; background: #d6c5bc; object-fit: cover; }
.admin-user-name { font-size: 14px; font-weight: 700; }
.admin-user-role { font-size: 11px; color: #7f716a; }
.admin-nav { list-style: none; margin: 0; padding: 0 8px; display: flex; flex-direction: column; gap: 6px; }
.admin-nav-link {
    display: flex; align-items: center; gap: 10px; border-radius: 12px; padding: 10px 12px;
    color: #655d5a; text-decoration: none; font-weight: 500; font-size: 14px;
}
.admin-nav-link:hover { background: #ebe5e2; }
.admin-nav-link.active { background: #76553e; color: #fff; box-shadow: 0 8px 18px rgba(118,85,62,.2); }
.admin-nav-link i { width: 18px; text-align: center; }
.admin-sidebar-bottom { margin-top: auto; border-top: 1px solid #e4dcda; padding: 10px 8px 0; }
.admin-main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
.admin-main-content { padding: 24px; flex: 1; }
.admin-footer { border-top: 1px solid #ebe5e2; padding: 14px 24px; font-size: 13px; color: #7f716a; }
.overview-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: 14px; }
.overview-card { background: #fff; border: 1px solid #efe8e5; border-radius: 16px; padding: 16px; }
.overview-title { font-family: 'Noto Serif', serif; color: #76553e; font-size: 28px; margin: 0 0 6px; }
.overview-sub { color: #7f716a; margin-bottom: 16px; }
.metric-label { font-size: 12px; color: #7f716a; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
.metric-num { font-family: 'Noto Serif', serif; font-size: 30px; color: #76553e; margin-top: 6px; }
@media (max-width: 992px) {
    .admin-sidebar { width: 92px; padding: 12px 8px; }
    .admin-brand-sub, .admin-user-role, .admin-user-name, .admin-nav-link span { display: none; }
    .admin-nav-link { justify-content: center; }
    .admin-nav-link i { width: auto; font-size: 16px; }
    .admin-user { justify-content: center; }
}
</style>
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand">
            <div class="admin-brand-title">Sweet Cake</div>
            <div class="admin-brand-sub">Admin Studio</div>
        </div>
        <div class="admin-user">
            <img class="admin-avatar" src="../images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Admin">
            <div>
                <div class="admin-user-name">Quản trị viên</div>
                <div class="admin-user-role">Sweet Cake Studio</div>
            </div>
        </div>
        <ul class="admin-nav">
            <li><a class="admin-nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" href="admin_dashboard.php"><i class="fas fa-chart-pie"></i><span>Tổng quan</span></a></li>
            <li><a class="admin-nav-link <?php echo $currentPage === 'products' ? 'active' : ''; ?>" href="admin_dashboard.php?page=products"><i class="fas fa-cake-candles"></i><span>Sản phẩm</span></a></li>
            <li><a class="admin-nav-link <?php echo $currentPage === 'producttype' ? 'active' : ''; ?>" href="admin_dashboard.php?page=producttype"><i class="fas fa-list"></i><span>Danh mục</span></a></li>
            <li><a class="admin-nav-link <?php echo $currentPage === 'sizes' ? 'active' : ''; ?>" href="admin_dashboard.php?page=sizes"><i class="fas fa-expand-arrows-alt"></i><span>Quản lý cỡ bánh</span></a></li>
            <li><a class="admin-nav-link <?php echo $currentPage === 'flavors' ? 'active' : ''; ?>" href="admin_dashboard.php?page=flavors"><i class="fas fa-palette"></i><span>Quản lý hương vị</span></a></li>
            <li><a class="admin-nav-link <?php echo $currentPage === 'orders' ? 'active' : ''; ?>" href="admin_dashboard.php?page=orders"><i class="fas fa-receipt"></i><span>Đơn hàng</span></a></li>
            <li><a class="admin-nav-link <?php echo $currentPage === 'customers' ? 'active' : ''; ?>" href="admin_dashboard.php?page=customers"><i class="fas fa-users"></i><span>Khách hàng</span></a></li>
        </ul>
        <div class="admin-sidebar-bottom">
            <a class="admin-nav-link <?php echo $currentPage === 'reports' ? 'active' : ''; ?>" href="admin_dashboard.php?page=reports"><i class="fas fa-chart-line"></i><span>Báo cáo</span></a>
            <a class="admin-nav-link" href="logout_admin.php"><i class="fas fa-right-from-bracket"></i><span>Đăng xuất</span></a>
        </div>
    </aside>
    <main class="admin-main">
        <div class="admin-main-content">
            <?php
            if (isset($_GET['page'])) {
                switch ($_GET['page']) {
                    case 'dashboard':
                        require_once 'connect.php';
                        $totalProducts = 0;
                        $totalOrders = 0;
                        $totalCustomers = 0;
                        $qProducts = $conn->query("SELECT COUNT(*) AS total FROM products");
                        if ($qProducts) { $totalProducts = (int)$qProducts->fetch_assoc()['total']; }
                        $qOrders = $conn->query("SELECT COUNT(*) AS total FROM orders");
                        if ($qOrders) { $totalOrders = (int)$qOrders->fetch_assoc()['total']; }
                        $qCustomers = $conn->query("SELECT COUNT(*) AS total FROM users");
                        if ($qCustomers) {
                            $totalCustomers = (int)$qCustomers->fetch_assoc()['total'];
                        } else {
                            $qCustomers = $conn->query("SELECT COUNT(*) AS total FROM customers");
                            if ($qCustomers) { $totalCustomers = (int)$qCustomers->fetch_assoc()['total']; }
                        }
                        ?>
                        <h1 class="overview-title">Tổng quan quản trị</h1>
                        <div class="overview-sub">Theo dõi nhanh dữ liệu chính của hệ thống Sweet Cake.</div>
                        <div class="overview-grid">
                            <div class="overview-card">
                                <div class="metric-label">Tổng sản phẩm</div>
                                <div class="metric-num"><?php echo number_format($totalProducts); ?></div>
                            </div>
                            <div class="overview-card">
                                <div class="metric-label">Tổng đơn hàng</div>
                                <div class="metric-num"><?php echo number_format($totalOrders); ?></div>
                            </div>
                            <div class="overview-card">
                                <div class="metric-label">Tổng khách hàng</div>
                                <div class="metric-num"><?php echo number_format($totalCustomers); ?></div>
                            </div>
                        </div>
                        <?php
                        break;
                    case 'customers': include 'manage_customers.php'; break;
                    case 'sizes': include 'manage_sizes.php'; break;
                    case 'flavors': include 'manage_flavors.php'; break;
                    case 'producttype': include 'manage_producttype.php'; break;
                    case 'products': include 'manage_products.php'; break;
                    case 'orders': include 'manage_orders.php'; break;
                    case 'order_detail': include 'order_detail.php'; break;
                    case 'shipping': include 'manage_shipping.php'; break;
                    case 'reports': include 'manage_reports.php'; break;
                    case 'contact': include 'manage_contact.php'; break;
                    case 'news': include 'manage_news.php'; break;
                    case 'promotions': include 'manage_promotions.php'; break;
                    default: echo '<h3>Chào mừng Admin</h3>';
                }
            } else {
                header('Location: admin_dashboard.php?page=dashboard');
                exit();
            }
            ?>
        </div>
        <footer class="admin-footer">&copy; 2026 Sweet Cake Admin</footer>
    </main>
</div>
</body>
</html>