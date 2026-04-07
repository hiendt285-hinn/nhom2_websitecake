<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}

require_once 'connect.php';

$error = '';
$success = '';

$categoryResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = intval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $shortDescription = trim($_POST['short_description'] ?? '');
    $categoryId = intval($_POST['category_id'] ?? 0);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (empty($name) || $price < 0 || $categoryId <= 0) {
        $error = 'Vui lòng điền Tên sản phẩm, Giá và chọn Danh mục.';
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $fileName = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt)) {
            $error = 'Chỉ chấp nhận định dạng ảnh: jpg, jpeg, png, gif, webp.';
        } else {
            $newFileName = uniqid() . '.' . $ext;
            $targetDir = realpath(__DIR__ . '/../images') . '/';
            $targetFile = $targetDir . $newFileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO products (name, price, image, description, short_description, category_id, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sisssiii', $name, $price, $newFileName, $description, $shortDescription, $categoryId, $isFeatured, $isActive);
                if ($stmt->execute()) {
                    $success = 'Thêm sản phẩm thành công!';
                    $name = $shortDescription = $description = '';
                    $price = 0;
                    $categoryId = 0;
                    $isFeatured = 0;
                    $isActive = 1;
                } else {
                    $error = 'Lỗi khi thêm sản phẩm.';
                }
                $stmt->close();
            } else {
                $error = 'Lỗi khi upload ảnh.';
            }
        }
    } else {
        $error = 'Vui lòng chọn ảnh sản phẩm.';
    }
}

$categoryResult = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thêm sản phẩm - Sweet Cake</title>
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
    box-sizing: border-box; /* Đảm bảo padding không làm tăng kích thước */
    }

/* Đảm bảo select box có cùng kích thước */
.admin-form-group select {
    width: 100%;
    appearance: none; /* Tùy chỉnh giao diện select */
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%238B4513' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px; /* Chừa chỗ cho icon mũi tên */
}

/* Fix cho container của 2 ô đầu */
.product-form-grid > .admin-form-group:first-child,
.product-form-grid > .admin-form-group:nth-child(2) {
    width: 100%;
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

        /* Image Preview */
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

        /* Button Styles - Đồng bộ với các trang khác */
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
            <i class="fas fa-plus-circle"></i>
            Thêm sản phẩm mới
        </h1>
        <a href="admin_dashboard.php?page=products" class="admin-btn admin-btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Quay lại
        </a>
    </div>

    <?php if ($error): ?>
        <div class="admin-message error">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="admin-message success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="admin-card">
        <form action="" method="post" enctype="multipart/form-data" class="admin-add-form">
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
                           value="<?php echo htmlspecialchars($name ?? ''); ?>" 
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
                        <?php if ($categoryResult): while ($cat = $categoryResult->fetch_assoc()): ?>
                            <option value="<?php echo (int)$cat['id']; ?>" 
                                <?php echo (isset($categoryId) && (int)$categoryId === (int)$cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; endif; ?>
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
                           value="<?php echo isset($price) ? (int)$price : ''; ?>" 
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
                           value="<?php echo htmlspecialchars($shortDescription ?? ''); ?>" 
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
                              placeholder="Mô tả chi tiết về sản phẩm, thành phần, hướng dẫn sử dụng..."><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                </div>

                <!-- Ảnh sản phẩm -->
                <div class="admin-form-group">
                    <label for="image">
                        <i class="fas fa-image"></i>
                        Ảnh sản phẩm <span class="required-star">*</span>
                    </label>
                    <input type="file" 
                           name="image" 
                           id="image" 
                           accept="image/*" 
                           required>
                    <div class="info-text">
                        <i class="fas fa-info-circle"></i>
                        Chấp nhận: JPG, JPEG, PNG, GIF, WEBP
                    </div>
                    <div class="image-preview-wrap" id="imagePreviewWrap" style="display:none;">
                        <img id="imagePreview" src="" alt="Preview">
                    </div>
                </div>

                <!-- Checkbox options -->
                <div class="admin-form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_featured" value="1" <?php echo !empty($isFeatured) ? 'checked' : ''; ?>>
                        <i class="fas fa-star" style="color: #f1c40f;"></i>
                        <span>Sản phẩm nổi bật</span>
                    </label>
                    
                    <label class="checkbox-label" style="margin-top: 12px;">
                        <input type="checkbox" name="is_active" value="1" <?php echo !isset($isActive) || $isActive ? 'checked' : ''; ?>>
                        <i class="fas fa-eye" style="color: var(--primary-color);"></i>
                        <span>Hiển thị (đang bán)</span>
                    </label>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="product-form-actions">
                <button type="submit" class="admin-btn admin-btn-primary">
                    <i class="fas fa-save"></i>
                    Thêm sản phẩm
                </button>
                <a href="admin_dashboard.php?page=products" class="admin-btn admin-btn-secondary">
                    <i class="fas fa-times"></i>
                    Hủy
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Preview image
document.getElementById('image').addEventListener('change', function(e) {
    var wrap = document.getElementById('imagePreviewWrap');
    var img = document.getElementById('imagePreview');
    if (this.files && this.files[0]) {
        var r = new FileReader();
        r.onload = function() { 
            img.src = r.result; 
            wrap.style.display = 'block';
            // Thêm animation
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
</script>

</body>
</html>