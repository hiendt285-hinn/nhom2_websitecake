<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}

require_once 'connect.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$product = null;
$categories = [];
$errorMessage = '';
$successMessage = '';

$categoryResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($categoryResult) {
    while ($row = $categoryResult->fetch_assoc()) {
        $categories[] = $row;
    }
}

if ($productId > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $errorMessage = 'Không tìm thấy sản phẩm.';
    }
    $stmt->close();
} else {
    $errorMessage = 'ID sản phẩm không hợp lệ.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $productId > 0 && $product) {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $categoryId = intval($_POST['category_id'] ?? 0);
    $shortDescription = trim($_POST['short_description'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 100;
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $currentImage = $product['image'];

    if (empty($name) || $price <= 0 || $categoryId <= 0) {
        $errorMessage = 'Vui lòng điền đầy đủ Tên sản phẩm, Giá và chọn Danh mục.';
    } else {
        $newImage = $currentImage;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $fileName = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            if (in_array($ext, $allowedExt)) {
                $newFileName = uniqid() . '.' . $ext;
                $targetDir = realpath(__DIR__ . '/../images') . '/';
                $targetFile = $targetDir . $newFileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $newImage = $newFileName;
                } else {
                    $errorMessage = 'Lỗi khi upload ảnh mới.';
                }
            } else {
                $errorMessage = 'Chỉ chấp nhận ảnh: jpg, jpeg, png, gif, webp.';
            }
        }

        if (empty($errorMessage)) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, image=?, short_description=?, description=?, stock=?, is_featured=?, is_active=? WHERE id=?");
            $stmt->bind_param('sidsissiii', $name, $categoryId, $price, $newImage, $shortDescription, $description, $stock, $isFeatured, $isActive, $productId);
            if ($stmt->execute()) {
                $product['name'] = $name;
                $product['category_id'] = $categoryId;
                $product['price'] = $price;
                $product['image'] = $newImage;
                $product['short_description'] = $shortDescription;
                $product['description'] = $description;
                $product['stock'] = $stock;
                $product['is_featured'] = $isFeatured;
                $product['is_active'] = $isActive;
                $successMessage = 'Cập nhật sản phẩm thành công!';
            } else {
                $errorMessage = 'Lỗi khi cập nhật: ' . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Chỉnh sửa sản phẩm - Sweet Cake</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
<style>
    .product-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) { .product-form-grid { grid-template-columns: 1fr; } }
    .product-form-actions { display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ecf0f1; }
    .image-current-wrap { margin-top: 8px; }
    .image-current-wrap img { max-width: 200px; max-height: 180px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd; }
    .image-preview-wrap { margin-top: 8px; }
    .image-preview-wrap img { max-width: 200px; max-height: 180px; object-fit: contain; border-radius: 8px; border: 1px solid #ddd; }
    .form-row-2 { grid-column: 1 / -1; }
    .admin-form-group input[type="checkbox"] { width: auto; margin-right: 8px; }
    .checkbox-label { display: flex; align-items: center; gap: 8px; font-weight: 500; cursor: pointer; }
    body { background: #f5f5f5; padding: 20px 30px; }
</style>
</head>
<body>
<?php include 'admin_header.php'; ?>
<div class="admin-content" style="max-width: 900px; margin: 0 auto; padding: 20px 24px;">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-edit"></i> Chỉnh sửa sản phẩm</h1>
        <a href="admin_dashboard.php?page=products" class="admin-btn admin-btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <?php if ($errorMessage): ?>
        <div class="admin-message error"><?php echo htmlspecialchars($errorMessage); ?></div>
    <?php endif; ?>
    <?php if ($successMessage): ?>
        <div class="admin-message success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

    <?php if ($product): ?>
    <div class="admin-card">
        <form method="post" action="edit_product.php?id=<?php echo $productId; ?>" enctype="multipart/form-data" class="admin-add-form">
            <div class="product-form-grid">
                <div class="admin-form-group">
                    <label for="name">Tên sản phẩm <span style="color:#c62828;">*</span></label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required placeholder="Nhập tên sản phẩm">
                </div>
                <div class="admin-form-group">
                    <label for="category_id">Danh mục <span style="color:#c62828;">*</span></label>
                    <select id="category_id" name="category_id" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" <?php echo ((int)$cat['id'] === (int)($product['category_id'] ?? 0)) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label for="price">Giá (VNĐ) <span style="color:#c62828;">*</span></label>
                    <input type="number" id="price" name="price" step="1000" min="0" value="<?php echo (int)$product['price']; ?>" required>
                </div>
                <div class="admin-form-group">
                    <label for="stock">Tồn kho</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo (int)($product['stock'] ?? 100); ?>">
                </div>
                <div class="admin-form-group form-row-2">
                    <label for="short_description">Mô tả ngắn</label>
                    <input type="text" id="short_description" name="short_description" value="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>" placeholder="Mô tả ngắn hiển thị trên danh sách">
                </div>
                <div class="admin-form-group form-row-2">
                    <label for="description">Mô tả chi tiết</label>
                    <textarea id="description" name="description" rows="4" placeholder="Mô tả chi tiết sản phẩm"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
                <div class="admin-form-group">
                    <label>Hình ảnh hiện tại</label>
                    <div class="image-current-wrap">
                        <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" alt="Ảnh sản phẩm" id="currentImage">
                    </div>
                    <label for="image" style="margin-top:12px;display:block;">Thay ảnh (tùy chọn)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div class="image-preview-wrap" id="imagePreviewWrap" style="display:none;"><img id="imagePreview" src="" alt="Preview"></div>
                </div>
                <div class="admin-form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" <?php echo !empty($product['is_featured']) ? 'checked' : ''; ?>>
                        Sản phẩm nổi bật
                    </label>
                    <label class="checkbox-label" style="margin-top: 10px;">
                        <input type="checkbox" name="is_active" value="1" <?php echo !isset($product['is_active']) || $product['is_active'] ? 'checked' : ''; ?>>
                        Hiển thị (đang bán)
                    </label>
                </div>
            </div>
            <div class="product-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Cập nhật sản phẩm</button>
                <a href="admin_dashboard.php?page=products" class="admin-btn admin-btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
<?php if ($product): ?>
<script>
document.getElementById('image').addEventListener('change', function(e) {
    var wrap = document.getElementById('imagePreviewWrap');
    var img = document.getElementById('imagePreview');
    if (this.files && this.files[0]) {
        var r = new FileReader();
        r.onload = function() { img.src = r.result; wrap.style.display = 'block'; };
        r.readAsDataURL(this.files[0]);
    } else { wrap.style.display = 'none'; }
});
</script>
<?php endif; ?>
</body>
</html>
