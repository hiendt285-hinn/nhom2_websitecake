<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}
require_once 'connect.php';

// Xử lý Thêm/Sửa/Xóa khách hàng
$error = '';
$success = '';

if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($action === 'add') {
        if ($username && $password && $email && $full_name) {
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, role, is_active) VALUES (?, ?, ?, ?, ?, 'customer', 1)");
            $stmt->bind_param("sssss", $username, $hashed_pass, $email, $full_name, $phone);
            if ($stmt->execute()) {
                $success = "Thêm khách hàng thành công!";
            } else {
                $error = "Lỗi khi thêm khách hàng!";
            }
            $stmt->close();
        } else {
            $error = "Vui lòng điền đầy đủ thông tin!";
        }
    } elseif ($action === 'edit' && $id > 0) {
        if ($full_name && $email && $username) {
            if ($password) {
                $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, username=?, password=? WHERE id=? AND role='customer'");
                $stmt->bind_param("sssssi", $full_name, $email, $phone, $username, $hashed_pass, $id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, username=? WHERE id=? AND role='customer'");
                $stmt->bind_param("ssssi", $full_name, $email, $phone, $username, $id);
            }
            if ($stmt->execute()) {
                $success = "Cập nhật khách hàng thành công!";
            } else {
                $error = "Lỗi khi cập nhật khách hàng!";
            }
            $stmt->close();
        } else {
            $error = "Vui lòng điền đầy đủ thông tin!";
        }
    } elseif ($action === 'delete' && $id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='customer'");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $success = "Xóa khách hàng thành công!";
        } else {
            $error = "Lỗi khi xóa khách hàng!";
        }
        $stmt->close();
    }
}

// Lấy danh sách khách hàng
$customers = $conn->query("SELECT id, username, full_name, email, phone, is_active FROM users WHERE role='customer' ORDER BY id DESC");
?>
<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title"><i class="fas fa-users"></i> Quản lý khách hàng</h1>
        <button class="admin-btn admin-btn-primary" onclick="openForm('add')">Thêm khách hàng</button>
    </div>

    <?php if($error): ?><div class="admin-message admin-message-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if($success): ?><div class="admin-message admin-message-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>

    <div class="admin-card">
    <table class="admin-table">
        <thead><tr>
            <th>ID</th>
            <th>Username</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Điện thoại</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr></thead>
        <tbody>
        <?php if($customers && $customers->num_rows>0): ?>
            <?php while($row=$customers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id'];?></td>
                    <td><?php echo htmlspecialchars($row['username']);?></td>
                    <td><?php echo htmlspecialchars($row['full_name']);?></td>
                    <td><?php echo htmlspecialchars($row['email']);?></td>
                    <td><?php echo htmlspecialchars($row['phone']);?></td>
                    <td><?php echo $row['is_active'] ? 'Hoạt động' : 'Không hoạt động';?></td>
                    <td class="admin-action-cell">
                        <button type="button" class="admin-btn admin-btn-primary admin-btn-sm" onclick="openForm('edit','<?php echo $row['id'];?>','<?php echo htmlspecialchars($row['username'], ENT_QUOTES);?>','<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES);?>','<?php echo htmlspecialchars($row['email'], ENT_QUOTES);?>','<?php echo htmlspecialchars($row['phone'], ENT_QUOTES);?>')"><i class="fas fa-edit"></i> Sửa</button>
                        <form style="display:inline" method="post" onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $row['id'];?>">
                            <button type="submit" class="admin-btn admin-btn-danger admin-btn-sm"><i class="fas fa-trash-alt"></i> Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile;?>
        <?php else: ?>
            <tr><td colspan="7">Chưa có khách hàng nào</td></tr>
        <?php endif; ?>
    </tbody>
    </table>
    </div>
</div>

<div class="edit-modal" id="formPopup" style="display:none;">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h2 class="admin-modal-title">Khách hàng</h2>
            <button type="button" class="admin-modal-close" onclick="closeForm()" aria-label="Đóng">&times;</button>
        </div>
        <form method="post" class="admin-modal-body">
            <input type="hidden" name="action" id="action">
            <input type="hidden" name="id" id="id">
            <div class="admin-form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="admin-form-group">
                <label for="full_name">Họ tên</label>
                <input type="text" name="full_name" id="full_name" required>
            </div>
            <div class="admin-form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="admin-form-group">
                <label for="phone">Điện thoại</label>
                <input type="text" name="phone" id="phone">
            </div>
            <div class="admin-form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" name="password" id="password">
                <span class="form-hint" id="passwordHint">Để trống nếu không đổi (khi sửa)</span>
            </div>
            <div class="admin-modal-actions">
                <button type="button" class="admin-btn admin-btn-secondary" onclick="closeForm()">Hủy</button>
                <button type="submit" class="admin-btn admin-btn-primary">Lưu</button>
            </div>
        </form>
    </div>
</div>
<script>
function openForm(action, id, username, full_name, email, phone) {
    id = id || ''; username = username || ''; full_name = full_name || ''; email = email || ''; phone = phone || '';
    document.getElementById('formPopup').style.display = 'flex';
    document.getElementById('action').value = action;
    document.getElementById('id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('full_name').value = full_name;
    document.getElementById('email').value = email;
    document.getElementById('phone').value = phone;
    var hint = document.getElementById('passwordHint');
    hint.style.display = (action === 'edit') ? 'block' : 'none';
}
function closeForm() { document.getElementById('formPopup').style.display = 'none'; }
</script>
