<?php
session_start();
require_once 'connect.php';
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}

// Thêm danh mục mới
if (isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    if ($name) {
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $slug, $description);
        $stmt->execute();
        $stmt->close();
        $success = "Thêm danh mục thành công!";
    }
}

// Xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?page=producttype&deleted=1");
    exit();
}

// Sửa danh mục
if (isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);
    $description = trim($_POST['description']);
    $stmt = $conn->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $slug, $description, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?page=producttype&updated=1");
    exit();
}

// Lấy danh sách danh mục
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
$hasCategories = $result && $result->num_rows > 0;
?>

<style>
    /* Variables - Giống trang quản lý sản phẩm */
    :root {
        --primary-color: #9a7b5a;
        --primary-light: #b89a7a;
        --primary-dark: #ffffff;
        --success-color: #27ae60;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --border-color: #e0e0e0;
        --bg-light: #f8f9fa;
        --text-dark: #2c3e50;
        --text-light: #7f8c8d;
    }

    body {
        background: #f5f5f5;
    }

    .header-actions { margin-top: 14px; }
    .add-modal-trigger { min-width: 180px; }

    .admin-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid var(--border-color);
    }

    .admin-card h2 {
        color: var(--primary-color);
        font-size: 18px;
        font-weight: 600;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-card h2 i {
        color: var(--primary-color);
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 8px;
        font-size: 14px;
    }

    .form-group label i {
        color: var(--primary-color);
        font-size: 16px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid var(--border-color);
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.3s ease;
        background: var(--bg-light);
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(154, 123, 90, 0.1);
        background: white;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    /* Button Styles - Giống trang quản lý sản phẩm */
    .admin-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        min-width: 100px;
        height: 40px;
        white-space: nowrap;
    }

    .admin-btn i {
        font-size: 14px;
    }

    .admin-btn-sm {
        min-width: 80px;
        height: 36px;
        padding: 8px 16px;
        font-size: 13px;
    }

    .admin-btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .admin-btn-primary:hover {
        background: var(--primary-light);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(154, 123, 90, 0.2);
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
        background: var(--danger-color);
        color: white;
    }

    .admin-btn-danger:hover {
        background: #c0392b;
        transform: translateY(-2px);
    }

    /* Table Styles - Giống trang quản lý sản phẩm */
    .table-wrapper {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--border-color);
    }

    .admin-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 800px;
    }

    .admin-table th {
        background: var(--primary-color);
        color: white;
        font-weight: 600;
        padding: 15px 12px;
        font-size: 14px;
        text-align: left;
        white-space: nowrap;
    }

    .admin-table td {
        padding: 15px 12px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        vertical-align: middle;
    }

    .admin-table tbody tr:hover td {
        background: #f9f6f2;
    }

    /* Action Cell */
    .admin-action-cell {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        min-width: 180px;
    }

    /* Category Badge - Giống trang quản lý sản phẩm */
    .category-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: #f0f0f0;
        border-radius: 20px;
        font-size: 12px;
        color: var(--text-dark);
    }

    .category-badge i {
        color: var(--primary-color);
    }

    /* Slug Code */
    .slug-code {
        background: #f0f0f0;
        padding: 4px 8px;
        border-radius: 4px;
        font-family: monospace;
        font-size: 12px;
        color: var(--primary-color);
    }

    /* Description Preview */
    .desc-preview {
        max-width: 250px;
        color: var(--text-light);
        font-size: 13px;
        line-height: 1.5;
    }

    /* Info Text */
    .info-text {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        color: var(--text-light);
        margin-top: 5px;
    }

    .info-text i {
        color: var(--primary-color);
    }

    /* Required Star */
    .required {
        color: var(--danger-color);
        margin-left: 3px;
    }

    /* Messages */
    .message {
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideDown 0.3s ease;
    }

    .message.success {
        background: #e8f5e9;
        color: #27ae60;
        border-left: 4px solid var(--success-color);
    }

    .message i {
        font-size: 20px;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Modal Styles - Giống trang quản lý sản phẩm */
    .edit-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .admin-modal-box {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .admin-modal-header {
        background: var(--primary-color);
        padding: 16px 20px;
        border-radius: 12px 12px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .admin-modal-title {
        color: white;
        margin: 0;
        font-size: 18px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .admin-modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        font-size: 24px;
        cursor: pointer;
        width: 36px;
        height: 36px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .admin-modal-close:hover {
        background: rgba(255,255,255,0.3);
    }

    .admin-modal-body {
        padding: 25px;
    }

    .admin-modal-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 25px;
    }
    .add-category-modal .admin-modal-box { max-width: 760px; width: 95%; }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-state i {
        font-size: 64px;
        color: var(--border-color);
        margin-bottom: 20px;
    }

    .empty-state p {
        color: var(--text-light);
        font-size: 16px;
        margin-bottom: 20px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .admin-action-cell {
            flex-direction: column;
        }

        .admin-btn-sm {
            width: 100%;
            min-width: 100%;
        }

        .admin-modal-actions {
            flex-direction: column;
        }

        .admin-modal-actions .admin-btn {
            width: 100%;
        }
    }
</style>

<div class="admin-content">
    <!-- Page Header -->
    <div class="admin-page-header">
        <h1 class="admin-page-title">
            <i class="fas fa-list"></i>
            Quản lý danh mục sản phẩm
        </h1>
        <div class="header-actions">
            <button type="button" class="admin-btn admin-btn-primary add-modal-trigger" onclick="openAddCategoryModal()">
                <i class="fas fa-plus-circle"></i> Thêm danh mục mới
            </button>
        </div>
    </div>

    <!-- Messages -->
    <?php if (isset($success)): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            Xóa danh mục thành công!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            Cập nhật danh mục thành công!
        </div>
    <?php endif; ?>

    <!-- Categories List Card -->
    <div class="admin-card">
        <h2>
            <i class="fas fa-list-ul"></i>
            Danh sách danh mục
        </h2>

        <?php if ($hasCategories): ?>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên danh mục</th>
                            <th>Slug</th>
                            <th>Mô tả</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr data-id="<?php echo (int)$row['id']; ?>" 
                            data-cat="<?php echo htmlspecialchars(json_encode([
                                'name' => $row['name'], 
                                'slug' => $row['slug'] ?? '', 
                                'description' => $row['description'] ?? ''
                            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>">
                            <td>
                                <span class="category-badge">
                                    <i class="fas fa-hashtag"></i>
                                    <?php echo (int)$row['id']; ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: var(--primary-color);">
                                    <?php echo htmlspecialchars($row['name']); ?>
                                </strong>
                            </td>
                            <td>
                                <span class="slug-code">
                                    <?php echo htmlspecialchars($row['slug'] ?: '—'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="desc-preview" title="<?php echo htmlspecialchars($row['description'] ?? ''); ?>">
                                    <?php 
                                    $desc = $row['description'] ?? '';
                                    echo htmlspecialchars(mb_substr($desc, 0, 50));
                                    echo mb_strlen($desc) > 50 ? '…' : '';
                                    ?>
                                </div>
                            </td>
                            <td class="admin-action-cell">
                                <button type="button" 
                                        class="admin-btn admin-btn-primary admin-icon-btn admin-tooltip" 
                                        data-tooltip="Chỉnh sửa"
                                        onclick="openEditCategory(<?php echo (int)$row['id']; ?>, this)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="admin_dashboard.php?page=producttype&delete=<?php echo (int)$row['id']; ?>" 
                                   class="admin-btn admin-btn-danger admin-icon-btn admin-tooltip"
                                   data-tooltip="Xóa"
                                   onclick="return confirm('Bạn có chắc muốn xóa?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>Chưa có danh mục nào.</p>
                <button class="admin-btn admin-btn-primary" onclick="openAddCategoryModal()">
                    <i class="fas fa-plus-circle"></i>
                    Thêm danh mục đầu tiên
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div id="addCategoryModal" class="edit-modal add-category-modal" style="display:none;">
        <div class="admin-modal-box">
            <div class="admin-modal-header">
                <h2 class="admin-modal-title"><i class="fas fa-plus-circle"></i> Thêm danh mục mới</h2>
                <button type="button" class="admin-modal-close" onclick="closeAddCategoryModal()"><i class="fas fa-times"></i></button>
            </div>
            <form method="post">
                <div class="admin-modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="add_name"><i class="fas fa-tag"></i>Tên danh mục <span class="required">*</span></label>
                            <input type="text" id="add_name" name="name" class="form-control" placeholder="VD: Bánh kem sinh nhật" required>
                        </div>
                        <div class="form-group">
                            <label for="add_slug"><i class="fas fa-link"></i>Slug</label>
                            <input type="text" id="add_slug" name="slug" class="form-control" placeholder="banh-kem-sinh-nhat">
                            <div class="info-text"><i class="fas fa-info-circle"></i>Slug tự động tạo từ tên nếu để trống</div>
                        </div>
                        <div class="form-group full-width">
                            <label for="add_description"><i class="fas fa-align-left"></i>Mô tả</label>
                            <textarea id="add_description" name="description" class="form-control" placeholder="Mô tả chi tiết về danh mục..." rows="3"></textarea>
                        </div>
                    </div>
                    <div class="admin-modal-actions">
                        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeAddCategoryModal()"><i class="fas fa-times"></i> Hủy</button>
                        <button type="submit" name="add_category" class="admin-btn admin-btn-primary"><i class="fas fa-save"></i> Thêm danh mục</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editCategoryModal" class="edit-modal" style="display:none;">
        <div class="admin-modal-box">
            <div class="admin-modal-header">
                <h2 class="admin-modal-title">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa danh mục
                </h2>
                <button type="button" class="admin-modal-close" onclick="closeEditCategory()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="post">
                <div class="admin-modal-body">
                    <input type="hidden" name="edit_category" value="1">
                    <input type="hidden" name="id" id="edit_cat_id">
                    
                    <div class="form-group">
                        <label for="edit_cat_name">
                            <i class="fas fa-tag"></i>
                            Tên danh mục <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="edit_cat_name" 
                               name="name" 
                               class="form-control" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_cat_slug">
                            <i class="fas fa-link"></i>
                            Slug
                        </label>
                        <input type="text" 
                               id="edit_cat_slug" 
                               name="slug" 
                               class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_cat_description">
                            <i class="fas fa-align-left"></i>
                            Mô tả
                        </label>
                        <textarea id="edit_cat_description" 
                                  name="description" 
                                  class="form-control" 
                                  rows="3"></textarea>
                    </div>
                    
                    <div class="admin-modal-actions">
                        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeEditCategory()">
                            <i class="fas fa-times"></i> Hủy
                        </button>
                        <button type="submit" class="admin-btn admin-btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    var modal = document.getElementById("editCategoryModal");
    
    function openEditCategory(id, btn) {
        var row = btn && btn.closest ? btn.closest('tr') : document.querySelector('tr[data-id="' + id + '"]');
        if (!row) return;
        
        try {
            var data = row.getAttribute('data-cat');
            var cat = data ? JSON.parse(data) : {};
            
            document.getElementById('edit_cat_id').value = id;
            document.getElementById('edit_cat_name').value = cat.name || '';
            document.getElementById('edit_cat_slug').value = cat.slug || '';
            document.getElementById('edit_cat_description').value = cat.description || '';
            
            modal.style.display = 'flex';
        } catch (e) {
            console.error('Error parsing category data:', e);
        }
    }
    
    function closeEditCategory() { 
        modal.style.display = 'none'; 
    }
    function openAddCategoryModal() {
        var addModal = document.getElementById('addCategoryModal');
        if (addModal) addModal.style.display = 'flex';
    }
    function closeAddCategoryModal() {
        var addModal = document.getElementById('addCategoryModal');
        if (addModal) addModal.style.display = 'none';
    }
    
    window.addEventListener('click', function(e) {
        var addModal = document.getElementById('addCategoryModal');
        if (e.target === modal) closeEditCategory();
        if (addModal && e.target === addModal) closeAddCategoryModal();
    });
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeEditCategory();
        }
    });
    
    function generateSlug(text) {
        return text
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[đĐ]/g, 'd')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }
    
    var addNameInput = document.getElementById('add_name');
    var addSlugInput = document.getElementById('add_slug');
    
    if (addNameInput && addSlugInput) {
        addNameInput.addEventListener('blur', function() {
            if (addSlugInput.value === '') {
                addSlugInput.value = generateSlug(this.value);
            }
        });
    }
    
    window.openEditCategory = openEditCategory;
    window.closeEditCategory = closeEditCategory;
    window.openAddCategoryModal = openAddCategoryModal;
    window.closeAddCategoryModal = closeAddCategoryModal;
})();
</script>