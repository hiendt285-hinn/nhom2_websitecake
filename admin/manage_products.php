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
            header('Location: manage_products.php');
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
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Quản lý sản phẩm - Savor Cake</title>
<style>
    body { font-family: 'Open Sans', sans-serif; background: #F5F1E8; margin:0; padding:20px;}
    h1 { color: #8B6F47; margin-bottom:16px; }
    table { width: 100%; border-collapse: collapse; background: white; border-radius:8px; overflow:hidden; }
    th, td { border-bottom: 1px solid #eee; padding: 10px 12px; text-align: left; font-size:14px; }
    th { background: #F5F1E8; color: #333; font-weight:600; }
    a { color: #8B6F47; text-decoration: none; font-weight: 600; }
    a:hover { color: #A0826D; }
    .btn-delete { color: #d32f2f; cursor: pointer; margin-left: 10px; }
    .btn-edit { color: #8B6F47; text-decoration: none; font-weight: 600; }
</style>
</head>
<body>
<h1>Quản lý sản phẩm</h1>
<p><a href="upload_image.php">Thêm sản phẩm mới</a></p>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Hình ảnh</th>
            <th>Số lượng kho</th>
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
                    <td><?php echo $row['stock'] ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn-edit">Chỉnh sửa</a>
                        <span class="btn-delete" onclick="deleteProduct(<?php echo $row['id']; ?>)">Xóa</span>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">Không có sản phẩm nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

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
</body>
</html>