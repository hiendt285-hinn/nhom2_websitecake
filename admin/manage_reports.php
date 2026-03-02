<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

require_once 'connect.php';

// Tham số lọc (GET)
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$productSearch = isset($_GET['product_search']) ? trim($_GET['product_search']) : '';

// Xây dựng WHERE và params (thứ tự: ngày trước, sau đó sản phẩm)
$where = "1=1";
$params = [];
$types = '';

$hasDateFilter = (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate));
if ($hasDateFilter) {
    $where .= " AND o.created_at BETWEEN ? AND ?";
    $params[] = $startDate . ' 00:00:00';
    $params[] = $endDate . ' 23:59:59';
    $types .= 'ss';
}
if ($productSearch !== '') {
    $where .= " AND o.id IN (SELECT oi2.order_id FROM order_items oi2 JOIN products p2 ON oi2.product_id = p2.id WHERE p2.name LIKE ?)";
    $params[] = '%' . $productSearch . '%';
    $types .= 's';
}

$runQuery = function($sql, $params, $types) use ($conn) {
    if (strlen($types) > 0 && count($params) > 0) {
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        }
    }
    return $conn->query($sql);
};

// Tổng doanh thu
$sqlTotal = "SELECT COALESCE(SUM(o.total_amount), 0) AS total_revenue FROM orders o WHERE $where";
$resTotal = $runQuery($sqlTotal, $params, $types);
$totalRevenue = $resTotal && $row = $resTotal->fetch_assoc() ? (float)$row['total_revenue'] : 0;

// Số đơn theo trạng thái (lưu vào mảng để dùng nhiều lần)
$sqlStatus = "SELECT o.status, COUNT(*) AS cnt FROM orders o WHERE $where GROUP BY o.status";
$resStatus = $runQuery($sqlStatus, $params, $types);
$statusRows = [];
if ($resStatus) {
    while ($r = $resStatus->fetch_assoc()) {
        $statusRows[] = $r;
    }
}

// Danh sách đơn hàng
$sqlOrders = "SELECT o.* FROM orders o WHERE $where ORDER BY o.created_at DESC";
$ordersResult = $runQuery($sqlOrders, $params, $types);
$ordersList = [];
if ($ordersResult && $ordersResult->num_rows > 0) {
    while ($r = $ordersResult->fetch_assoc()) {
        $ordersList[] = $r;
    }
}

// Lấy "sản phẩm đã đặt" cho từng đơn (order_id => [ "Tên x qty", ... ])
$orderProducts = [];
if (!empty($ordersList)) {
    $ids = array_map(function ($o) { return (int)$o['id']; }, $ordersList);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sqlItems = "SELECT oi.order_id, p.name, oi.quantity
                 FROM order_items oi
                 JOIN products p ON oi.product_id = p.id
                 WHERE oi.order_id IN ($placeholders)
                 ORDER BY oi.order_id, oi.id";
    $stmtItems = $conn->prepare($sqlItems);
    if ($stmtItems) {
        $bindParams = array_merge([str_repeat('i', count($ids))], $ids);
        $refs = [];
        foreach ($bindParams as $key => $val) {
            $refs[$key] = &$bindParams[$key];
        }
        call_user_func_array([$stmtItems, 'bind_param'], $refs);
        $stmtItems->execute();
        $resItems = $stmtItems->get_result();
        if ($resItems) {
            while ($row = $resItems->fetch_assoc()) {
                $oid = (int)$row['order_id'];
                if (!isset($orderProducts[$oid])) {
                    $orderProducts[$oid] = [];
                }
                $orderProducts[$oid][] = htmlspecialchars($row['name']) . ' x' . (int)$row['quantity'];
            }
        }
        $stmtItems->close();
    }
}

// Dữ liệu biểu đồ: theo từng loại (danh mục) - cột riêng từng loại
$chartWhere = $where;
$chartParams = $params;
$chartTypes = $types;
$sqlChartCategory = "SELECT COALESCE(c.name, 'Chưa phân loại') AS category_name, SUM(oi.quantity) AS total_qty, SUM(oi.quantity * oi.unit_price) AS total_revenue
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE $chartWhere
    GROUP BY c.id, COALESCE(c.name, 'Chưa phân loại')
    ORDER BY total_qty DESC";
$chartResultCategory = $runQuery($sqlChartCategory, $chartParams, $chartTypes);
$chartLabelsCategory = [];
$chartDataQty = [];
$chartDataRevenue = [];
if ($chartResultCategory && $chartResultCategory->num_rows > 0) {
    while ($r = $chartResultCategory->fetch_assoc()) {
        $chartLabelsCategory[] = $r['category_name'];
        $chartDataQty[] = (int)$r['total_qty'];
        $chartDataRevenue[] = (float)$r['total_revenue'];
    }
}

// Thống kê người dùng đăng ký (khách hàng)
$userRegWhere = "1=1";
$userRegParams = [];
$userRegTypes = '';
if ($hasDateFilter) {
    $userRegWhere .= " AND created_at BETWEEN ? AND ?";
    $userRegParams[] = $startDate . ' 00:00:00';
    $userRegParams[] = $endDate . ' 23:59:59';
    $userRegTypes .= 'ss';
}
$sqlUserCount = "SELECT COUNT(*) AS total FROM users WHERE role = 'customer' AND $userRegWhere";
if (count($userRegParams) > 0) {
    $stmtU = $conn->prepare($sqlUserCount);
    $stmtU->bind_param($userRegTypes, ...$userRegParams);
    $stmtU->execute();
    $userRegCount = (int)($stmtU->get_result()->fetch_assoc()['total'] ?? 0);
    $stmtU->close();
} else {
    $userRegCount = (int)($conn->query($sqlUserCount)->fetch_assoc()['total']);
}
// Đăng ký theo ngày (trong khoảng lọc)
$userRegByDay = [];
if ($hasDateFilter) {
    $sqlUserByDay = "SELECT DATE(created_at) AS day_key, COUNT(*) AS cnt FROM users WHERE role = 'customer' AND created_at BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY day_key";
    $stmtU2 = $conn->prepare($sqlUserByDay);
    $stmtU2->bind_param('ss', $startDate . ' 00:00:00', $endDate . ' 23:59:59');
    $stmtU2->execute();
    $userRegByDay = $stmtU2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmtU2->close();
}

// Chi tiết danh sách người dùng đăng ký (theo bộ lọc)
$sqlUserList = "SELECT id, username, full_name, email, phone, created_at FROM users WHERE role = 'customer' AND $userRegWhere ORDER BY created_at DESC";
if (count($userRegParams) > 0) {
    $stmtUL = $conn->prepare($sqlUserList);
    $stmtUL->bind_param($userRegTypes, ...$userRegParams);
    $stmtUL->execute();
    $userListResult = $stmtUL->get_result();
    $stmtUL->close();
} else {
    $userListResult = $conn->query($sqlUserList);
}
$userListRows = [];
if ($userListResult && $userListResult->num_rows > 0) {
    while ($r = $userListResult->fetch_assoc()) {
        $userListRows[] = $r;
    }
}

// Top sản phẩm bán chạy
$sqlBestSelling = "SELECT p.name, SUM(oi.quantity) AS total_qty, SUM(oi.quantity * oi.unit_price) AS total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE $where
    GROUP BY p.id, p.name
    ORDER BY total_qty DESC
    LIMIT 10";
$bestSelling = $runQuery($sqlBestSelling, $params, $types);

// Doanh thu theo ngày (áp dụng cùng bộ lọc: ngày + sản phẩm)
$revenueByDay = [];
$whereDay = "1=1";
$paramsDay = [];
$typesDay = '';
if ($hasDateFilter) {
    $whereDay .= " AND created_at BETWEEN ? AND ?";
    $paramsDay[] = $startDate . ' 00:00:00';
    $paramsDay[] = $endDate . ' 23:59:59';
    $typesDay .= 'ss';
}
if ($productSearch !== '') {
    $whereDay .= " AND id IN (SELECT order_id FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.name LIKE ?)";
    $paramsDay[] = '%' . $productSearch . '%';
    $typesDay .= 's';
}
$sqlDay = "SELECT DATE(created_at) AS day_key, SUM(total_amount) AS day_revenue, COUNT(*) AS order_count FROM orders WHERE $whereDay GROUP BY DATE(created_at) ORDER BY day_key ASC";
if (count($paramsDay) > 0) {
    $stmtDay = $conn->prepare($sqlDay);
    if ($stmtDay) {
        $stmtDay->bind_param($typesDay, ...$paramsDay);
        $stmtDay->execute();
        $revenueByDay = $stmtDay->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmtDay->close();
    }
} else {
    $r = $conn->query($sqlDay);
    $revenueByDay = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<div class="admin-content">
    <h1 class="admin-page-title"><i class="fas fa-chart-line"></i> Báo cáo doanh thu - Thống kê</h1>

    <form method="get" class="admin-card" style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
        <label style="font-weight:600; color:#5D4037;">Ngày bắt đầu:</label>
        <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" style="padding:8px 12px; border:1px solid #ccc; border-radius:6px;">
        <label style="font-weight:600; color:#5D4037;">Ngày kết thúc:</label>
        <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" style="padding:8px 12px; border:1px solid #ccc; border-radius:6px;">
        <label style="font-weight:600; color:#5D4037;">Tìm sản phẩm:</label>
        <input type="text" name="product_search" placeholder="Tên bánh..." value="<?php echo htmlspecialchars($productSearch); ?>" style="padding:8px 12px; border:1px solid #ccc; border-radius:6px; min-width:180px;">
        <button type="submit" class="admin-btn admin-btn-primary">Lọc</button>
    </form>

    <div class="admin-card">
        <h2>Tổng doanh thu: <strong style="color:#8B6F47;"><?php echo number_format((float)$totalRevenue, 0, ',', '.'); ?> ₫</strong></h2>
    </div>

    <div class="admin-card">
        <h2>Mặt hàng bán chạy (Top 10)</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Sản phẩm</th>
                    <th>Số lượng bán</th>
                    <th>Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bestSelling && $bestSelling->num_rows > 0): ?>
                    <?php $rank = 1; while ($row = $bestSelling->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $rank++; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo number_format((int)$row['total_qty'], 0, ',', '.'); ?></td>
                            <td><?php echo number_format((float)$row['total_revenue'], 0, ',', '.'); ?> ₫</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Chưa có dữ liệu</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($revenueByDay)): ?>
    <div class="admin-card">
        <h2>Doanh thu theo ngày</h2>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Ngày</th>
                    <th>Số đơn</th>
                    <th>Doanh thu</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($revenueByDay as $r): ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($r['day_key'])); ?></td>
                        <td><?php echo (int)$r['order_count']; ?></td>
                        <td><?php echo number_format((float)$r['day_revenue'], 0, ',', '.'); ?> ₫</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <div class="admin-card">
        <h2>Số lượng đơn hàng theo trạng thái</h2>
        <table class="admin-table">
        <tr><th>Trạng thái</th><th>Số lượng</th></tr>
        <?php foreach ($statusRows as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                <td><?php echo (int)$row['cnt']; ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($statusRows)): ?>
            <tr><td colspan="2">Chưa có đơn hàng</td></tr>
        <?php endif; ?>
        </table>
    </div>

    <div class="admin-card">
        <h2>Thống kê người dùng đăng ký tài khoản</h2>
        <p class="muted"><?php echo $hasDateFilter ? "Trong khoảng $startDate đến $endDate" : 'Toàn thời gian'; ?></p>
        <p style="font-size: 18px; margin-top: 8px;"><strong>Số tài khoản khách hàng đăng ký: <?php echo number_format($userRegCount, 0, ',', '.'); ?></strong></p>
        <?php if (!empty($userRegByDay)): ?>
        <table class="admin-table" style="margin-top: 12px;">
            <thead><tr><th>Ngày</th><th>Số đăng ký</th></tr></thead>
            <tbody>
                <?php foreach ($userRegByDay as $ur): ?>
                    <tr><td><?php echo date('d/m/Y', strtotime($ur['day_key'])); ?></td><td><?php echo (int)$ur['cnt']; ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <div class="admin-card">
        <h2>Chi tiết người dùng đăng ký tài khoản</h2>
        <p class="muted"><?php echo $hasDateFilter ? "Trong khoảng $startDate đến $endDate" : 'Toàn thời gian'; ?> — Tổng: <?php echo count($userListRows); ?> tài khoản</p>
        <div style="overflow-x: auto;">
        <table class="admin-table" style="margin-top: 12px;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Số điện thoại</th>
                    <th>Ngày đăng ký</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($userListRows as $u): ?>
                    <tr>
                        <td><?php echo (int)$u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username']); ?></td>
                        <td><?php echo htmlspecialchars($u['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['phone']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($userListRows)): ?>
                    <tr><td colspan="6">Chưa có tài khoản nào</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="admin-card">
        <h2>Chi tiết đơn hàng</h2>
        <table class="admin-table">
        <tr>
            <th>ID</th>
            <th>Khách hàng</th>
            <th>Số điện thoại</th>
            <th>Địa chỉ</th>
            <th>Sản phẩm đã đặt</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
        </tr>
        <?php if (!empty($ordersList)): ?>
            <?php foreach ($ordersList as $row): ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td style="max-width:320px; font-size:13px;"><?php
                        $oid = (int)$row['id'];
                        echo isset($orderProducts[$oid]) ? implode(', ', $orderProducts[$oid]) : '—';
                    ?></td>
                    <td><?php echo number_format((float)$row['total_amount'], 0, ',', '.'); ?> ₫</td>
                    <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8">Không có đơn hàng nào</td></tr>
        <?php endif; ?>
        </table>
    </div>

    <div class="admin-card chart-wrap">
        <h2>Biểu đồ cột theo từng loại (danh mục)</h2>
        <?php if (empty($chartLabelsCategory)): ?>
            <p class="muted">Chưa có dữ liệu. Hãy chọn khoảng ngày hoặc có đơn hàng.</p>
        <?php else: ?>
            <canvas id="chartByCategory" height="120"></canvas>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php if (!empty($chartLabelsCategory)): ?>
<script>
(function() {
    var ctx = document.getElementById('chartByCategory');
    if (!ctx) return;
    var labels = <?php echo json_encode($chartLabelsCategory); ?>;
    var dataQty = <?php echo json_encode($chartDataQty); ?>;
    var colors = ['#8B6F47', '#A0826D', '#5D4037', '#D4A574', '#6B5344', '#8D6E63', '#4E342E'];
    var bg = labels.map(function(_, i) { return colors[i % colors.length]; });
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Số lượng bán',
                data: dataQty,
                backgroundColor: bg,
                borderColor: bg.map(function(c) { return c; }),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                x: { beginAtZero: true },
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { afterLabel: function(ctx) { return 'Tổng: ' + ctx.raw + ' SP'; } } }
            }
        }
    });
})();
</script>
<?php endif; ?>
