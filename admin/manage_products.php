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
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $stmt->close();
    header('Location: manage_products.php');
    exit();
}

// Lấy danh sách sản phẩm với tên danh mục
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Quản lý sản phẩm - Savor Cake</title>
<style>
    body { font-family: 'Poppins', sans-serif; background: #fffaf0; margin:0; padding:20px;}
    h1 { color: #5a8b56; }
    table { width: 100%; border-collapse: collapse; background: white; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
    th { background: #ff5f9e; color: white; }
    a { color: #ff5f9e; text-decoration: none; font-weight: 600; }
    a:hover { color: #ff90c2; }
    .btn-delete { color: red; }
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
                <tr>
                    <td><?php echo $row['id'] ?></td>
                    <td><?php echo htmlspecialchars($row['name']) ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']) ?></td>
                    <td><?php echo number_format($row['price'], 0, ',', '.') ?>₫</td>
                    <td><img src="../images/<?php echo htmlspecialchars($row['image']) ?>" alt="" style="height:50px;"></td>
                    <td><?php echo $row['stock'] ?></td>
                    <td>
                        <a href="manage_products.php?delete_id=<?php echo $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');" class="btn-delete">Xóa</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="7">Không có sản phẩm nào.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
</body>
</html>
