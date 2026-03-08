<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}
require_once 'connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS news (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  slug varchar(255) DEFAULT NULL,
  summary varchar(500) DEFAULT NULL,
  content text DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY slug (slug),
  KEY is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$error = '';

// Xóa
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_dashboard.php?page=news");
    exit();
}

// Lưu (thêm mới hoặc cập nhật)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = trim($_POST['title'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if ($title === '') {
        $error = 'Vui lòng nhập tiêu đề.';
    } else {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE news SET title = ?, summary = ?, content = ?, image = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param('ssssii', $title, $summary, $content, $image, $isActive, $id);
            if ($stmt->execute()) {
                $message = 'Đã cập nhật bài viết.';
            } else {
                $error = 'Lỗi cập nhật.';
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO news (title, summary, content, image, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssi', $title, $summary, $content, $image, $isActive);
            if ($stmt->execute()) {
                $message = 'Đã thêm bài viết mới.';
            } else {
                $error = 'Lỗi thêm bài viết.';
            }
            $stmt->close();
        }
    }
}

$editRow = null;
if (isset($_GET['edit_id'])) {
    $editId = (int)$_GET['edit_id'];
    $res = $conn->query("SELECT * FROM news WHERE id = $editId LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $editRow = $res->fetch_assoc();
    }
}

// Bộ lọc theo trạng thái (giống manage_orders)
$filterActive = isset($_GET['filter_active']) ? $_GET['filter_active'] : '';
if ($filterActive === '1') {
    $list = $conn->query("SELECT id, title, is_active, created_at FROM news WHERE is_active = 1 ORDER BY created_at DESC");
} elseif ($filterActive === '0') {
    $list = $conn->query("SELECT id, title, is_active, created_at FROM news WHERE is_active = 0 ORDER BY created_at DESC");
} else {
    $list = $conn->query("SELECT id, title, is_active, created_at FROM news ORDER BY created_at DESC");
}
?>
<style>
.news-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
.news-badge-active { background: #27ae60; color: #fff; }
.news-badge-hidden { background: #95a5a6; color: #fff; }
.news-filter { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.news-filter a { padding: 6px 14px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 500; background: #f0f0f0; color: #9a7b5a; }
.news-filter a:hover { background: #e0e0e0; }
.news-filter a.active { background: #9a7b5a; color: #fff; }
.news-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
</style>

<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-newspaper"></i> Quản lý tin tức</h1>
        <button type="button" class="admin-btn admin-btn-primary" onclick="document.getElementById('addNewsModal').style.display='flex';"><i class="fas fa-plus-circle"></i> Thêm bài viết mới</button>
    </div>

    <?php if ($message): ?>
        <div class="admin-message admin-message-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="admin-message admin-message-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="admin-card">
        <div class="news-filter">
            <span style="font-weight: 600; color: #2c3e50;">Lọc theo trạng thái:</span>
            <a href="admin_dashboard.php?page=news" class="<?php echo $filterActive === '' ? 'active' : ''; ?>">Tất cả</a>
            <a href="admin_dashboard.php?page=news&filter_active=1" class="<?php echo $filterActive === '1' ? 'active' : ''; ?>">Hiển thị</a>
            <a href="admin_dashboard.php?page=news&filter_active=0" class="<?php echo $filterActive === '0' ? 'active' : ''; ?>">Ẩn</a>
        </div>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tiêu đề</th>
                    <th>Trạng thái</th>
                    <th>Ngày đăng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($list && $list->num_rows > 0): ?>
                    <?php while ($row = $list->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo (int)$row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td>
                                <span class="news-badge <?php echo (int)$row['is_active'] ? 'news-badge-active' : 'news-badge-hidden'; ?>">
                                    <?php echo (int)$row['is_active'] ? 'Hiển thị' : 'Ẩn'; ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td class="admin-action-cell news-actions">
                                <a href="admin_dashboard.php?page=news&edit_id=<?php echo (int)$row['id']; ?>" class="admin-btn admin-btn-primary admin-btn-sm" style="text-decoration:none;"><i class="fas fa-edit"></i> Sửa</a>
                                <a href="admin_dashboard.php?page=news&delete_id=<?php echo (int)$row['id']; ?>" class="admin-btn admin-btn-danger admin-btn-sm" style="text-decoration:none;" onclick="return confirm('Xóa bài viết này?');"><i class="fas fa-trash-alt"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 24px; color: #7f8c8d;">
                            <?php echo $filterActive !== '' ? 'Không có bài viết nào với trạng thái đã chọn.' : 'Chưa có bài viết nào. Thêm bài viết bằng nút phía trên.'; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Popup thêm bài viết mới -->
    <div id="addNewsModal" class="edit-modal" style="display:none;" onclick="if(event.target===this) this.style.display='none';">
        <div class="admin-modal-box" style="max-width: 640px;" onclick="event.stopPropagation();">
            <div class="admin-modal-header">
                <h2 class="admin-modal-title">Thêm bài viết mới</h2>
                <button type="button" class="admin-modal-close" onclick="document.getElementById('addNewsModal').style.display='none';" aria-label="Đóng">&times;</button>
            </div>
            <form method="POST">
                <div class="admin-modal-body">
                    <div class="admin-form-group">
                        <label for="add_title">Tiêu đề *</label>
                        <input type="text" id="add_title" name="title" required placeholder="Nhập tiêu đề bài viết">
                    </div>
                    <div class="admin-form-group">
                        <label for="add_summary">Tóm tắt (hiển thị ở danh sách)</label>
                        <textarea id="add_summary" name="summary" rows="2" placeholder="Tóm tắt ngắn gọn"></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label for="add_content">Nội dung</label>
                        <textarea id="add_content" name="content" rows="6" placeholder="Nội dung chi tiết"></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label for="add_image">Ảnh (tên file hoặc URL)</label>
                        <input type="text" id="add_image" name="image" placeholder="vd: tin-tuc-1.jpg hoặc https://...">
                    </div>
                    <div class="admin-form-group">
                        <label><input type="checkbox" name="is_active" value="1" checked> Hiển thị trên trang tin tức</label>
                    </div>
                    <div class="admin-modal-actions">
                        <button type="button" class="admin-btn admin-btn-secondary" onclick="document.getElementById('addNewsModal').style.display='none';">Hủy</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Thêm bài viết</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($editRow): ?>
    <div id="editNewsModal" class="edit-modal" style="display:flex;">
        <div class="admin-modal-box" style="max-width: 640px;">
            <div class="admin-modal-header">
                <h2 class="admin-modal-title">Chỉnh sửa bài viết</h2>
                <a href="admin_dashboard.php?page=news" class="admin-modal-close" aria-label="Đóng" style="text-decoration:none; color:inherit;">&times;</a>
            </div>
            <form method="POST">
                <div class="admin-modal-body">
                    <input type="hidden" name="id" value="<?php echo (int)$editRow['id']; ?>">
                    <div class="admin-form-group">
                        <label for="edit_title">Tiêu đề *</label>
                        <input type="text" id="edit_title" name="title" value="<?php echo htmlspecialchars($editRow['title']); ?>" required>
                    </div>
                    <div class="admin-form-group">
                        <label for="edit_summary">Tóm tắt</label>
                        <textarea id="edit_summary" name="summary" rows="2"><?php echo htmlspecialchars($editRow['summary'] ?? ''); ?></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label for="edit_content">Nội dung</label>
                        <textarea id="edit_content" name="content" rows="6"><?php echo htmlspecialchars($editRow['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="admin-form-group">
                        <label for="edit_image">Ảnh (tên file hoặc URL)</label>
                        <input type="text" id="edit_image" name="image" value="<?php echo htmlspecialchars($editRow['image'] ?? ''); ?>" placeholder="vd: tin-tuc-1.jpg hoặc https://...">
                    </div>
                    <div class="admin-form-group">
                        <label><input type="checkbox" name="is_active" value="1" <?php echo ($editRow['is_active'] ?? 1) ? 'checked' : ''; ?>> Hiển thị trên trang tin tức</label>
                    </div>
                    <div class="admin-modal-actions">
                        <a href="admin_dashboard.php?page=news" class="admin-btn admin-btn-secondary">Hủy</a>
                        <button type="submit" class="admin-btn admin-btn-primary">Cập nhật</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
