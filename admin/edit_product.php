<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}

include 'admin_header.php';
require_once 'connect.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$categories = [];
$error_message = '';
$success_message = '';


$category_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($category_result) {
    while($row = $category_result->fetch_assoc()) {
        $categories[] = $row;
    }
}


if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $error_message = 'Không tìm thấy sản phẩm.';
    }
    $stmt->close();
} else {
    $error_message = 'ID sản phẩm không hợp lệ.';
}



if ($_SERVER['REQUEST_METHOD'] == 'POST' && $product_id > 0) {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category_id = intval($_POST['category_id'] ?? 0);
    $current_image = $product['image']; // Giữ ảnh cũ

    if (empty($name) || $price <= 0 || $category_id <= 0) {
        $error_message = 'Vui lòng điền đầy đủ Tên sản phẩm, Giá và chọn Danh mục.';
    } else {
        $new_image = $current_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../images/";
            $image_file = basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image_file;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $new_image = $image_file;
            } else {
                $error_message = 'Lỗi khi upload ảnh mới.';
            }
        }

        if (empty($error_message)) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, stock=?, image=? WHERE id=?");
            $stmt->bind_param('siidis', $name, $category_id, $price, $stock, $new_image, $product_id);
            
            if ($stmt->execute()) {
                $success_message = 'Cập nhật sản phẩm thành công!';
                header("Location: manage_products.php"); 
                exit();
            } else {
                $error_message = 'Lỗi khi cập nhật vào database: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<title>Chỉnh sửa sản phẩm - Savor Cake</title>
<style>
    body { font-family: 'Poppins', sans-serif; background: #fffaf0; margin:0; padding:20px;}
    h1 { color: #5a8b56; }
    form { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    label { display: block; margin-top: 10px; font-weight: 600; color: #5a8b56; }
    input[type="text"], input[type="number"], select { width: calc(100% - 22px); padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    input[type="submit"] { background-color: #ff5f9e; color: white; padding: 12px 20px; margin-top: 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
    input[type="submit"]:hover { background-color: #ff90c2; }
    .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
    .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
</style>
</head>
<body>
<h1>Chỉnh sửa sản phẩm: <?php echo htmlspecialchars($product['name'] ?? 'Không tìm thấy'); ?></h1>

<?php if ($error_message): ?>
    <div class="message error"><?php echo $error_message; ?></div>
<?php endif; ?>
<?php if ($success_message): ?>
    <div class="message success"><?php echo $success_message; ?></div>
<?php endif; ?>

<?php if ($product): ?>
<form method="POST" action="edit_product.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data">
    
    <label for="name">Tên sản phẩm:</label>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

    <label for="category_id">Danh mục:</label>
    <select id="category_id" name="category_id" required>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $product['category_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="price">Giá (₫):</label>
    <input type="number" id="price" name="price" step="1000" min="0" value="<?php echo $product['price']; ?>" required>

    <label for="stock">Số lượng kho:</label>
    <input type="number" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>

    <label>Hình ảnh hiện tại:</label><br>
    <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" alt="Hình ảnh sản phẩm" style="height: 100px; margin-top: 10px;">
    
    <label for="image">Thay đổi Hình ảnh (Tùy chọn):</label>
    <input type="file" id="image" name="image" accept="image/*">

    <input type="submit" value="Cập nhật sản phẩm">
</form>
<?php endif; ?>

<p><a href="manage_products.php">Quay lại Quản lý sản phẩm</a></p>

</body>
</html>