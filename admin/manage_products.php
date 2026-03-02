<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}


require_once 'connect.php';

// Xử lý xóa sản phẩm (nếu có) - Bây giờ hỗ trợ AJAX và renumber ID
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        // Renumber ID sau xóa (reset thứ tự liên tục)
        $conn->query("SET @row_number = 0;");
        $conn->query("UPDATE products SET id = (@row_number := @row_number + 1) ORDER BY created_at DESC;"); // Đổi ORDER BY p.id ASC nếu muốn sắp xếp tăng dần
        
        // Set AUTO_INCREMENT = max(id) + 1 để ID mới đúng
        $max_result = $conn->query("SELECT MAX(id) AS max_id FROM products");
        $max_id = $max_result ? $max_result->fetch_assoc()['max_id'] : 0;
        $conn->query("ALTER TABLE products AUTO_INCREMENT = " . ($max_id + 1) . ";");

        // Nếu là AJAX request, trả JSON thay vì redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true]);
            exit();
        } else {
            header('Location: admin_dashboard.php?page=products');
            exit();
        }
    } else {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => false, 'error' => 'Lỗi khi xóa sản phẩm.']);
            exit();
        }
    }
    $stmt->close();
}

// Lấy danh sách sản phẩm với tên danh mục
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC"; // Hoặc ORDER BY p.id ASC nếu muốn ID tăng dần
$result = $conn->query($sql);
?>
<div class="admin-content">
<h1 class="admin-page-title"><i class="fas fa-cake-candles"></i> Quản lý sản phẩm</h1>
<p style="margin-bottom:16px;"><a href="upload_image.php" class="admin-btn admin-btn-primary">Thêm sản phẩm mới</a></p>
<div class="admin-card">
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Hình ảnh</th>
            <th>Thao tác</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td><?php echo $row['id'] ?></td>
                    <td><?php echo htmlspecialchars($row['name']) ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']) ?></td>
                    <td><?php echo number_format($row['price'], 0, ',', '.') ?>₫</td>
                    <td><img src="../images/<?php echo htmlspecialchars($row['image']) ?>" alt="" style="height:50px;"></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="admin-link">Chỉnh sửa</a>
                        <span style="color:#d32f2f; cursor:pointer; margin-left:10px;" onclick="deleteProduct(<?php echo $row['id']; ?>)">Xóa</span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Không có sản phẩm nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</div>

<script>
function deleteProduct(id) {
    if (confirm('Bạn có chắc muốn xóa sản phẩm này?')) {
        // Gửi AJAX request
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'manage_products.php?delete_id=' + id, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); // Để PHP biết là AJAX
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.success) {
                    document.getElementById('row-' + id).remove();
                    location.reload(); 
                } else {
                    alert(response.error || 'Lỗi khi xóa.');
                }
            } else {
                alert('Lỗi kết nối server.');
            }
        };
        xhr.send();
    }
}
</script>