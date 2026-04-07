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

// Xử lý xóa sản phẩm (nếu có)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Kiểm tra sản phẩm có trong đơn hàng chưa giao không (chỉ chặn xóa khi còn đơn pending/processing/shipping...)
    $checkPending = $conn->prepare("
        SELECT COUNT(*) AS cnt FROM order_items oi
        INNER JOIN orders o ON o.id = oi.order_id
        WHERE oi.product_id = ? AND o.status NOT IN ('completed', 'delivered', 'cancelled')
    ");
    $checkPending->bind_param('i', $delete_id);
    $checkPending->execute();
    $inPendingOrders = (int) $checkPending->get_result()->fetch_assoc()['cnt'];
    $checkPending->close();

    if ($inPendingOrders > 0) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'error' => 'Không thể xóa sản phẩm đang có trong đơn hàng chưa giao. Bạn có thể ẩn sản phẩm (tắt Hiển thị) hoặc đợi đơn giao xong.']);
            exit();
        }
        header('Location: admin_dashboard.php?page=products&delete_error=1');
        exit();
    }

    // Kiểm tra sản phẩm có trong đơn đã giao hoặc không có trong đơn nào
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
        // Có trong đơn đã giao → chỉ ẩn sản phẩm 
        $stmt = $conn->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
        $stmt->bind_param('i', $delete_id);
        if ($stmt->execute()) {
            $stmt->close();
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'hidden' => true]);
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

// Phân trang: 20 sản phẩm / trang
$perPage = 20;
$currentPage = max(1, isset($_GET['pg']) ? (int)$_GET['pg'] : 1);
$countResult = $conn->query("SELECT COUNT(*) AS total FROM products");
$totalProducts = $countResult ? (int)$countResult->fetch_assoc()['total'] : 0;
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

// Lấy danh sách sản phẩm với tên danh mục và tổng đã bán 
$sql = "SELECT p.*, c.name AS category_name,
        COALESCE(SUM(CASE WHEN o.status IN ('completed', 'delivered') THEN oi.quantity ELSE 0 END), 0) AS total_sold
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN order_items oi ON oi.product_id = p.id
        LEFT JOIN orders o ON o.id = oi.order_id
        GROUP BY p.id, c.name
        ORDER BY " . $orderBy . "
        LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
$result = $conn->query($sql);
if ($result === false) {
    $fallbackSql = "SELECT p.*, c.name AS category_name, 0 AS total_sold
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.id DESC
            LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
    $result = $conn->query($fallbackSql);
}
?>
<?php
$activeProducts = 0;
$outOfStockProducts = 0;
$monthlyRevenue = 0;
$activeResult = $conn->query("SELECT COUNT(*) AS total FROM products");
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
$categoryResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
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
    <div class="admin-message admin-message-error">Không thể xóa sản phẩm đang có trong đơn hàng chưa giao. Bạn có thể ẩn sản phẩm hoặc đợi đơn giao xong.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['deleted_hidden'])): ?>
    <div class="admin-message admin-message-success">Sản phẩm đã được ẩn để giữ lịch sử đơn hàng.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['added'])): ?>
    <div class="admin-message admin-message-success">Thêm sản phẩm thành công.</div>
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
            <input class="h-input" type="text" placeholder="Tìm kiếm sản phẩm..." />
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
                <option value="admin_dashboard.php?page=products&sort=newest#products" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Mới nhất</option>
                <option value="admin_dashboard.php?page=products&sort=sold_desc#products" <?php echo $sort === 'sold_desc' ? 'selected' : ''; ?>>Đã bán nhiều nhất</option>
                <option value="admin_dashboard.php?page=products&sort=sold_asc#products" <?php echo $sort === 'sold_asc' ? 'selected' : ''; ?>>Đã bán ít nhất</option>
            </select>
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
                                        <a class="icon-btn" href="edit_product.php?id=<?php echo $row['id']; ?>" title="Sửa"><i class="fas fa-pen"></i></a>
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
                        <a class="p-btn" href="admin_dashboard.php?page=products<?php echo $sort !== 'newest' ? $sortParam : ''; ?>&pg=<?php echo $currentPage - 1; ?>#products"><i class="fas fa-angle-left"></i></a>
                    <?php endif; ?>
                    <?php $startPage = max(1, min($currentPage - 2, $totalPages - 4)); $endPage = min($totalPages, $startPage + 4); ?>
                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a class="p-btn <?php echo $i === $currentPage ? 'active' : ''; ?>" href="admin_dashboard.php?page=products<?php echo $sort !== 'newest' ? $sortParam : ''; ?>&pg=<?php echo $i; ?>#products"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a class="p-btn" href="admin_dashboard.php?page=products<?php echo $sort !== 'newest' ? $sortParam : ''; ?>&pg=<?php echo $currentPage + 1; ?>#products"><i class="fas fa-angle-right"></i></a>
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
                        <?php if ($categoryResult): while ($cat = $categoryResult->fetch_assoc()): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endwhile; endif; ?>
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

<script>
function openAddProductModal(){ document.getElementById('addProductModal').style.display='flex'; }
function closeAddProductModal(){ document.getElementById('addProductModal').style.display='none'; }
function deleteProduct(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')) return;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'manage_products.php?delete_id=' + id, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onload = function() {
        if (xhr.status !== 200) return alert('Lỗi kết nối server. Vui lòng thử lại.');
        try {
            var response = JSON.parse(xhr.responseText);
            if (!response.success) return alert(response.error || 'Lỗi khi xóa sản phẩm.');
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