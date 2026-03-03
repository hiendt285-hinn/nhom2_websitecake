<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: login_admin.php');
    exit();
}


require_once 'connect.php';

$table_name = 'sizes';
$attribute_name = 'Kích cỡ';

$message = '';
$error = '';

// Xử lý Thêm mới và Chỉnh sửa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_name'])) {
        // --- Xử lý Thêm mới ---
        $new_name = trim($_POST['new_name']);
        if (!empty($new_name)) {
            $check_stmt = $conn->prepare("SELECT id FROM $table_name WHERE name = ?");
            $check_stmt->bind_param('s', $new_name);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $insert_stmt = $conn->prepare("INSERT INTO $table_name (name) VALUES (?)");
                $insert_stmt->bind_param('s', $new_name);
                if ($insert_stmt->execute()) {
                    $message = "Thêm $attribute_name thành công!";
                } else {
                    $error = "Lỗi khi thêm $attribute_name.";
                }
                $insert_stmt->close();
            } else {
                $error = "$attribute_name đã tồn tại!";
            }
            $check_stmt->close();
        }
    } elseif (isset($_POST['edit_id'], $_POST['edit_name'])) {
        // --- Xử lý Cập nhật ---
        $edit_id = intval($_POST['edit_id']);
        $edit_name = trim($_POST['edit_name']);

        if (!empty($edit_name) && $edit_id > 0) {
            // Kiểm tra trùng lặp (trừ bản ghi đang chỉnh sửa)
            $check_stmt = $conn->prepare("SELECT id FROM $table_name WHERE name = ? AND id != ?");
            $check_stmt->bind_param('si', $edit_name, $edit_id);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows == 0) {
                $update_stmt = $conn->prepare("UPDATE $table_name SET name = ? WHERE id = ?");
                $update_stmt->bind_param('si', $edit_name, $edit_id);
                if ($update_stmt->execute()) {
                    $message = "Cập nhật $attribute_name thành công!";
                } else {
                    $error = "Lỗi khi cập nhật $attribute_name.";
                }
                $update_stmt->close();
            } else {
                $error = "$attribute_name '$edit_name' đã tồn tại với ID khác.";
            }
            $check_stmt->close();
        }
    }
}

// Xử lý Xóa
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    // Chú ý: Việc xóa có thể thất bại do ràng buộc khóa ngoại nếu thuộc tính đang được sử dụng.
    $stmt = $conn->prepare("DELETE FROM $table_name WHERE id = ?");
    $stmt->bind_param('i', $delete_id);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php?page=sizes&success=deleted");
        exit();
    } else {
        $error = "Không thể xóa $attribute_name. Có thể đang được sử dụng (liên kết với sản phẩm, đơn hàng, hoặc giỏ hàng).";
    }
    $stmt->close();
}

// Lấy danh sách
$sql = "SELECT id, name FROM $table_name ORDER BY id ASC";
$result = $conn->query($sql);
?>
<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-expand-arrows-alt"></i> Quản lý <?php echo $attribute_name; ?></h1>
    </div>
    <?php if ($message): ?><div class="admin-message admin-message-success"><?php echo htmlspecialchars($message); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="admin-message admin-message-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <div class="admin-card">
        <h2><i class="fas fa-plus-circle"></i> Thêm <?php echo $attribute_name; ?> mới</h2>
        <form method="POST" class="admin-add-form">
            <div class="admin-form-row">
                <div class="admin-form-group">
                    <label for="new_name">Tên <?php echo $attribute_name; ?></label>
                    <input type="text" id="new_name" name="new_name" placeholder="Ví dụ: 20cm" required>
                </div>
                <button type="submit" class="admin-btn admin-btn-primary">Thêm</button>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên <?php echo $attribute_name; ?></th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id'] ?></td>
                            <td><?php echo htmlspecialchars($row['name']) ?></td>
                            <td class="admin-action-cell">
                                <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="openEditModal(<?php echo $row['id']; ?>, '<?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>')"><i class="fas fa-edit"></i> Sửa</button>
                                <a href="admin_dashboard.php?page=sizes&delete_id=<?php echo $row['id']; ?>" class="admin-btn admin-btn-danger admin-btn-sm" style="text-decoration:none;" onclick="return confirm('Bạn có chắc muốn xóa <?php echo htmlspecialchars($row['name'], ENT_QUOTES); ?>?');"><i class="fas fa-trash-alt"></i> Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3">Chưa có <?php echo $attribute_name; ?> nào được thêm.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="editModal" class="edit-modal" style="display:none;">
        <div class="admin-modal-box">
            <div class="admin-modal-header">
                <h2 class="admin-modal-title">Chỉnh sửa <?php echo $attribute_name; ?></h2>
                <button type="button" class="admin-modal-close" onclick="closeEditModal()" aria-label="Đóng">&times;</button>
            </div>
            <form method="POST">
                <div class="admin-modal-body">
                    <input type="hidden" id="edit_id" name="edit_id">
                    <div class="admin-form-group">
                        <label for="edit_name">Tên <?php echo $attribute_name; ?></label>
                        <input type="text" id="edit_name" name="edit_name" required>
                    </div>
                    <div class="admin-modal-actions">
                        <button type="button" class="admin-btn admin-btn-secondary" onclick="closeEditModal()">Hủy</button>
                        <button type="submit" class="admin-btn admin-btn-primary">Lưu thay đổi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
<script>
(function() {
    var modal = document.getElementById("editModal");
    function openEditModal(id, name) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        modal.style.display = "flex";
    }
    function closeEditModal() { modal.style.display = "none"; }
    window.onclick = function(e) { if (e.target === modal) closeEditModal(); };
    window.openEditModal = openEditModal;
    window.closeEditModal = closeEditModal;
})();
</script>
</div>