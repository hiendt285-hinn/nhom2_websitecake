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
?>
<div class="admin-content">
    <h1 class="admin-page-title"><i class="fas fa-list"></i> Quản lý danh mục sản phẩm</h1>

    <div class="admin-card">
        <h2>Thêm danh mục mới</h2>
        <form method="post" style="max-width: 500px;">
            <div style="margin-bottom: 12px;">
                <input type="text" name="name" placeholder="Tên danh mục" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
            </div>
            <div style="margin-bottom: 12px;">
                <input type="text" name="slug" placeholder="Slug" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
            </div>
            <div style="margin-bottom: 12px;">
                <textarea name="description" placeholder="Mô tả" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;"></textarea>
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
                <?php while ($result && $row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo (int)$row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['slug']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <a href="admin_dashboard.php?page=producttype&edit=<?php echo (int)$row['id']; ?>" class="admin-link">Sửa</a>
                        <span style="color: #999;">|</span>
                        <a href="admin_dashboard.php?page=producttype&delete=<?php echo (int)$row['id']; ?>" class="admin-link" style="color: #d32f2f;" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php if (isset($_GET['edit'])): ?>
        <?php
        $id = (int)$_GET['edit'];
        $res = $conn->query("SELECT * FROM categories WHERE id=$id LIMIT 1");
        $cat = $res ? $res->fetch_assoc() : null;
        if ($cat):
        ?>
    <div class="admin-card">
        <h2>Sửa danh mục</h2>
        <form method="post" style="max-width: 500px;">
            <input type="hidden" name="id" value="<?php echo (int)$cat['id']; ?>">
            <div style="margin-bottom: 12px;">
                <input type="text" name="name" value="<?php echo htmlspecialchars($cat['name']); ?>" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
            </div>
            <div style="margin-bottom: 12px;">
                <input type="text" name="slug" value="<?php echo htmlspecialchars($cat['slug']); ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;">
            </div>
            <div style="margin-bottom: 12px;">
                <textarea name="description" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px;"><?php echo htmlspecialchars($cat['description']); ?></textarea>
            </div>
            <button type="submit" name="edit_category" class="admin-btn admin-btn-primary">Cập nhật danh mục</button>
        </form>
    </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
