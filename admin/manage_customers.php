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

<style>
/* Modal Styles */
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
}

.admin-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 2px solid #f0f0f0;
}

.admin-modal-title {
    margin: 0;
    font-size: 20px;
    color: #9a7b5a;
    font-weight: 600;
}

.admin-modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #999;
    transition: color 0.3s;
}

.admin-modal-close:hover {
    color: #e74c3c;
}

.admin-modal-body {
    padding: 25px;
}

/* Form Groups */
.admin-form-group {
    margin-bottom: 20px;
}

.admin-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
    font-size: 14px;
}

.admin-form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.admin-form-group input:focus {
    border-color: #9a7b5a;
    outline: none;
    box-shadow: 0 0 0 3px rgba(139,69,19,0.1);
}

.form-hint {
    display: block;
    font-size: 12px;
    color: #999;
    margin-top: 5px;
    font-style: italic;
}

/* Modal Actions */
.admin-modal-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 2px solid #f0f0f0;
}

/* Button Styles - Đồng bộ với các trang khác */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
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

.btn i {
    font-size: 14px;
}

.btn-sm {
    min-width: 80px;
    height: 36px;
    padding: 8px 16px;
    font-size: 13px;
}
.btn-icon {
    width: 36px;
    min-width: 36px;
    height: 36px;
    padding: 0;
    border-radius: 8px;
    gap: 0;
}

.btn-primary {
    background: #9a7b5a;
    color: white;
}

.btn-primary:hover {
    background: #A0522D;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(139,69,19,0.2);
}

.btn-secondary {
    background: #95a5a6;
    color: white;
}

.btn-secondary:hover {
    background: #7f8c8d;
    transform: translateY(-2px);
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    transform: translateY(-2px);
}

/* Status Badge */
.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #e8f5e9;
    color: #27ae60;
    border-left: 3px solid #27ae60;
}

.status-inactive {
    background: #ffebee;
    color: #e74c3c;
    border-left: 3px solid #e74c3c;
}

/* Action Cell */
.admin-action-cell {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
}

.btn-tooltip { position: relative; }
.btn-tooltip::after {
    content: attr(data-tooltip);
    position: absolute;
    left: 50%;
    bottom: calc(100% + 8px);
    transform: translateX(-50%);
    background: #2c3e50;
    color: #fff;
    border-radius: 6px;
    font-size: 11px;
    padding: 4px 8px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}
.btn-tooltip:hover::after { opacity: 1; visibility: visible; }

/* Table Styles */
.admin-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.admin-table {
    width: 100%;
    border-collapse: collapse;
}

.admin-table th {
    background: #9a7b5a;
    color: white;
    font-weight: 600;
    padding: 15px 12px;
    font-size: 14px;
    text-align: left;
    white-space: nowrap;
}

.admin-table td {
    padding: 15px 12px;
    border-bottom: 1px solid #e0e0e0;
    font-size: 14px;
    vertical-align: middle;
}

.admin-table tbody tr:hover td {
    background: #f9f6f2;
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
    font-size: 24px;
    color: #9a7b5a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Messages */
.admin-message {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.admin-message-success {
    background: #e8f5e9;
    color: #27ae60;
    border-left: 4px solid #27ae60;
}

.admin-message-error {
    background: #ffebee;
    color: #e74c3c;
    border-left: 4px solid #e74c3c;
}

/* Responsive */
@media (max-width: 768px) {
    .admin-page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .admin-action-cell {
        flex-direction: column;
    }
    
    .btn-sm {
        width: 100%;
        min-width: 100%;
    }
    
    .admin-modal-box {
        width: 95%;
        margin: 20px;
    }
    
    .admin-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="admin-content">
    <div class="admin-page-header">
        <h1 class="admin-page-title">
            <i class="fas fa-users"></i> 
            Quản lý khách hàng
        </h1>
        <button class="btn btn-primary" onclick="openForm('add')">
            <i class="fas fa-plus-circle"></i> Thêm khách hàng
        </button>
    </div>

    <?php if($error): ?>
    <div class="admin-message admin-message-error">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    
    <?php if($success): ?>
    <div class="admin-message admin-message-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="admin-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if($customers && $customers->num_rows > 0): ?>
                    <?php while($row = $customers->fetch_assoc()): ?>
                        <tr>
                            <td><strong>#<?php echo $row['id']; ?></strong></td>
                            <td>
                                <span class="status-badge" style="background: #f0f0f0; color: #333; border-left-color: #8B4513;">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($row['username']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td>
                                <span style="display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-envelope" style="color: #8B4513;"></i>
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-phone" style="color: #8B4513;"></i>
                                    <?php echo htmlspecialchars($row['phone'] ?: 'Chưa cập nhật'); ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $row['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <i class="fas <?php echo $row['is_active'] ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                                    <?php echo $row['is_active'] ? 'Hoạt động' : 'Không hoạt động'; ?>
                                </span>
                            </td>
                            <td class="admin-action-cell">
                                <!-- Button Sửa - fixed size -->
                                <button type="button" 
                                        class="btn btn-primary btn-sm btn-icon btn-tooltip" 
                                        data-tooltip="Chỉnh sửa"
                                        onclick="openForm('edit','<?php echo $row['id'];?>','<?php echo htmlspecialchars($row['username'], ENT_QUOTES);?>','<?php echo htmlspecialchars($row['full_name'], ENT_QUOTES);?>','<?php echo htmlspecialchars($row['email'], ENT_QUOTES);?>','<?php echo htmlspecialchars($row['phone'], ENT_QUOTES);?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <!-- Button Xóa - fixed size -->
                                <form style="display:inline" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách hàng này? Hành động này không thể hoàn tác.');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $row['id'];?>">
                                    <button type="submit" class="btn btn-danger btn-sm btn-icon btn-tooltip" data-tooltip="Xóa">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #7f8c8d;">
                            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; display: block; opacity: 0.3;"></i>
                            <p style="margin: 0;">Chưa có khách hàng nào trong hệ thống</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div class="edit-modal" id="formPopup" style="display:none;">
    <div class="admin-modal-box">
        <div class="admin-modal-header">
            <h2 class="admin-modal-title">
                <i class="fas fa-user"></i>
                <span id="modalTitle">Thêm khách hàng mới</span>
            </h2>
            <button type="button" class="admin-modal-close" onclick="closeForm()" aria-label="Đóng">&times;</button>
        </div>
        <form method="post" class="admin-modal-body">
            <input type="hidden" name="action" id="action">
            <input type="hidden" name="id" id="id">
            
            <div class="admin-form-group">
                <label for="username">
                    <i class="fas fa-user-circle"></i> Username <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text" name="username" id="username" placeholder="Nhập username" required>
            </div>
            
            <div class="admin-form-group">
                <label for="full_name">
                    <i class="fas fa-id-card"></i> Họ tên <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text" name="full_name" id="full_name" placeholder="Nhập họ tên đầy đủ" required>
            </div>
            
            <div class="admin-form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email <span style="color: #e74c3c;">*</span>
                </label>
                <input type="email" name="email" id="email" placeholder="example@email.com" required>
            </div>
            
            <div class="admin-form-group">
                <label for="phone">
                    <i class="fas fa-phone"></i> Điện thoại
                </label>
                <input type="text" name="phone" id="phone" placeholder="Số điện thoại">
            </div>
            
            <div class="admin-form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Mật khẩu
                </label>
                <input type="password" name="password" id="password" placeholder="Nhập mật khẩu">
                <span class="form-hint" id="passwordHint">
                    <i class="fas fa-info-circle"></i> Để trống nếu không đổi (khi sửa)
                </span>
            </div>
            
            <div class="admin-modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeForm()">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openForm(action, id, username, full_name, email, phone) {
    id = id || ''; 
    username = username || ''; 
    full_name = full_name || ''; 
    email = email || ''; 
    phone = phone || '';
    
    document.getElementById('formPopup').style.display = 'flex';
    document.getElementById('action').value = action;
    document.getElementById('id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('full_name').value = full_name;
    document.getElementById('email').value = email;
    document.getElementById('phone').value = phone;
    
    var modalTitle = document.getElementById('modalTitle');
    var hint = document.getElementById('passwordHint');
    
    if (action === 'edit') {
        modalTitle.innerHTML = 'Chỉnh sửa khách hàng';
        hint.style.display = 'block';
        document.getElementById('password').required = false;
    } else {
        modalTitle.innerHTML = 'Thêm khách hàng mới';
        hint.style.display = 'none';
        document.getElementById('password').required = true;
    }
}

function closeForm() { 
    document.getElementById('formPopup').style.display = 'none';
}

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    var modal = document.getElementById('formPopup');
    if (event.target == modal) {
        closeForm();
    }
}
</script>