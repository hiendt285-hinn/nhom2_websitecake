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
<?php if ($currentPage === 'reports' || $currentPage === 'dashboard'): ?>
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
.metric-label { font-size: 12px; color: #7f716a; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; }
.metric-num { font-family: 'Noto Serif', serif; font-size: 30px; color: #76553e; margin-top: 6px; }
.overview-charts { margin-top: 14px; display: grid; grid-template-columns: 2fr 1fr; gap: 14px; }
.chart-card { background: #fff; border: 1px solid #efe8e5; border-radius: 16px; padding: 16px; }
.chart-title { margin: 0 0 12px; font-size: 16px; color: #76553e; font-weight: 700; }
.chart-wrap { position: relative; width: 100%; min-height: 300px; }
.overview-table-card { margin-top: 14px; background: #fff; border: 1px solid #efe8e5; border-radius: 16px; padding: 16px; }
.overview-table-title { margin: 0 0 12px; font-size: 16px; color: #76553e; font-weight: 700; display:flex; justify-content:space-between; align-items:center; gap:8px; }
.overview-filter { display:flex; align-items:center; gap:8px; margin-bottom:10px; flex-wrap:wrap; }
.overview-filter input[type="date"] { padding:8px 10px; border:1px solid #ddd; border-radius:8px; }
.overview-table { width:100%; border-collapse: collapse; }
.overview-table th,.overview-table td { border-bottom:1px solid #f0ece9; padding:10px 8px; font-size:13px; text-align:left; }
.overview-table th { color:#7f716a; font-size:12px; text-transform:uppercase; letter-spacing:.5px; }
.status-badge { padding:4px 8px; border-radius:999px; font-size:12px; font-weight:600; display:inline-block; }
.status-badge.done { background:#e8f5e9; color:#2e7d32; }
.status-badge.progress { background:#e3f2fd; color:#1565c0; }
.status-badge.pending { background:#fff3e0; color:#ef6c00; }
.status-badge.cancel { background:#ffebee; color:#c62828; }
.link-small { color:#9a7b5a; text-decoration:none; font-size:13px; }
@media (max-width: 1200px) { .overview-grid { grid-template-columns: 1fr; } .overview-charts { grid-template-columns: 1fr; } }
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
                        $newCustomersCount = 0;
                        $qNewCustomers = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='customer' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                        if ($qNewCustomers) {
                            $newCustomersCount = (int)$qNewCustomers->fetch_assoc()['total'];
                        }
                        $revenueLabels = [];
                        $revenueValues = [];
                        $revenueQuery = "
                            SELECT DATE_FORMAT(created_at, '%m/%Y') AS label, SUM(total_price) AS revenue
                            FROM orders
                            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                            ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC
                        ";
                        $revenueResult = $conn->query($revenueQuery);
                        if (!$revenueResult) {
                            $revenueQuery = "
                                SELECT DATE_FORMAT(created_at, '%m/%Y') AS label, SUM(total_amount) AS revenue
                                FROM orders
                                WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                                GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                                ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC
                            ";
                            $revenueResult = $conn->query($revenueQuery);
                        }
                        if ($revenueResult) {
                            while ($row = $revenueResult->fetch_assoc()) {
                                $revenueLabels[] = $row['label'];
                                $revenueValues[] = (float)($row['revenue'] ?? 0);
                            }
                        }
                        if (!$revenueLabels) {
                            $revenueLabels = ['Chưa có dữ liệu'];
                            $revenueValues = [0];
                        }

                        $statusLabels = [];
                        $statusValues = [];
                        $statusResult = $conn->query("SELECT status, COUNT(*) AS total FROM orders GROUP BY status");
                        if ($statusResult) {
                            while ($row = $statusResult->fetch_assoc()) {
                                $statusLabels[] = ucfirst((string)$row['status']);
                                $statusValues[] = (int)$row['total'];
                            }
                        }
                        if (!$statusLabels) {
                            $statusLabels = ['No data'];
                            $statusValues = [1];
                        }

                        $categoryLabels = [];
                        $categoryValues = [];
                        $categoryResult = $conn->query("
                            SELECT c.name, COUNT(p.id) AS total
                            FROM categories c
                            LEFT JOIN products p ON p.category_id = c.id
                            GROUP BY c.id, c.name
                            ORDER BY total DESC
                            LIMIT 5
                        ");
                        if ($categoryResult) {
                            while ($row = $categoryResult->fetch_assoc()) {
                                $categoryLabels[] = $row['name'] ?: 'Khác';
                                $categoryValues[] = (int)$row['total'];
                            }
                        }
                        if (!$categoryLabels) {
                            $categoryLabels = ['Chưa có dữ liệu'];
                            $categoryValues = [0];
                        }

                        $orderDateFrom = isset($_GET['order_date_from']) ? trim($_GET['order_date_from']) : '';
                        $orderDateTo = isset($_GET['order_date_to']) ? trim($_GET['order_date_to']) : '';
                        $dateValid = function ($d) { return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); };
                        $orderFilterFrom = ($orderDateFrom && $dateValid($orderDateFrom)) ? $orderDateFrom : date('Y-m-d', strtotime('-30 days'));
                        $orderFilterTo = ($orderDateTo && $dateValid($orderDateTo)) ? $orderDateTo : date('Y-m-d');
                        if (strtotime($orderFilterFrom) > strtotime($orderFilterTo)) {
                            $orderFilterFrom = $orderFilterTo;
                        }

                        $ordersInRange = false;
                        $todayOrdersStmt = $conn->prepare("
                            SELECT o.id, o.full_name, o.phone, o.address, o.total_amount, o.status, o.created_at
                            FROM orders o
                            WHERE DATE(o.created_at) >= ? AND DATE(o.created_at) <= ?
                            ORDER BY o.created_at DESC
                            LIMIT 30
                        ");
                        if ($todayOrdersStmt) {
                            $todayOrdersStmt->bind_param('ss', $orderFilterFrom, $orderFilterTo);
                            $todayOrdersStmt->execute();
                            $ordersInRange = $todayOrdersStmt->get_result();
                            $todayOrdersStmt->close();
                        }

                        $registeredCustomers = $conn->query("
                            SELECT id, username, email, full_name, phone, created_at
                            FROM users
                            WHERE role = 'customer'
                            ORDER BY created_at DESC
                            LIMIT 15
                        ");
                        ?>
                        <h1 class="admin-page-title">Tổng quan quản trị</h1>
                        <div class="admin-page-subtitle">Theo dõi nhanh dữ liệu chính của hệ thống Sweet Cake.</div>
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
                            <div class="overview-card">
                                <div class="metric-label">Doanh thu (đã giao)</div>
                                <div class="metric-num"><?php echo number_format(array_sum($revenueValues), 0, ',', '.'); ?>đ</div>
                            </div>
                            <div class="overview-card">
                                <div class="metric-label">Khách hàng mới (30 ngày)</div>
                                <div class="metric-num"><?php echo number_format($newCustomersCount); ?></div>
                            </div>
                        </div>
                        <div class="overview-charts">
                            <div class="chart-card">
                                <h3 class="chart-title">Doanh thu 6 tháng gần đây</h3>
                                <div class="chart-wrap"><canvas id="dashboardRevenueChart"></canvas></div>
                            </div>
                            <div class="chart-card">
                                <h3 class="chart-title">Trạng thái đơn hàng</h3>
                                <div class="chart-wrap"><canvas id="dashboardStatusChart"></canvas></div>
                            </div>
                            <div class="chart-card" style="grid-column: 1 / -1;">
                                <h3 class="chart-title">Top 5 danh mục theo số sản phẩm</h3>
                                <div class="chart-wrap"><canvas id="dashboardCategoryChart"></canvas></div>
                            </div>
                        </div>
                        <div class="overview-table-card">
                            <h3 class="overview-table-title">
                                Danh sách đơn hàng chi tiết đã đặt
                                <a class="link-small" href="admin_dashboard.php?page=orders">Xem tất cả</a>
                            </h3>
                            <form class="overview-filter" method="get">
                                <input type="hidden" name="page" value="dashboard">
                                <label for="order_date_from">Từ ngày</label>
                                <input type="date" id="order_date_from" name="order_date_from" value="<?php echo htmlspecialchars($orderFilterFrom); ?>">
                                <label for="order_date_to">Đến ngày</label>
                                <input type="date" id="order_date_to" name="order_date_to" value="<?php echo htmlspecialchars($orderFilterTo); ?>">
                                <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm">Lọc</button>
                                <a class="admin-btn admin-btn-secondary admin-btn-sm" href="admin_dashboard.php?page=dashboard">30 ngày gần nhất</a>
                            </form>
                            <table class="overview-table">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th><th>Khách hàng</th><th>SĐT</th><th>Địa chỉ</th><th>Thời gian</th><th>Tổng tiền</th><th>Trạng thái</th><th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $statusMap = ['pending' => 'pending', 'confirmed' => 'progress', 'processing' => 'progress', 'shipping' => 'progress', 'delivered' => 'done', 'completed' => 'done', 'cancelled' => 'cancel'];
                                    $statusTextMap = ['pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'processing' => 'Đang xử lý', 'shipping' => 'Đang giao', 'delivered' => 'Đã giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
                                    if ($ordersInRange && $ordersInRange->num_rows > 0):
                                        while ($ord = $ordersInRange->fetch_assoc()):
                                            $statusClass = $statusMap[$ord['status']] ?? 'pending';
                                            $statusText = $statusTextMap[$ord['status']] ?? $ord['status'];
                                    ?>
                                    <tr>
                                        <td>#<?php echo (int)$ord['id']; ?></td>
                                        <td><?php echo htmlspecialchars($ord['full_name'] ?: '—'); ?></td>
                                        <td><?php echo htmlspecialchars($ord['phone'] ?: '—'); ?></td>
                                        <td><?php echo htmlspecialchars(mb_substr($ord['address'] ?? '—', 0, 40)); ?><?php echo mb_strlen($ord['address'] ?? '') > 40 ? '…' : ''; ?></td>
                                        <td><?php echo date('H:i d/m/Y', strtotime($ord['created_at'])); ?></td>
                                        <td><?php echo number_format((float)$ord['total_amount'], 0, ',', '.'); ?>đ</td>
                                        <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusText); ?></span></td>
                                        <td><a class="link-small" href="admin_dashboard.php?page=order_detail&id=<?php echo (int)$ord['id']; ?>">Xem</a></td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="8" style="text-align:center;color:#7f716a;">Không có đơn hàng nào trong khoảng thời gian đã chọn.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="overview-table-card">
                            <h3 class="overview-table-title">
                                Khách hàng mới (đăng ký gần đây)
                                <a class="link-small" href="admin_dashboard.php?page=customers">Xem tất cả</a>
                            </h3>
                            <table class="overview-table">
                                <thead>
                                    <tr>
                                        <th>STT</th><th>Họ tên</th><th>Username / Email</th><th>Số điện thoại</th><th>Ngày đăng ký</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($registeredCustomers && $registeredCustomers->num_rows > 0): $stt = 1; while ($cust = $registeredCustomers->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $stt++; ?></td>
                                        <td><?php echo htmlspecialchars($cust['full_name'] ?: '—'); ?></td>
                                        <td><?php echo htmlspecialchars($cust['username']); ?><br><span style="font-size:12px;color:#7f716a;"><?php echo htmlspecialchars($cust['email']); ?></span></td>
                                        <td><?php echo htmlspecialchars($cust['phone'] ?: '—'); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($cust['created_at'])); ?></td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="5" style="text-align:center;color:#7f716a;">Chưa có khách hàng đăng ký.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <script>
                        (function () {
                            if (typeof Chart === 'undefined') return;
                            const revenueLabels = <?php echo json_encode($revenueLabels, JSON_UNESCAPED_UNICODE); ?>;
                            const revenueValues = <?php echo json_encode($revenueValues); ?>;
                            const statusLabels = <?php echo json_encode($statusLabels, JSON_UNESCAPED_UNICODE); ?>;
                            const statusValues = <?php echo json_encode($statusValues); ?>;
                            const categoryLabels = <?php echo json_encode($categoryLabels, JSON_UNESCAPED_UNICODE); ?>;
                            const categoryValues = <?php echo json_encode($categoryValues); ?>;

                            new Chart(document.getElementById('dashboardRevenueChart'), {
                                type: 'line',
                                data: {
                                    labels: revenueLabels,
                                    datasets: [{
                                        label: 'Doanh thu',
                                        data: revenueValues,
                                        borderColor: '#76553e',
                                        backgroundColor: 'rgba(118,85,62,0.12)',
                                        tension: 0.35,
                                        fill: true
                                    }]
                                },
                                options: { responsive: true, maintainAspectRatio: false }
                            });

                            new Chart(document.getElementById('dashboardStatusChart'), {
                                type: 'doughnut',
                                data: {
                                    labels: statusLabels,
                                    datasets: [{
                                        data: statusValues,
                                        backgroundColor: ['#76553e', '#916d55', '#cfa88d', '#e8d4c7', '#8c7e77']
                                    }]
                                },
                                options: { responsive: true, maintainAspectRatio: false }
                            });

                            new Chart(document.getElementById('dashboardCategoryChart'), {
                                type: 'bar',
                                data: {
                                    labels: categoryLabels,
                                    datasets: [{
                                        label: 'Số sản phẩm',
                                        data: categoryValues,
                                        backgroundColor: '#916d55'
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                                }
                            });
                        })();
                        </script>
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