<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}


require_once 'connect.php';

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
$orderBy = 'p.created_at DESC';
if ($sort === 'sold_desc') {
    $orderBy = 'total_sold DESC, p.created_at DESC';
} elseif ($sort === 'sold_asc') {
    $orderBy = 'total_sold ASC, p.created_at DESC';
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
?>

<style>
/* Đồng bộ button với các trang quản lý khác */
.admin-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 100px;
    height: 38px;
    white-space: nowrap;
}

.admin-btn i {
    font-size: 14px;
}

.admin-btn-sm {
    min-width: 90px;
    height: 36px;
    padding: 6px 12px;
    font-size: 13px;
}

.admin-btn-primary {
    background: #9a7b5a;
    color: white;
}

.admin-btn-primary:hover {
    background: #A0522D;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139,69,19,0.2);
}

.admin-btn-secondary {
    background: #95a5a6;
    color: white;
}

.admin-btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

.admin-btn-danger {
    background: #e74c3c;
    color: white;
}

.admin-btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

/* Action Cell */
.admin-action-cell {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    min-width: 200px;
}

/* Product Image */
.product-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #f0f0f0;
    transition: all 0.3s ease;
}

.product-img:hover {
    transform: scale(1.1);
    border-color: #9a7b5a;
}

/* Page Header */
.admin-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.admin-page-title {
    font-size: 24px;
    color: #9a7b5a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Card */
.admin-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 20px;
    overflow-x: auto;
}

/* Table */
.admin-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1000px;
}

.admin-table th {
    background: #9a7b5a;
    color: white;
    font-weight: 600;
    padding: 15px 12px;
    font-size: 14px;
    text-align: left;
    white-space: nowrap;
}

.admin-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 14px;
    vertical-align: middle;
}

.admin-table tbody tr:hover td {
    background: #f9f6f2;
}

/* Pagination */
.admin-pagination {
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    padding: 15px 0;
    border-top: 2px solid #f0f0f0;
}

.pagination-info {
    font-size: 13px;
    color: #7f8c8d;
    display: flex;
    align-items: center;
    gap: 5px;
}

.pagination-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

/* Category Badge */
.category-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    background: #f0f0f0;
    color: #555;
    font-size: 12px;
    font-weight: 500;
}

/* Price */
.price {
    font-weight: 600;
    color: #8B4513;
    font-size: 15px;
}

.sold-count {
    display: inline-block;
    padding: 4px 8px;
    background: #e8f5e9;
    color: #27ae60;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
}

/* Responsive */
@media (max-width: 992px) {
    .admin-action-cell {
        flex-direction: column;
        min-width: auto;
    }
    
    .admin-btn-sm {
        width: 100%;
        min-width: 100%;
    }
    
    .pagination-buttons {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .admin-page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-pagination {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="admin-content">
    <?php if (!empty($_GET['delete_error'])): ?>
    <div class="admin-message admin-message-error">Không thể xóa sản phẩm đang có trong đơn hàng chưa giao. Bạn có thể ẩn sản phẩm (tắt Hiển thị) hoặc đợi đơn giao xong.</div>
    <?php endif; ?>
    <?php if (!empty($_GET['deleted_hidden'])): ?>
    <div class="admin-message admin-message-success">Sản phẩm đã được ẩn (vì có trong đơn đã giao, hệ thống giữ lại để lưu lịch sử đơn hàng).</div>
    <?php endif; ?>
    <div class="admin-page-header">
        <h1 class="admin-page-title">
            <i class="fas fa-cake-candles"></i> 
            Quản lý sản phẩm
        </h1>
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
            <span class="pagination-info">
                <i class="fas fa-box"></i>
                Tổng <?php echo number_format($totalProducts); ?> sản phẩm — 
                Trang <?php echo $currentPage; ?>/<?php echo $totalPages; ?>
            </span>
            <a href="upload_image.php" class="admin-btn admin-btn-primary">
                <i class="fas fa-plus-circle"></i> Thêm sản phẩm mới
            </a>
        </div>
    </div>
    
    <!-- Bộ lọc sắp xếp -->
    <div class="admin-filter-bar" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
        <span style="font-size:13px; color:#666;">Sắp xếp:</span>
        <a href="admin_dashboard.php?page=products<?php echo $sort === 'newest' ? '' : '&sort=newest'; ?>#products" 
           class="admin-btn <?php echo $sort === 'newest' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?> admin-btn-sm">
            <i class="fas fa-clock"></i> Mới nhất
        </a>
        <a href="admin_dashboard.php?page=products&sort=sold_desc#products" 
           class="admin-btn <?php echo $sort === 'sold_desc' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?> admin-btn-sm">
            <i class="fas fa-sort-amount-down-alt"></i> Đã bán nhiều nhất
        </a>
        <a href="admin_dashboard.php?page=products&sort=sold_asc#products" 
           class="admin-btn <?php echo $sort === 'sold_asc' ? 'admin-btn-primary' : 'admin-btn-secondary'; ?> admin-btn-sm">
            <i class="fas fa-sort-amount-up"></i> Đã bán ít nhất
        </a>
    </div>
    
    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Đã bán</th>
                    <th>Hình ảnh</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr id="row-<?php echo $row['id']; ?>">
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($row['name']); ?></span>
                            </td>
                            <td>
                                <span class="category-badge">
                                    <i class="fas fa-tag" style="color: #8B4513;"></i>
                                    <?php echo htmlspecialchars($row['category_name'] ?: 'Chưa phân loại'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="price">
                                    <i class="fas fa-dollar-sign" style="font-size: 12px;"></i>
                                    <?php echo number_format($row['price'], 0, ',', '.'); ?>₫
                                </span>
                            </td>
                            <td>
                                <span class="sold-count">
                                    <i class="fas fa-chart-line"></i>
                                    <?php echo number_format((int)($row['total_sold'] ?? 0)); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['image'])): ?>
                                    <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" 
                                         alt="" 
                                         class="product-img"
                                         onerror="this.src='../images/no-image.png'">
                                <?php else: ?>
                                    <span class="category-badge">Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td class="admin-action-cell">
                                <!-- Button Sửa - fixed size -->
                                <a href="edit_product.php?id=<?php echo $row['id']; ?>" 
                                   class="admin-btn admin-btn-primary admin-btn-sm"
                                   title="Chỉnh sửa sản phẩm">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                
                                <!-- Button Xóa - fixed size -->
                                <button type="button" 
                                        class="admin-btn admin-btn-danger admin-btn-sm" 
                                        onclick="deleteProduct(<?php echo $row['id']; ?>)"
                                        title="Xóa sản phẩm">
                                    <i class="fas fa-trash-alt"></i> Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 60px 20px;">
                            <i class="fas fa-box-open" style="font-size: 48px; color: #ddd; margin-bottom: 15px; display: block;"></i>
                            <p style="color: #7f8c8d; font-size: 16px; margin: 0;">Không có sản phẩm nào trong danh mục.</p>
                            <a href="upload_image.php" class="admin-btn admin-btn-primary" style="margin-top: 20px;">
                                <i class="fas fa-plus-circle"></i> Thêm sản phẩm đầu tiên
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="admin-pagination">
                <div class="pagination-info">
                    <i class="fas fa-eye"></i>
                    Hiển thị 
                    <strong><?php echo $result && $result->num_rows ? (($currentPage - 1) * $perPage + 1) : 0; ?></strong>
                    – 
                    <strong><?php echo $result ? min($currentPage * $perPage, $totalProducts) : 0; ?></strong>
                    / <strong><?php echo number_format($totalProducts); ?></strong> sản phẩm
                </div>
                
                <div class="pagination-buttons">
                    <?php if ($currentPage > 1): ?>
                        <a href="admin_dashboard.php?page=products<?php echo $sort !== 'newest' ? $sortParam : ''; ?>&pg=<?php echo $currentPage - 1; ?>#products" 
                           class="admin-btn admin-btn-secondary admin-btn-sm"
                           title="Trang trước">
                            <i class="fas fa-chevron-left"></i> Trước
                        </a>
                    <?php endif; ?>

                    <?php
                    // Hiển thị tối đa 5 trang
                    $startPage = max(1, min($currentPage - 2, $totalPages - 4));
                    $endPage = min($totalPages, $startPage + 4);
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                        if ($i == $currentPage): ?>
                            <span class="admin-btn admin-btn-primary admin-btn-sm" 
                                  style="pointer-events:none; min-width: 40px;">
                                <?php echo $i; ?>
                            </span>
                        <?php else: ?>
                            <a href="admin_dashboard.php?page=products<?php echo $sort !== 'newest' ? $sortParam : ''; ?>&pg=<?php echo $i; ?>#products" 
                               class="admin-btn admin-btn-secondary admin-btn-sm"
                               style="min-width: 40px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endif;
                    endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="admin_dashboard.php?page=products<?php echo $sort !== 'newest' ? $sortParam : ''; ?>&pg=<?php echo $currentPage + 1; ?>#products" 
                           class="admin-btn admin-btn-secondary admin-btn-sm"
                           title="Trang sau">
                            Sau <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteProduct(id) {
    if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này? Hành động này không thể hoàn tác.')) {
        // Gửi AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'manage_products.php?delete_id=' + id, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Xóa dòng thành công
                        var row = document.getElementById('row-' + id);
                        if (row) {
                            row.style.transition = 'all 0.3s ease';
                            row.style.opacity = '0';
                            setTimeout(function() {
                                row.remove();
                                // Reload lại trang để cập nhật số thứ tự ID
                                location.reload();
                            }, 300);
                        }
                    } else {
                        alert(response.error || 'Lỗi khi xóa sản phẩm.');
                    }
                } catch(e) {
                    alert('Lỗi xử lý dữ liệu từ server.');
                }
            } else {
                alert('Lỗi kết nối server. Vui lòng thử lại.');
            }
        };
        
        xhr.onerror = function() {
            alert('Không thể kết nối đến server.');
        };
        
        xhr.send();
    }
}

// Thêm hiệu ứng fade out khi xóa
document.addEventListener('DOMContentLoaded', function() {
    // Có thể thêm các hiệu ứng khác nếu cần
});
</script>