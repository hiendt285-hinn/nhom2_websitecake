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
                    // Xóa ảnh cũ nếu có
                    if ($currentImage && file_exists($targetDir . $currentImage)) {
                        unlink($targetDir . $currentImage);
                    }
                    $newImage = $newFileName;
                } else {
                    $errorMessage = 'Lỗi khi upload ảnh mới.';
                }
            } else {
                $errorMessage = 'Chỉ chấp nhận ảnh: jpg, jpeg, png, gif, webp.';
            }
        }

        if (empty($errorMessage)) {
            $stmt = $conn->prepare("UPDATE products SET name=?, category_id=?, price=?, image=?, short_description=?, description=?, is_featured=?, is_active=? WHERE id=?");
            $stmt->bind_param('sidsissii', $name, $categoryId, $price, $newImage, $shortDescription, $description, $isFeatured, $isActive, $productId);
            if ($stmt->execute()) {
                $product['name'] = $name;
                $product['category_id'] = $categoryId;
                $product['price'] = $price;
                $product['image'] = $newImage;
                $product['short_description'] = $shortDescription;
                $product['description'] = $description;
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
        /* Reset và variables */
        :root {
            --primary-color: #8B4513;
            --primary-light: #A0522D;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --border-color: #e0e0e0;
            --bg-light: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f5f5f5;
        }

        /* Layout */
        .admin-content {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 24px;
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
            font-size: 26px;
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-page-title i {
            font-size: 32px;
        }

        /* Card */
        .admin-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(139, 69, 19, 0.08);
            padding: 30px;
            border: 1px solid rgba(139, 69, 19, 0.1);
        }

        /* Messages */
        .admin-message {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-left: 4px solid;
        }

        .admin-message.error {
            background: #ffebee;
            border-left-color: var(--danger-color);
            color: #b71c1c;
        }

        .admin-message.success {
            background: #e8f5e9;
            border-left-color: var(--success-color);
            color: #1b5e20;
        }

        .admin-message i {
            font-size: 20px;
        }

        /* Form Grid */
        .product-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-row-2 {
            grid-column: 1 / -1;
        }

        /* Form Groups */
        .admin-form-group {
            margin-bottom: 5px;
            width: 100%;
        }

        .admin-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .admin-form-group input[type="text"],
        .admin-form-group input[type="number"],
        .admin-form-group select,
        .admin-form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
            box-sizing: border-box;
        }

        .admin-form-group input:focus,
        .admin-form-group select:focus,
        .admin-form-group textarea:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(139,69,19,0.1);
        }

        /* Select box styling */
        .admin-form-group select {
            width: 100%;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238B4513' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }

        .admin-form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            background: var(--bg-light);
            cursor: pointer;
        }

        .admin-form-group input[type="file"]:hover {
            border-color: var(--primary-color);
        }

        /* Checkbox Styles */
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 15px;
            background: var(--bg-light);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkbox-label:hover {
            background: #f0f0f0;
            border-color: var(--primary-color);
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary-color);
        }

        /* Image Preview và Current Image */
        .image-current-wrap {
            margin-top: 15px;
            text-align: center;
            padding: 15px;
            background: var(--bg-light);
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .image-current-wrap img {
            max-width: 250px;
            max-height: 200px;
            object-fit: contain;
            border-radius: 12px;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 15px rgba(139,69,19,0.2);
        }

        .image-preview-wrap {
            margin-top: 15px;
            text-align: center;
        }

        .image-preview-wrap img {
            max-width: 250px;
            max-height: 200px;
            object-fit: contain;
            border-radius: 12px;
            border: 3px solid var(--primary-color);
            box-shadow: 0 4px 15px rgba(139,69,19,0.2);
        }

        /* Required Star */
        .required-star {
            color: var(--danger-color);
            font-size: 16px;
            margin-left: 3px;
        }

        /* Button Styles */
        .admin-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
            height: 45px;
            white-space: nowrap;
        }

        .admin-btn i {
            font-size: 16px;
        }

        .admin-btn-primary {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 10px rgba(139,69,19,0.2);
        }

        .admin-btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(139,69,19,0.3);
        }

        .admin-btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .admin-btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }

        /* Form Actions */
        .product-form-actions {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 2px solid var(--border-color);
        }

        /* Info Text */
        .info-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .info-text i {
            color: var(--primary-color);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-content {
                padding: 15px;
            }

            .admin-card {
                padding: 20px;
            }

            .product-form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .admin-page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .product-form-actions {
                flex-direction: column;
            }

            .admin-btn {
                width: 100%;
                min-width: 100%;
            }

            .checkbox-label {
                width: 100%;
            }

            .image-current-wrap img,
            .image-preview-wrap img {
                max-width: 100%;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .admin-card {
            animation: fadeIn 0.5s ease;
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title">
            <i class="fas fa-edit"></i>
            Chỉnh sửa sản phẩm
        </h1>
        <a href="admin_dashboard.php?page=products" class="admin-btn admin-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    <?php if ($errorMessage): ?>
        <div class="admin-message error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($successMessage): ?>
        <div class="admin-message success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <?php if ($product): ?>
    <div class="admin-card">
        <form method="post" action="edit_product.php?id=<?php echo $productId; ?>" enctype="multipart/form-data" class="admin-add-form">
            <div class="product-form-grid">
                <!-- Tên sản phẩm -->
                <div class="admin-form-group">
                    <label for="name">
                        <i class="fas fa-cake"></i>
                        Tên sản phẩm <span class="required-star">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="<?php echo htmlspecialchars($product['name']); ?>" 
                           required 
                           placeholder="VD: Bánh kem sinh nhật">
                </div>

                <!-- Danh mục -->
                <div class="admin-form-group">
                    <label for="category_id">
                        <i class="fas fa-tag"></i>
                        Danh mục <span class="required-star">*</span>
                    </label>
                    <select name="category_id" id="category_id" required>
                        <option value="">-- Chọn danh mục --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" 
                                <?php echo ((int)$cat['id'] === (int)($product['category_id'] ?? 0)) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Giá -->
                <div class="admin-form-group">
                    <label for="price">
                        <i class="fas fa-dollar-sign"></i>
                        Giá (VNĐ) <span class="required-star">*</span>
                    </label>
                    <input type="number" 
                           name="price" 
                           id="price" 
                           min="0" 
                           step="1000" 
                           value="<?php echo (int)$product['price']; ?>" 
                           required 
                           placeholder="0">
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Nhập giá theo VNĐ, ví dụ: 150000
                    </div>
                </div>

                <!-- Mô tả ngắn -->
                <div class="admin-form-group">
                    <label for="short_description">
                        <i class="fas fa-align-left"></i>
                        Mô tả ngắn
                    </label>
                    <input type="text" 
                           name="short_description" 
                           id="short_description" 
                           value="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>" 
                           placeholder="Mô tả ngắn hiển thị trên danh sách">
                </div>

                <!-- Mô tả chi tiết -->
                <div class="admin-form-group form-row-2">
                    <label for="description">
                        <i class="fas fa-align-justify"></i>
                        Mô tả chi tiết
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="5" 
                              placeholder="Mô tả chi tiết về sản phẩm, thành phần, hướng dẫn sử dụng..."><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>

                <!-- Ảnh sản phẩm -->
                <div class="admin-form-group">
                    <label for="image">
                        <i class="fas fa-image"></i>
                        Ảnh sản phẩm
                    </label>
                    
                    <!-- Hiển thị ảnh hiện tại -->
                    <?php if (!empty($product['image'])): ?>
                    <div class="image-current-wrap">
                        <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" alt="Ảnh hiện tại">
                        <div class="info-text" style="justify-content: center; margin-top: 10px;">
                            <i class="fas fa-info-circle"></i>
                            Ảnh hiện tại
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <label for="image" style="margin-top: 15px; display: block;">
                        <i class="fas fa-upload"></i>
                        Chọn ảnh mới (để trống nếu giữ ảnh cũ)
                    </label>
                    <input type="file" 
                           name="image" 
                           id="image" 
                           accept="image/*">
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Chấp nhận: JPG, JPEG, PNG, GIF, WEBP
                    </div>
                    <div class="image-preview-wrap" id="imagePreviewWrap" style="display:none;">
                        <img id="imagePreview" src="" alt="Preview ảnh mới">
                        <div class="info-text" style="justify-content: center; margin-top: 10px;">
                            <i class="fas fa-eye"></i>
                            Xem trước ảnh mới
                        </div>
                    </div>
                </div>

                <!-- Checkbox options -->
                <div class="admin-form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" <?php echo !empty($product['is_featured']) ? 'checked' : ''; ?>>
                        <i class="fas fa-star" style="color: #f1c40f;"></i>
                        <span>Sản phẩm nổi bật</span>
                    </label>
                    
                    <label class="checkbox-label" style="margin-top: 12px;">
                        <input type="checkbox" name="is_active" value="1" <?php echo !isset($product['is_active']) || $product['is_active'] ? 'checked' : ''; ?>>
                        <i class="fas fa-eye" style="color: var(--primary-color);"></i>
                        <span>Hiển thị (đang bán)</span>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="product-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-save"></i>
                    Cập nhật sản phẩm
                </button>
                <a href="admin_dashboard.php?page=products" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php if ($product): ?>
<script>
// Preview image khi chọn ảnh mới
document.getElementById('image').addEventListener('change', function(e) {
    var wrap = document.getElementById('imagePreviewWrap');
    var img = document.getElementById('imagePreview');
    if (this.files && this.files[0]) {
        var r = new FileReader();
        r.onload = function() { 
            img.src = r.result; 
            wrap.style.display = 'block';
            wrap.style.animation = 'fadeIn 0.3s ease';
        };
        r.readAsDataURL(this.files[0]);
    } else { 
        wrap.style.display = 'none'; 
    }
});

// Format price input
document.getElementById('price').addEventListener('input', function(e) {
    var value = this.value.replace(/\D/g, '');
    if (value) {
        this.value = parseInt(value);
    }
});

// Auto-resize textarea
document.getElementById('description').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
}, false);

// Trigger auto-resize on page load
window.addEventListener('load', function() {
    var textarea = document.getElementById('description');
    textarea.style.height = 'auto';
    textarea.style.height = (textarea.scrollHeight) + 'px';
});
</script>
<?php endif; ?>
</body>
</html>