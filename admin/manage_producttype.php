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
    }
}

// Xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?page=producttype");
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
    header("Location: admin_dashboard.php?page=producttype");
    exit();
}

// Lấy danh sách danh mục
$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
$hasCategories = $result && $result->num_rows > 0;
?>
<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-list"></i> Quản lý danh mục sản phẩm</h1>
    </div>

    <div class="admin-card">
        <h2><i class="fas fa-plus-circle"></i> Thêm danh mục mới</h2>
        <form method="post">
            <div class="admin-form-group" style="max-width: 400px;">
                <label for="add_name">Tên danh mục</label>
                <input type="text" id="add_name" name="name" placeholder="Tên danh mục" required>
            </div>
            <div class="admin-form-group" style="max-width: 400px;">
                <label for="add_slug">Slug</label>
                <input type="text" id="add_slug" name="slug" placeholder="slug-danh-muc">
            </div>
            <div class="admin-form-group" style="max-width: 500px;">
                <label for="add_description">Mô tả</label>
                <textarea id="add_description" name="description" placeholder="Mô tả" rows="3"></textarea>
            </div>
            <button type="submit" name="add_category" class="admin-btn admin-btn-primary">Thêm danh mục</button>
        </form>
    </div>

    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên danh mục</th>
                    <th>Slug</th>
                    <th>Mô tả</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($hasCategories): while ($row = $result->fetch_assoc()): ?>
                <tr data-id="<?php echo (int)$row['id']; ?>" data-cat="<?php echo htmlspecialchars(json_encode(['name' => $row['name'], 'slug' => $row['slug'] ?? '', 'description' => $row['description'] ?? ''], JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>">
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['slug']); ?></td>
                    <td><?php echo htmlspecialchars(mb_substr($row['description'] ?? '', 0, 50)); ?><?php echo mb_strlen($row['description'] ?? '') > 50 ? '…' : ''; ?></td>
                    <td class="admin-action-cell">
                        <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="openEditCategory(<?php echo (int)$row['id']; ?>, this)"><i class="fas fa-edit"></i> Sửa</button>
                        <a href="admin_dashboard.php?page=producttype&delete=<?php echo (int)$row['id']; ?>" class="admin-btn admin-btn-danger admin-btn-sm" style="text-decoration:none;" onclick="return confirm('Bạn có chắc muốn xóa?');"><i class="fas fa-trash-alt"></i> Xóa</a>
                    </td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="5">Chưa có danh mục nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="editCategoryModal" class="edit-modal" style="display:none;">
        <div class="admin-modal-box">
            <div class="admin-modal-header">
                <h2 class="admin-modal-title">Chỉnh sửa danh mục</h2>
                <button type="button" class="admin-modal-close" onclick="closeEditCategory()" aria-label="Đóng">&times;</button>
            </div>
            <form method="post">
                <div class="admin-modal-body">
                    <input type="hidden" name="edit_category" value="1">
                    <input type="hidden" name="id" id="edit_cat_id">
                    <div class="admin-form-group">
                        <label for="edit_cat_name">Tên danh mục</label>
                        <input type="text" id="edit_cat_name" name="name" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="edit_cat_slug">Slug</label>
                        <input type="text" id="edit_cat_slug" name="slug">
                    </div>
                    <div class="admin-form-group">
                        <label for="edit_cat_description">Mô tả</label>
                        <textarea id="edit_cat_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="admin-modal-actions">
                        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeEditCategory()">Hủy</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Cập nhật danh mục</button>
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
        var data = row.getAttribute('data-cat');
        var cat = data ? JSON.parse(data) : {};
        document.getElementById('edit_cat_id').value = id;
        document.getElementById('edit_cat_name').value = cat.name || '';
        document.getElementById('edit_cat_slug').value = cat.slug || '';
        document.getElementById('edit_cat_description').value = cat.description || '';
        modal.style.display = 'flex';
    }
    function closeEditCategory() { modal.style.display = 'none'; }
    window.onclick = function(e) { if (e.target === modal) closeEditCategory(); };
    window.openEditCategory = openEditCategory;
    window.closeEditCategory = closeEditCategory;
})();
</script>
