<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}

require_once 'connect.php';

$error = '';
$success = '';

// Lấy danh mục để chọn
$category_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    $description = trim($_POST['description']);
    $short_description = trim($_POST['short_description']);
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    
    // Xử lý upload file hình
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $file_name = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            $error = "Chỉ chấp nhận định dạng ảnh: jpg, jpeg, png, gif, webp";
        } else {
            $new_file_name = uniqid() . '.' . $ext;
            $target_dir = realpath(__DIR__ . '/../images') . '/';
            $target_file = $target_dir . $new_file_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Thêm sản phẩm vào DB
                $stmt = $conn->prepare("INSERT INTO products (name, price, image, description, short_description, category_id, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sisssii', $name, $price, $new_file_name, $description, $short_description, $category_id, $stock);
                if ($stmt->execute()) {
                    $success = "Thêm sản phẩm thành công!";
                } else {
                    $error = "Lỗi khi thêm sản phẩm.";
                }
                $stmt->close();
            } else {
                $error = "Lỗi khi upload ảnh.";
            }
        }
    } else {
        $error = "Vui lòng chọn ảnh sản phẩm.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Thêm sản phẩm mới - Savor Cake</title>
<style>
    body { font-family: 'Poppins', sans-serif; background: #fffaf0; margin:0; padding:20px;}
    h1 { color: #8B6F47; }
    form { background: white; padding: 20px; border-radius: 8px; width: 450px; }
    label { display: block; margin-top: 12px; font-weight: 600; }
    input[type=text], input[type=number], textarea, select {
        width: 100%; padding: 8px; margin-top: 6px; border-radius: 6px; border: 1px solid #ddd; font-size: 14px;
    }
    textarea { resize: vertical; }
    input[type=submit] {
        margin-top: 20px; background: #8B6F47; color: white; border: none; padding: 12px 20px; border-radius: 10px;
        cursor: pointer; font-weight: 600; font-size: 16px;
    }
    input[type=submit]:hover {
        background: #ff90c2;
    }
    .message { margin-top: 15px; padding: 10px; border-radius: 8px; }
    .error { background: #f8d7da; color: #721c24; }
    .success { background: #d4edda; color: #155724; }
</style>
</head>
<body>
<h1>Thêm sản phẩm mới</h1>

<?php if ($error): ?>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="message success"><?php echo htmlspecialchars($success); ?></div>
<?php endif; ?>

<form action="" method="post" enctype="multipart/form-data">
    <label for="name">Tên sản phẩm</label>
    <input type="text" name="name" id="name" required>

    <label for="price">Giá (VNĐ)</label>
    <input type="number" name="price" id="price" min="0" required>

    <label for="category_id">Danh mục</label>
    <select name="category_id" id="category_id" required>
        <option value="">-- Chọn danh mục --</option>
        <?php while($cat = $category_result->fetch_assoc()): ?>
            <option value="<?php echo $cat['id'] ?>"><?php echo htmlspecialchars($cat['name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label for="stock">Số lượng kho</label>
    <input type="number" name="stock" id="stock" min="0" value="100" required>

    <label for="short_description">Mô tả ngắn</label>
    <input type="text" name="short_description" id="short_description">

    <label for="description">Mô tả chi tiết</label>
    <textarea name="description" id="description" rows="5"></textarea>

    <label for="image">Ảnh sản phẩm</label>
    <input type="file" name="image" id="image" accept="image/*" required>

    <input type="submit" value="Thêm sản phẩm">
</form>
</body>
</html>
