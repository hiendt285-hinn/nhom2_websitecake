<?php
// manage_reports.php
ob_start();
require_once 'connect.php';

// Lấy thống kê
// Tổng doanh thu
$revenue_query = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status = 'completed'";
$revenue_result = $conn->query($revenue_query);
$total_revenue = $revenue_result->fetch_assoc()['total_revenue'] ?? 0;

// Tổng đơn hàng
$orders_query = "SELECT COUNT(*) as total_orders FROM orders";
$orders_result = $conn->query($orders_query);
$total_orders = $orders_result->fetch_assoc()['total_orders'] ?? 0;

// Tổng khách hàng
$customers_query = "SELECT COUNT(*) as total_customers FROM users WHERE role = 'customer'";
$customers_result = $conn->query($customers_query);
$total_customers = $customers_result->fetch_assoc()['total_customers'] ?? 0;

// Tổng sản phẩm
$products_query = "SELECT COUNT(*) as total_products FROM products";
$products_result = $conn->query($products_query);
$total_products = $products_result->fetch_assoc()['total_products'] ?? 0;

// Doanh thu theo tháng (6 tháng gần nhất)
$monthly_revenue_query = "
    SELECT 
        DATE_FORMAT(created_at, '%m/%Y') as month,
        SUM(total_amount) as revenue,
        COUNT(*) as order_count
    FROM orders 
    WHERE status = 'completed' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY DATE_FORMAT(created_at, '%Y-%m') ASC
";
$monthly_revenue = $conn->query($monthly_revenue_query);

// Doanh thu theo ngày (30 ngày gần nhất)
$daily_revenue_query = "
    SELECT 
        DATE(created_at) as day,
        SUM(total_amount) as revenue,
        COUNT(*) as order_count
    FROM orders 
    WHERE status = 'completed' 
        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
";
$daily_revenue = $conn->query($daily_revenue_query);

// Top sản phẩm bán chạy
$top_products_query = "
    SELECT 
        p.name,
        p.image,
        SUM(oi.quantity) as total_sold,
        SUM(oi.unit_price * oi.quantity) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
";
$top_products = $conn->query($top_products_query);

// Thống kê theo trạng thái đơn hàng
$order_status_query = "
    SELECT 
        status,
        COUNT(*) as count,
        SUM(total_amount) as total
    FROM orders
    GROUP BY status
";
$order_status = $conn->query($order_status_query);

// Doanh thu theo danh mục
$category_revenue_query = "
    SELECT 
        c.name as category,
        SUM(oi.unit_price * oi.quantity) as revenue,
        COUNT(DISTINCT o.id) as order_count
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY c.id
    ORDER BY revenue DESC
";
$category_revenue = $conn->query($category_revenue_query);

// Danh sách khách hàng đăng ký (mới nhất)
$registered_customers_query = "SELECT id, username, email, full_name, phone, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC LIMIT 15";
$registered_customers = $conn->query($registered_customers_query);

// Dữ liệu cho biểu đồ top sản phẩm (dùng lại query, không consume $top_products)
$top_products_chart_query = "
    SELECT p.name, SUM(oi.quantity) as total_sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10
";
$top_products_chart = $conn->query($top_products_chart_query);

// Danh sách đơn hàng chi tiết trong ngày (lọc theo ngày)
$order_date_param = isset($_GET['order_date']) ? trim($_GET['order_date']) : '';
if ($order_date_param && preg_match('/^\d{4}-\d{2}-\d{2}$/', $order_date_param)) {
    $order_filter_date = $order_date_param;
} else {
    $order_filter_date = date('Y-m-d');
}
$today_orders_stmt = $conn->prepare("
    SELECT o.id, o.full_name, o.phone, o.address, o.total_amount, o.status, o.created_at, o.payment_method
    FROM orders o
    WHERE DATE(o.created_at) = ?
    ORDER BY o.created_at DESC
");
$today_orders_stmt->bind_param('s', $order_filter_date);
$today_orders_stmt->execute();
$today_orders = $today_orders_stmt->get_result();
$today_orders_stmt->close();
$today_date_display = date('d/m/Y', strtotime($order_filter_date));
?>

<style>
    /* Report specific styles */
    .report-header {
        margin-bottom: 30px;
    }

    .report-header h2 {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .report-header p {
        color: #7f8c8d;
        font-size: 14px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .stat-info h3 {
        font-size: 14px;
        color: #7f8c8d;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        background: #f8f5f2;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #9a7b5a;
        font-size: 24px;
    }

    .revenue-icon { background: #e8f5e9; color: #2e7d32; }
    .orders-icon { background: #e3f2fd; color: #1565c0; }
    .customers-icon { background: #fff3e0; color: #ef6c00; }
    .products-icon { background: #f3e5f5; color: #7b1fa2; }

    .charts-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    .charts-row-single {
        grid-template-columns: 1fr;
    }

    .chart-container {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-header h3 {
        font-size: 16px;
        color: #2c3e50;
        font-weight: 600;
    }

    .chart-header select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 13px;
        color: #2c3e50;
        background: #fff;
        cursor: pointer;
    }

    .chart-header select:focus {
        outline: none;
        border-color: #9a7b5a;
    }

    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .tables-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .report-table {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.04);
    }

    .report-table h3 {
        font-size: 16px;
        color: #2c3e50;
        margin-bottom: 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .view-all {
        font-size: 13px;
        color: #9a7b5a;
        text-decoration: none;
        font-weight: normal;
    }

    .view-all:hover {
        text-decoration: underline;
    }

    .report-table table {
        width: 100%;
        border-collapse: collapse;
    }

    .report-table th {
        text-align: left;
        padding: 12px 10px;
        font-size: 13px;
        font-weight: 600;
        color: #7f8c8d;
        border-bottom: 2px solid #f0f0f0;
    }

    .report-table td {
        padding: 12px 10px;
        font-size: 14px;
        color: #2c3e50;
        border-bottom: 1px solid #f0f0f0;
    }

    .report-table tr:last-child td {
        border-bottom: none;
    }

    .product-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .product-image {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        object-fit: cover;
        background: #f5f5f5;
    }

    .product-name {
        font-weight: 600;
        color: #2c3e50;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-block;
    }

    .badge-pending { background: #fff3e0; color: #ef6c00; }
    .badge-completed { background: #e8f5e9; color: #2e7d32; }
    .badge-cancelled { background: #ffebee; color: #c62828; }
    .badge-processing { background: #e3f2fd; color: #1565c0; }

    .amount {
        font-weight: 600;
        color: #2c3e50;
    }

    .text-right { text-align: right; }

    .report-section { margin-bottom: 30px; }
    .report-table-full { max-width: 100%; }
    .report-date-filter {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .report-date-filter label { font-weight: 600; color: #2c3e50; font-size: 14px; }
    .report-date-filter input[type="date"] {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
    }
    .customer-username { font-weight: 600; color: #2c3e50; }
    .customer-email { font-size: 12px; color: #7f8c8d; }

    @media (max-width: 1024px) {
        .charts-row,
        .tables-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="report-header">
    <h2>Báo cáo & Thống kê</h2>
    <p>Tổng quan hoạt động kinh doanh của cửa hàng</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-info">
            <h3>Tổng doanh thu</h3>
            <div class="stat-number"><?php echo number_format($total_revenue, 0, ',', '.'); ?>đ</div>
        </div>
        <div class="stat-icon revenue-icon">
            <i class="fas fa-chart-pie"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Tổng đơn hàng</h3>
            <div class="stat-number"><?php echo number_format($total_orders); ?></div>
        </div>
        <div class="stat-icon orders-icon">
            <i class="fas fa-shopping-bag"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Khách hàng</h3>
            <div class="stat-number"><?php echo number_format($total_customers); ?></div>
        </div>
        <div class="stat-icon customers-icon">
            <i class="fas fa-users"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-info">
            <h3>Sản phẩm</h3>
            <div class="stat-number"><?php echo number_format($total_products); ?></div>
        </div>
        <div class="stat-icon products-icon">
            <i class="fas fa-cake-candles"></i>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="charts-row">
    <div class="chart-container">
        <div class="chart-header">
            <h3>Biểu đồ doanh thu</h3>
            <div style="display:flex; align-items:center; gap:10px;">
                <select id="revenueChartRange">
                    <option value="day">Doanh thu theo ngày</option>
                    <option value="month">Doanh thu theo tháng</option>
                </select>
                <select id="revenueChartType">
                    <option value="bar">Biểu đồ cột</option>
                    <option value="line">Biểu đồ đường</option>
                </select>
            </div>
        </div>
        <div class="chart-wrapper">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <div class="chart-container">
        <div class="chart-header">
            <h3>Trạng thái đơn hàng</h3>
        </div>
        <div class="chart-wrapper">
            <canvas id="orderStatusChart"></canvas>
        </div>
    </div>
</div>

<!-- Chart Top sản phẩm bán chạy -->
<div class="charts-row charts-row-single">
    <div class="chart-container">
        <div class="chart-header">
            <h3>Top sản phẩm bán chạy</h3>
            <a href="?page=products" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="chart-wrapper">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>
</div>

<!-- Tables Row -->
<div class="tables-row">
    <!-- Top sản phẩm -->
    <div class="report-table">
        <h3>
            Top sản phẩm bán chạy
            <a href="?page=products" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
        </h3>
    <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-right">Đã bán</th>
                    <th class="text-right">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($top_products && $top_products->num_rows > 0): ?>
                    <?php while($product = $top_products->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="product-info">
                                <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo $product['name']; ?>" 
                                     class="product-image"
                                     onerror="this.src='../images/no-image.png'">
                                <span class="product-name"><?php echo htmlspecialchars($product['name']); ?></span>
                            </div>
                        </td>
                        <td class="text-right"><?php echo number_format($product['total_sold']); ?></td>
                        <td class="text-right amount"><?php echo number_format($product['revenue'], 0, ',', '.'); ?>đ</td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            Chưa có dữ liệu
                        </td>
            </tr>
                <?php endif; ?>
            </tbody>
    </table>
    </div>

    <!-- Thống kê theo danh mục -->
    <div class="report-table">
        <h3>
            Doanh thu theo danh mục
            <a href="?page=producttype" class="view-all">Xem chi tiết <i class="fas fa-arrow-right"></i></a>
        </h3>
    <table>
            <thead>
                <tr>
                    <th>Danh mục</th>
                    <th class="text-right">Đơn hàng</th>
                    <th class="text-right">Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($category_revenue && $category_revenue->num_rows > 0): ?>
                    <?php while($category = $category_revenue->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['category']); ?></td>
                        <td class="text-right"><?php echo number_format($category['order_count']); ?></td>
                        <td class="text-right amount"><?php echo number_format($category['revenue'], 0, ',', '.'); ?>đ</td>
                    </tr>
                    <?php endwhile; ?>
        <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            Chưa có dữ liệu
                        </td>
                    </tr>
        <?php endif; ?>
            </tbody>
    </table>
    </div>
</div>

<!-- Danh sách đơn hàng chi tiết trong ngày -->
<div class="report-section">
    <div class="report-table report-table-full">
        <h3>
            <i class="fas fa-list-alt"></i> Danh sách đơn hàng chi tiết trong ngày (<?php echo $today_date_display; ?>)
            <a href="?page=orders" class="view-all">Xem tất cả đơn hàng <i class="fas fa-arrow-right"></i></a>
        </h3>
        <form method="get" class="report-date-filter">
            <input type="hidden" name="page" value="reports">
            <label for="order_date">Lọc theo ngày:</label>
            <input type="date" id="order_date" name="order_date" value="<?php echo htmlspecialchars($order_filter_date); ?>">
            <button type="submit" class="admin-btn admin-btn-primary admin-btn-sm"><i class="fas fa-filter"></i> Lọc</button>
            <a href="?page=reports" class="admin-btn admin-btn-secondary admin-btn-sm" style="text-decoration:none; margin-left:6px;">Hôm nay</a>
        </form>
        <table>
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Số điện thoại</th>
                    <th>Địa chỉ</th>
                    <th>Thời gian</th>
                    <th class="text-right">Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $statusLabels = ['pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'processing' => 'Đang xử lý', 'shipping' => 'Đang giao', 'delivered' => 'Đã giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy'];
                if ($today_orders && $today_orders->num_rows > 0):
                    while ($ord = $today_orders->fetch_assoc()):
                        $statusText = $statusLabels[$ord['status']] ?? $ord['status'];
                        $statusClass = 'badge-pending';
                        if (in_array($ord['status'], ['completed', 'delivered'])) $statusClass = 'badge-completed';
                        elseif ($ord['status'] === 'cancelled') $statusClass = 'badge-cancelled';
                        elseif (in_array($ord['status'], ['confirmed', 'processing', 'shipping'])) $statusClass = 'badge-processing';
                ?>
                    <tr>
                        <td><strong>#<?php echo (int)$ord['id']; ?></strong></td>
                        <td><?php echo htmlspecialchars($ord['full_name'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($ord['phone'] ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars(mb_substr($ord['address'] ?? '—', 0, 40)); ?><?php echo mb_strlen($ord['address'] ?? '') > 40 ? '…' : ''; ?></td>
                        <td><?php echo date('H:i d/m/Y', strtotime($ord['created_at'])); ?></td>
                        <td class="text-right amount"><?php echo number_format($ord['total_amount'], 0, ',', '.'); ?>đ</td>
                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusText); ?></span></td>
                        <td><a href="?page=orders" class="view-all">Xem</a></td>
                    </tr>
                <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #7f8c8d;">Không có đơn hàng nào trong ngày hôm nay.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Danh sách khách hàng đăng ký (cuối trang) -->
<div class="report-section">
    <div class="report-table report-table-full">
        <h3>
            Danh sách khách hàng đăng ký
            <a href="?page=customers" class="view-all">Xem tất cả <i class="fas fa-arrow-right"></i></a>
        </h3>
        <table>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Họ tên</th>
                    <th>Username / Email</th>
                    <th>Số điện thoại</th>
                    <th>Ngày đăng ký</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($registered_customers && $registered_customers->num_rows > 0): ?>
                    <?php $stt = 1; while($cust = $registered_customers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $stt++; ?></td>
                        <td><?php echo htmlspecialchars($cust['full_name'] ?: '—'); ?></td>
                        <td>
                            <span class="customer-username"><?php echo htmlspecialchars($cust['username']); ?></span><br>
                            <span class="customer-email"><?php echo htmlspecialchars($cust['email']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($cust['phone'] ?: '—'); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($cust['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
        <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            Chưa có khách hàng đăng ký
                        </td>
                    </tr>
        <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js chưa tải. Vui lòng tải lại trang.');
        return;
    }
    // Dữ liệu doanh thu theo tháng
    const months = [];
    const revenues = [];
    <?php 
    if ($monthly_revenue) {
        $monthly_revenue->data_seek(0);
        while($row = $monthly_revenue->fetch_assoc()) {
            echo "months.push('" . addslashes($row['month']) . "'); revenues.push(" . (float)($row['revenue'] ?? 0) . ");\n";
        }
    }
    ?>

    // Dữ liệu doanh thu theo ngày (30 ngày gần nhất)
    const days = [];
    const dayRevenues = [];
    <?php 
    if ($daily_revenue && $daily_revenue->num_rows > 0) {
        $daily_revenue->data_seek(0);
        while($row = $daily_revenue->fetch_assoc()) {
            $d = $row['day'];
            echo "days.push('" . date('d/m', strtotime($d)) . "'); dayRevenues.push(" . (float)($row['revenue'] ?? 0) . ");\n";
        }
    }
    ?>

    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    let revenueChart = null;

    function updateRevenueChart() {
        const range = document.getElementById('revenueChartRange').value;
        const chartType = document.getElementById('revenueChartType').value;
        const isDay = range === 'day';
        const labels = isDay ? days : months;
        const data = isDay ? dayRevenues : revenues;

        if (revenueChart) revenueChart.destroy();
        revenueChart = new Chart(revenueCtx, {
            type: chartType,
            data: {
                labels: labels,
                datasets: [{
                    label: 'Doanh thu (VNĐ)',
                    data: data,
                    backgroundColor: 'rgba(154, 123, 90, 0.2)',
                    borderColor: '#9a7b5a',
                    borderWidth: 2,
                    borderRadius: 6,
                    tension: 0.4,
                    fill: chartType === 'line'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw || 0;
                                return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000000) return (value / 1000000) + 'tr';
                                if (value >= 1000) return (value / 1000) + 'k';
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }

    updateRevenueChart();
    document.getElementById('revenueChartRange').addEventListener('change', updateRevenueChart);
    document.getElementById('revenueChartType').addEventListener('change', updateRevenueChart);

    // Dữ liệu trạng thái đơn hàng
    const statusLabels = [];
    const statusCounts = [];
    const statusColors = [];
    <?php 
    $statusColorsMap = array('pending' => '#ef6c00', 'completed' => '#2e7d32', 'cancelled' => '#c62828', 'processing' => '#1565c0', 'confirmed' => '#1565c0', 'shipping' => '#0277bd', 'delivered' => '#2e7d32');
    if ($order_status) {
        $order_status->data_seek(0);
        while($row = $order_status->fetch_assoc()) {
            $color = isset($statusColorsMap[$row['status']]) ? $statusColorsMap[$row['status']] : '#7f8c8d';
            $label = addslashes(ucfirst($row['status']));
            echo "statusLabels.push('" . $label . "'); statusCounts.push(" . (int)$row['count'] . "); statusColors.push('" . $color . "');\n";
        }
    }
    ?>

    // Biểu đồ trạng thái đơn hàng
    new Chart(document.getElementById('orderStatusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusCounts,
                backgroundColor: statusColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        font: {
                            size: 12
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });

    // Dữ liệu top sản phẩm bán chạy
    const topProductLabels = [];
    const topProductData = [];
    <?php
    if ($top_products_chart && $top_products_chart->num_rows > 0) {
        while ($row = $top_products_chart->fetch_assoc()) {
            $name = addslashes($row['name']);
            if (mb_strlen($row['name']) > 25) {
                $name = addslashes(mb_substr($row['name'], 0, 25) . '...');
            }
            echo "topProductLabels.push('" . $name . "'); topProductData.push(" . (int)$row['total_sold'] . ");\n";
        }
    }
    ?>

    // Biểu đồ top sản phẩm bán chạy (bar ngang)
    const topProductsCtx = document.getElementById('topProductsChart');
    if (topProductsCtx && topProductLabels.length > 0) {
        new Chart(topProductsCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: topProductLabels,
                datasets: [{
                    label: 'Số lượng bán',
                    data: topProductData,
                    backgroundColor: 'rgba(154, 123, 90, 0.6)',
                    borderColor: '#9a7b5a',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Đã bán: ' + context.raw + ' sản phẩm';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>