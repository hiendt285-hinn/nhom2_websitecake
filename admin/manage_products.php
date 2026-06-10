<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}


require_once 'connect.php';

mysqli_report(MYSQLI_REPORT_OFF);
$addError = '';
$addSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name) || $price < 0 || $categoryId <= 0) {
        $addError = 'Vui lòng điền Tên sản phẩm, Giá và chọn Danh mục.';
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            $addError = 'Chỉ chấp nhận định dạng ảnh: jpg, jpeg, png, gif, webp.';
        } else {
            $newFileName = uniqid() . '.' . $ext;
            $targetDir = realpath(__DIR__ . '/../images') . '/';
            $targetFile = $targetDir . $newFileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO products (name, price, image, description, short_description, category_id, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param('sisssiii', $name, $price, $newFileName, $description, $shortDescription, $categoryId, $isFeatured, $isActive);
                    if ($stmt->execute()) {
                        header('Location: admin_dashboard.php?page=products&added=1');
                        exit();
                    }
                    $stmt->close();
                }
                $addError = 'Lỗi khi thêm sản phẩm.';
            } else {
                $addError = 'Lỗi khi upload ảnh.';
            }
        }
    } else {
        $addError = 'Vui lòng chọn ảnh sản phẩm.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = (int) ($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($productId <= 0 || $name === '' || $price < 0 || $categoryId <= 0) {
        $addError = 'Dữ liệu cập nhật chưa hợp lệ. Vui lòng kiểm tra lại.';
    } else {
        $currentStmt = $conn->prepare("SELECT image FROM products WHERE id = ? LIMIT 1");
        $currentStmt->bind_param('i', $productId);
        $currentStmt->execute();
        $currentResult = $currentStmt->get_result();
        $currentProduct = $currentResult ? $currentResult->fetch_assoc() : null;
        $currentStmt->close();

        if (!$currentProduct) {
            $addError = 'Không tìm thấy sản phẩm cần chỉnh sửa.';
        } else {
            $newImage = $currentProduct['image'] ?? '';
            if (isset($_FILES['image']) && (int)($_FILES['image']['error'] ?? 4) === 0) {
                $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $fileName = basename($_FILES['image']['name']);
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    $addError = 'Chỉ chấp nhận định dạng ảnh: jpg, jpeg, png, gif, webp.';
                } else {
                    $newFileName = uniqid() . '.' . $ext;
                    $targetDir = realpath(__DIR__ . '/../images') . '/';
                    $targetFile = $targetDir . $newFileName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        if (!empty($newImage) && file_exists($targetDir . $newImage)) {
                            @unlink($targetDir . $newImage);
                        }
                        $newImage = $newFileName;
                    } else {
                        $addError = 'Lỗi khi upload ảnh mới.';
                    }
                }
            }

            if ($addError === '') {
                $stmt = $conn->prepare("UPDATE products SET name = ?, price = ?, image = ?, description = ?, short_description = ?, category_id = ?, is_featured = ?, is_active = ? WHERE id = ?");
                if ($stmt) {
                    $stmt->bind_param('sisssiiii', $name, $price, $newImage, $description, $shortDescription, $categoryId, $isFeatured, $isActive, $productId);
                    if ($stmt->execute()) {
                        $stmt->close();
                        header('Location: admin_dashboard.php?page=products&edited=1');
                        exit();
                    }
                    $stmt->close();
                }
                $addError = 'Lỗi khi cập nhật sản phẩm.';
            }
        }
    }
}

// Xử lý xóa sản phẩm (nếu có)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Có order_items → không xóa cứng (FK + lịch sử đơn), chỉ ẩn khỏi cửa hàng.
    // Không chặn khi đơn còn pending/shipping: ẩn sản phẩm vẫn an toàn vì dòng đơn giữ nguyên product_id.
    $checkAnyOrder = $conn->prepare("SELECT COUNT(*) AS cnt FROM order_items WHERE product_id = ?");
    $checkAnyOrder->bind_param('i', $delete_id);
    $checkAnyOrder->execute();
    $inAnyOrder = (int) $checkAnyOrder->get_result()->fetch_assoc()['cnt'];
    $checkAnyOrder->close();

    if ($inAnyOrder === 0) {
        // Không có trong đơn nào → xóa hẳn
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param('i', $delete_id);
        if ($stmt->execute()) {
            $stmt->close();
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true]);
                exit();
            }
            header('Location: admin_dashboard.php?page=products');
            exit();
        }
        $errorMsg = $conn->error ?: 'Lỗi khi xóa sản phẩm.';
        $stmt->close();
    } else {
        // Có trong đơn hàng → chỉ ẩn sản phẩm
        $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->bind_param('i', $delete_id);
        if ($stmt->execute()) {
            $stmt->close();
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'hidden' => true,
                    'message' => 'Sản phẩm đã được ẩn khỏi cửa hàng. Dữ liệu trong đơn hàng vẫn được giữ nguyên.',
                ]);
                exit();
            }
            header('Location: admin_dashboard.php?page=products&deleted_hidden=1');
            exit();
        }
        $errorMsg = $conn->error ?: 'Lỗi khi ẩn sản phẩm.';
        $stmt->close();
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'error' => $errorMsg]);
        exit();
    }
    header('Location: admin_dashboard.php?page=products&delete_error=1');
    exit();
}

// Phân trang: 20 sản phẩm / trang (mặc định chỉ SP đang bán; ?show_inactive=1 để xem cả đã ẩn)
$perPage = 20;
$showInactive = isset($_GET['show_inactive']) && $_GET['show_inactive'] === '1';
$searchRaw = isset($_GET['q']) ? (string) $_GET['q'] : '';
$search = trim($searchRaw);
if (function_exists('mb_substr')) {
    $search = mb_substr($search, 0, 200);
} else {
    $search = substr($search, 0, 200);
}

$currentPage = max(1, isset($_GET['pg']) ? (int)$_GET['pg'] : 1);

$whereParts = [];
if (!$showInactive) {
    $whereParts[] = 'p.is_active = 1';
}
if ($search !== '') {
    $whereParts[] = '(p.name LIKE ? OR IFNULL(p.short_description, \'\') LIKE ? OR IFNULL(p.description, \'\') LIKE ? OR IFNULL(c.name, \'\') LIKE ?)';
}
$listWhereClause = $whereParts ? ' WHERE ' . implode(' AND ', $whereParts) : '';

$countSql = 'SELECT COUNT(DISTINCT p.id) AS total FROM products p LEFT JOIN categories c ON p.category_id = c.id' . $listWhereClause;
$countStmt = $conn->prepare($countSql);
$totalProducts = 0;
if ($countStmt) {
    if ($search !== '') {
        $like1 = '%' . $search . '%';
        $like2 = '%' . $search . '%';
        $like3 = '%' . $search . '%';
        $like4 = '%' . $search . '%';
        $countStmt->bind_param('ssss', $like1, $like2, $like3, $like4);
    }
    if ($countStmt->execute()) {
        $countRes = $countStmt->get_result();
        $countRow = $countRes ? $countRes->fetch_assoc() : null;
        $totalProducts = $countRow ? (int) $countRow['total'] : 0;
    }
    $countStmt->close();
}

$totalPages = $totalProducts > 0 ? (int)ceil($totalProducts / $perPage) : 1;
$currentPage = min(max(1, $currentPage), $totalPages);
$offset = ($currentPage - 1) * $perPage;

// Bộ lọc sắp xếp:
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
if (!in_array($sort, ['newest', 'sold_desc', 'sold_asc'], true)) {
    $sort = 'newest';
}
$orderBy = 'p.id DESC';
if ($sort === 'sold_desc') {
    $orderBy = 'total_sold DESC, p.id DESC';
} elseif ($sort === 'sold_asc') {
    $orderBy = 'total_sold ASC, p.id DESC';
}
$sortParam = '&sort=' . urlencode($sort);
$inactiveParam = $showInactive ? '&show_inactive=1' : '';
$searchParam = $search !== '' ? '&q=' . urlencode($search) : '';

// Lấy danh sách sản phẩm với tên danh mục và tổng đã bán 
$sql = "SELECT p.*, c.name AS category_name,
        COALESCE(SUM(CASE WHEN o.status IN ('completed', 'delivered') THEN oi.quantity ELSE 0 END), 0) AS total_sold
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN order_items oi ON oi.product_id = p.id
        LEFT JOIN orders o ON o.id = oi.order_id
        " . $listWhereClause . "
        GROUP BY p.id, c.name
        ORDER BY " . $orderBy . "
        LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
$listStmt = $conn->prepare($sql);
$result = false;
if ($listStmt) {
    if ($search !== '') {
        $l1 = '%' . $search . '%';
        $l2 = '%' . $search . '%';
        $l3 = '%' . $search . '%';
        $l4 = '%' . $search . '%';
        $listStmt->bind_param('ssss', $l1, $l2, $l3, $l4);
    }
    if ($listStmt->execute()) {
        $result = $listStmt->get_result();
    }
    $listStmt->close();
}
if ($result === false) {
    $fallbackSql = "SELECT p.*, c.name AS category_name, 0 AS total_sold
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            " . $listWhereClause . "
            ORDER BY p.id DESC
            LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
    $fbStmt = $conn->prepare($fallbackSql);
    if ($fbStmt) {
        if ($search !== '') {
            $f1 = '%' . $search . '%';
            $f2 = '%' . $search . '%';
            $f3 = '%' . $search . '%';
            $f4 = '%' . $search . '%';
            $fbStmt->bind_param('ssss', $f1, $f2, $f3, $f4);
        }
        if ($fbStmt->execute()) {
            $result = $fbStmt->get_result();
        }
        $fbStmt->close();
    }
}
?>
<?php
$activeProducts = 0;
$outOfStockProducts = 0;
$monthlyRevenue = 0;
$activeResult = $conn->query("SELECT COUNT(*) AS total FROM products WHERE is_active = 1");
if ($activeResult) {
    $activeProducts = (int)$activeResult->fetch_assoc()['total'];
}
$stockResult = $conn->query("SELECT COUNT(*) AS total FROM products WHERE quantity <= 0");
if ($stockResult) {
    $outOfStockProducts = (int)$stockResult->fetch_assoc()['total'];
}
$revenueResult = $conn->query("SELECT COALESCE(SUM(total_price), 0) AS total FROM orders");
if ($revenueResult) {
    $monthlyRevenue = (float)$revenueResult->fetch_assoc()['total'];
}
$categories = [];
$categoryResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($categoryResult) {
    while ($cat = $categoryResult->fetch_assoc()) {
        $categories[] = $cat;
    }
}
?>
<style>
.product-layout{color:#1b1c1b}
.product-topbar{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.h-input{background:#f5f3f1;border:none;border-radius:999px;padding:11px 14px;width:280px}
.h-btn{border:none;border-radius:999px;padding:10px 18px;display:inline-flex;align-items:center;gap:8px;font-weight:600;text-decoration:none;cursor:pointer}
.h-btn-primary{background:#76553e;color:#fff}
.h-btn-primary:hover{background:#674633}
.product-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
.stat-card{background:#f1edeb;border-radius:16px;padding:16px}
.stat-card .label{display:block;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#74645d;font-weight:700}
.stat-row{margin-top:12px;display:flex;justify-content:space-between;align-items:center}
.stat-num{font-family:'Noto Serif',serif;font-size:38px;color:#76553e;font-weight:700}
.stat-num.error{color:#ba1a1a}
.chip-tabs{display:flex;flex-wrap:wrap;gap:8px}
.chip-tab{padding:8px 14px;border-radius:999px;background:#ece7e4;color:#645b58;font-size:12px;text-decoration:none;font-weight:600}
.chip-tab.active{background:#916d55;color:#fff}
.filters{display:flex;justify-content:space-between;gap:10px;align-items:center;margin-bottom:14px;flex-wrap:wrap}
.sort-select{border:none;background:#f5f3f1;border-radius:999px;padding:10px 14px}
.product-card{background:#fff;border-radius:24px;overflow:hidden;border:1px solid #efe8e5}
.product-table{width:100%;border-collapse:collapse}
.product-table th{padding:18px 16px;font-size:11px;letter-spacing:1px;text-transform:uppercase;color:#7f716a;background:#f8f5f3}
.product-table td{padding:16px;border-top:1px solid #f1edeb;vertical-align:middle}
.row-img{width:58px;height:58px;border-radius:14px;object-fit:cover;background:#efedec}
.badge{font-size:10px;padding:5px 9px;background:#ece0dc;color:#5a504d;border-radius:999px;font-weight:700}
.price{font-weight:700;color:#533a27}
.table-actions{display:flex;justify-content:flex-end;gap:8px}
.icon-btn{
    width:36px;
    height:36px;
    border-radius:10px;
    border:none;
    background:#f4efec;
    color:#76553e;
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    padding:0;
    line-height:1;
    flex:0 0 36px;
}
.icon-btn.danger{color:#ba1a1a}
.pagination{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;background:#faf8f7;border-top:1px solid #f1edeb;gap:10px;flex-wrap:wrap}
.pages{display:flex;align-items:center;gap:6px}
.p-btn{min-width:32px;height:32px;display:inline-flex;align-items:center;justify-content:center;border-radius:8px;text-decoration:none;background:#f0ece9;color:#6b615e;font-size:13px;font-weight:700}
.p-btn.active{background:#76553e;color:#fff}
.empty{text-align:center;padding:56px 16px;color:#7f716a}
.admin-modal-overlay{position:fixed;inset:0;background:rgba(30,21,16,.45);display:none;align-items:center;justify-content:center;padding:20px;z-index:9999}
.admin-modal{background:#fff;border-radius:18px;max-width:860px;width:100%;max-height:90vh;overflow:auto;padding:18px 18px 14px}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px}
.modal-title{font-family:'Noto Serif',serif;color:#76553e;font-size:24px;margin:0}
.modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.modal-grid .full{grid-column:1/-1}
.modal-input,.modal-select,.modal-textarea{
    width:100%;
    padding:10px 12px;
    border:1px solid #dfd7d4;
    border-radius:10px;
    background:#faf7f5;
    font:inherit;
    box-sizing:border-box;
}
.modal-input,.modal-select{height:44px}
.modal-select{
    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236b615e' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
    padding-right:36px;
}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:12px}
@media (max-width: 740px){.modal-grid{grid-template-columns:1fr}}
@media (max-width: 1080px){.product-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media (max-width: 640px){.product-stats{grid-template-columns:1fr}.h-input{width:100%}}
</style>

<div class="product-layout">
    <?php if (!empty($_GET['delete_error'])): ?>
    <div class="admin-message admin-message-error">Không thể xóa hoặc ẩn sản phẩm. Vui lòng thử lại.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['deleted_hidden'])): ?>
    <div class="admin-message admin-message-success">Sản phẩm đã được ẩn để giữ lịch sử đơn hàng.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['added'])): ?>
    <div class="admin-message admin-message-success">Thêm sản phẩm thành công.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['edited'])): ?>
    <div class="admin-message admin-message-success">Cập nhật sản phẩm thành công.</div>
    <?php endif; ?>
    <?php if (!empty($addError)): ?>
    <div class="admin-message admin-message-error"><?php echo htmlspecialchars($addError); ?></div>
    <?php endif; ?>

    <div class="product-topbar">
        <div>
            <h1 class="admin-page-title">Quản lý sản phẩm</h1>
            <div class="admin-page-subtitle">Danh sách bánh thủ công cao cấp</div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            <form method="get" action="admin_dashboard.php" style="display:inline-flex;align-items:center;margin:0;">
                <input type="hidden" name="page" value="products">
                <?php if ($showInactive): ?><input type="hidden" name="show_inactive" value="1"><?php endif; ?>
                <?php if ($sort !== 'newest'): ?><input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort, ENT_QUOTES, 'UTF-8'); ?>"><?php endif; ?>
                <input class="h-input" type="search" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Tìm kiếm sản phẩm..." autocomplete="off">
            </form>
            <button type="button" class="h-btn h-btn-primary" onclick="openAddProductModal()"><i class="fas fa-plus"></i> Thêm sản phẩm mới</button>
        </div>
    </div>

    <div class="product-stats">
        <div class="stat-card"><span class="label">Tổng sản phẩm</span><div class="stat-row"><span class="stat-num"><?php echo number_format($totalProducts); ?></span></div></div>
        <div class="stat-card"><span class="label">Đang kinh doanh</span><div class="stat-row"><span class="stat-num"><?php echo number_format($activeProducts); ?></span></div></div>
        <div class="stat-card"><span class="label">Hết hàng</span><div class="stat-row"><span class="stat-num error"><?php echo number_format($outOfStockProducts); ?></span></div></div>
        <div class="stat-card"><span class="label">Doanh thu tháng</span><div class="stat-row"><span class="stat-num"><?php echo number_format($monthlyRevenue / 1000000, 1); ?>M</span><span>VNĐ</span></div></div>
    </div>

    <div class="filters">
        <div class="chip-tabs">
            <a class="chip-tab active" href="#">Tất cả</a>
            <a class="chip-tab" href="#">Bánh Kem</a>
            <a class="chip-tab" href="#">Bánh Mì</a>
            <a class="chip-tab" href="#">Macarons</a>
        </div>
        <div>
            <span style="font-size:12px;color:#7f716a;margin-right:8px;">Sắp xếp:</span>
            <select class="sort-select" onchange="if(this.value){window.location.href=this.value;}">
                <option value="admin_dashboard.php?page=products&sort=newest<?php echo $inactiveParam . $searchParam; ?>#products" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="admin_dashboard.php?page=products&sort=sold_desc<?php echo $inactiveParam . $searchParam; ?>#products" <?php echo $sort === 'sold_desc' ? 'selected' : ''; ?>>Đã bán nhiều nhất</option>
                <option value="admin_dashboard.php?page=products&sort=sold_asc<?php echo $inactiveParam . $searchParam; ?>#products" <?php echo $sort === 'sold_asc' ? 'selected' : ''; ?>>Đã bán ít nhất</option>
            </select>
            <?php if ($showInactive): ?>
                <a class="chip-tab" href="admin_dashboard.php?page=products<?php echo $sort !== 'newest' ? $sortParam : ''; ?><?php echo $searchParam; ?>#products" style="margin-left:8px;">← Chỉ sản phẩm đang bán</a>
            <?php else: ?>
                <a class="chip-tab" href="admin_dashboard.php?page=products&show_inactive=1<?php echo $sort !== 'newest' ? $sortParam : ''; ?><?php echo $searchParam; ?>#products" style="margin-left:8px;">Xem sản phẩm đã ẩn</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-card" id="products">
        <div style="overflow-x:auto;">
            <table class="product-table">
                <thead>
                    <tr>
                        <th>ID</th><th>Hình ảnh</th><th>Sản phẩm</th><th>Danh mục</th><th>Giá</th><th>Đã bán</th><th style="text-align:right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr id="row-<?php echo $row['id']; ?>">
                                <td>#SC-<?php echo str_pad((string)$row['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" alt="" class="row-img" onerror="this.src='../images/no-image.png'">
                                    <?php else: ?>
                                        <span class="badge">Không ảnh</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><span class="badge"><?php echo htmlspecialchars($row['category_name'] ?: 'Chưa phân loại'); ?></span></td>
                                <td><span class="price"><?php echo number_format($row['price'], 0, ',', '.'); ?>đ</span></td>
                                <td><?php echo number_format((int)($row['total_sold'] ?? 0)); ?></td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            class="icon-btn"
                                            type="button"
                                            title="Sửa"
                                            onclick="openEditProductModal(this)"
                                            data-id="<?php echo (int)$row['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>"
                                            data-category-id="<?php echo (int)$row['category_id']; ?>"
                                            data-price="<?php echo (int)$row['price']; ?>"
                                            data-short-description="<?php echo htmlspecialchars((string)($row['short_description'] ?? ''), ENT_QUOTES); ?>"
                                            data-description="<?php echo htmlspecialchars((string)($row['description'] ?? ''), ENT_QUOTES); ?>"
                                            data-is-featured="<?php echo (int)$row['is_featured']; ?>"
                                            data-is-active="<?php echo (int)$row['is_active']; ?>"
                                            data-image="<?php echo htmlspecialchars((string)($row['image'] ?? ''), ENT_QUOTES); ?>"
                                        ><i class="fas fa-pen"></i></button>
                                        <button class="icon-btn danger" type="button" onclick="deleteProduct(<?php echo $row['id']; ?>)" title="Xóa"><i class="fas fa-trash"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td class="empty" colspan="7">Không có sản phẩm nào trong danh mục.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <div>Hiển thị <?php echo $result && $result->num_rows ? (($currentPage - 1) * $perPage + 1) : 0; ?> - <?php echo $result ? min($currentPage * $perPage, $totalProducts) : 0; ?> / <?php echo number_format($totalProducts); ?> sản phẩm</div>
                <div class="pages">
                    <?php if ($currentPage > 1): ?>
                        <a class="p-btn" href="admin_dashboard.php?page=products<?php echo $inactiveParam; ?><?php echo $sort !== 'newest' ? $sortParam : ''; ?><?php echo $searchParam; ?>&pg=<?php echo $currentPage - 1; ?>#products"><i class="fas fa-angle-left"></i></a>
                    <?php endif; ?>
                    <?php $startPage = max(1, min($currentPage - 2, $totalPages - 4)); $endPage = min($totalPages, $startPage + 4); ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a class="p-btn <?php echo $i === $currentPage ? 'active' : ''; ?>" href="admin_dashboard.php?page=products<?php echo $inactiveParam; ?><?php echo $sort !== 'newest' ? $sortParam : ''; ?><?php echo $searchParam; ?>&pg=<?php echo $i; ?>#products"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a class="p-btn" href="admin_dashboard.php?page=products<?php echo $inactiveParam; ?><?php echo $sort !== 'newest' ? $sortParam : ''; ?><?php echo $searchParam; ?>&pg=<?php echo $currentPage + 1; ?>#products"><i class="fas fa-angle-right"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="addProductModal" class="admin-modal-overlay" onclick="if(event.target===this){closeAddProductModal();}">
    <div class="admin-modal">
        <div class="modal-header">
            <h2 class="modal-title">Thêm sản phẩm mới</h2>
            <button type="button" class="icon-btn" onclick="closeAddProductModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="add_product" value="1">
            <div class="modal-grid">
                <div>
                    <label>Tên sản phẩm</label>
                    <input class="modal-input" type="text" name="name" required>
                </div>
                <div>
                    <label>Danh mục</label>
                    <select class="modal-select" name="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Giá (VNĐ)</label>
                    <input class="modal-input" type="number" min="0" step="1000" name="price" required>
                </div>
                <div>
                    <label>Ảnh sản phẩm</label>
                    <input class="modal-input" type="file" name="image" accept="image/*" required>
                </div>
                <div class="full">
                    <label>Mô tả ngắn</label>
                    <input class="modal-input" type="text" name="short_description">
                </div>
                <div class="full">
                    <label>Mô tả chi tiết</label>
                    <textarea class="modal-textarea" rows="4" name="description"></textarea>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="h-btn" onclick="closeAddProductModal()">Hủy</button>
                <button type="submit" class="h-btn h-btn-primary">Thêm sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<div id="editProductModal" class="admin-modal-overlay" onclick="if(event.target===this){closeEditProductModal();}">
    <div class="admin-modal">
        <div class="modal-header">
            <h2 class="modal-title">Chỉnh sửa sản phẩm</h2>
            <button type="button" class="icon-btn" onclick="closeEditProductModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="edit_product" value="1">
            <input type="hidden" name="product_id" id="edit-product-id">
            <div class="modal-grid">
                <div>
                    <label>Tên sản phẩm</label>
                    <input class="modal-input" type="text" name="name" id="edit-name" required>
                </div>
                <div>
                    <label>Danh mục</label>
                    <select class="modal-select" name="category_id" id="edit-category-id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label>Giá (VNĐ)</label>
                    <input class="modal-input" type="number" min="0" step="1000" name="price" id="edit-price" required>
                </div>
                <div>
                    <label>Ảnh mới (không bắt buộc)</label>
                    <input class="modal-input" type="file" name="image" accept="image/*">
                    <small id="edit-current-image" style="display:block;margin-top:6px;color:#7f716a;"></small>
                </div>
                <div class="full">
                    <label>Mô tả ngắn</label>
                    <input class="modal-input" type="text" name="short_description" id="edit-short-description">
                </div>
                <div class="full">
                    <label>Mô tả chi tiết</label>
                    <textarea class="modal-textarea" rows="4" name="description" id="edit-description"></textarea>
                </div>
                <div>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_featured" id="edit-is-featured" value="1"> Sản phẩm nổi bật</label>
                </div>
                <div>
                    <label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="is_active" id="edit-is-active" value="1"> Hiển thị trên cửa hàng</label>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="h-btn" onclick="closeEditProductModal()">Hủy</button>
                <button type="submit" class="h-btn h-btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddProductModal(){ document.getElementById('addProductModal').style.display='flex'; }
function closeAddProductModal(){ document.getElementById('addProductModal').style.display='none'; }
function openEditProductModal(btn){
    document.getElementById('edit-product-id').value = btn.dataset.id || '';
    document.getElementById('edit-name').value = btn.dataset.name || '';
    document.getElementById('edit-category-id').value = btn.dataset.categoryId || '';
    document.getElementById('edit-price').value = btn.dataset.price || '';
    document.getElementById('edit-short-description').value = btn.dataset.shortDescription || '';
    document.getElementById('edit-description').value = btn.dataset.description || '';
    document.getElementById('edit-is-featured').checked = (btn.dataset.isFeatured === '1');
    document.getElementById('edit-is-active').checked = (btn.dataset.isActive === '1');
    document.getElementById('edit-current-image').textContent = btn.dataset.image ? ('Ảnh hiện tại: ' + btn.dataset.image) : 'Ảnh hiện tại: (không có)';
    document.getElementById('editProductModal').style.display='flex';
}
function closeEditProductModal(){ document.getElementById('editProductModal').style.display='none'; }
function deleteProduct(id) {
    if (!confirm('Xóa sản phẩm này?\n\nNếu sản phẩm đã từng có trong đơn hàng, hệ thống chỉ ẩn khỏi cửa hàng (không xóa vĩnh viễn) để giữ lịch sử đơn.')) return;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'manage_products.php?delete_id=' + encodeURIComponent(id), true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function() {
        if (xhr.status !== 200) return alert('Lỗi kết nối server. Vui lòng thử lại.');
        try {
            var response = JSON.parse(xhr.responseText);
            if (!response.success) return alert(response.error || 'Lỗi khi xóa sản phẩm.');
            if (response.hidden && response.message) alert(response.message);
            var row = document.getElementById('row-' + id);
            if (!row) return location.reload();
            row.style.transition = 'all .25s ease';
            row.style.opacity = '0';
            setTimeout(function(){ row.remove(); location.reload(); }, 250);
        } catch (e) {
            alert('Lỗi xử lý dữ liệu từ server.');
        }
    };
    xhr.onerror = function() { alert('Không thể kết nối đến server.'); };
    xhr.send();
}
</script>