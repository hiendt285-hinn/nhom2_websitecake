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

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản lý khách hàng - Savor Cake</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body {font-family: 'Open Sans', sans-serif; background:#F5F1E8; margin:0; padding:20px;}
    table {width:100%; border-collapse: collapse; margin-top:10px;background:#ffffff; border-radius:8px; overflow:hidden;}
    th, td {border-bottom:1px solid #eee; padding:10px 12px; text-align:left; font-size:14px;}
    th {background:#f9f6f2; color:#333; font-weight:600;}
    .btn {padding:6px 10px; border:none; border-radius:999px; cursor:pointer; font-size:13px; font-weight:600;}
    .btn-edit {background:#8B6F47; color:white;}
    .btn-delete {background:#d32f2f; color:white;}
    .form-popup {display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); border:1px solid #ccc; background:white; padding:20px; z-index:9; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.15);}
    .form-container input[type=text], .form-container input[type=password], .form-container input[type=email] {width:100%; padding:8px; margin:5px 0 10px 0; border:1px solid #ccc; border-radius:5px;}
    .form-container .btn {width:100%; margin-top:5px;}
    .message {padding:10px; margin-bottom:10px; border-radius:5px; font-size:14px;}
    .success {background:#d4edda; color:#155724;}
    .error {background:#f8d7da; color:#721c24;}
</style>
<script>
function openForm(action, id='', username='', full_name='', email='', phone='') {
    document.getElementById('formPopup').style.display = 'block';
    document.getElementById('action').value = action;
    document.getElementById('id').value = id;
    document.getElementById('username').value = username;
    document.getElementById('full_name').value = full_name;
    document.getElementById('email').value = email;
    document.getElementById('phone').value = phone;
    if(action==='edit'){ document.getElementById('password').placeholder='Để trống nếu không đổi'; }
    else { document.getElementById('password').placeholder='Mật khẩu'; }
}

function closeForm() {
    document.getElementById('formPopup').style.display = 'none';
}
</script>
</head>
<body>
<div style="padding:0 0 20px 0;">
    <h2 style="color:#8B6F47;">Quản lý khách hàng</h2>

    <?php if($error): ?><div class="message error"><?php echo $error;?></div><?php endif; ?>
    <?php if($success): ?><div class="message success"><?php echo $success;?></div><?php endif; ?>

    <button class="btn btn-edit" onclick="openForm('add')">Thêm khách hàng</button>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Điện thoại</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
        <?php if($customers && $customers->num_rows>0): ?>
            <?php while($row=$customers->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id'];?></td>
                    <td><?php echo htmlspecialchars($row['username']);?></td>
                    <td><?php echo htmlspecialchars($row['full_name']);?></td>
                    <td><?php echo htmlspecialchars($row['email']);?></td>
                    <td><?php echo htmlspecialchars($row['phone']);?></td>
                    <td><?php echo $row['is_active'] ? 'Hoạt động' : 'Không hoạt động';?></td>
                    <td>
                        <button class="btn btn-edit" onclick="openForm('edit','<?php echo $row['id'];?>','<?php echo htmlspecialchars($row['username']);?>','<?php echo htmlspecialchars($row['full_name']);?>','<?php echo htmlspecialchars($row['email']);?>','<?php echo htmlspecialchars($row['phone']);?>')">Sửa</button>
                        <form style="display:inline" method="post" onsubmit="return confirm('Bạn có chắc muốn xóa khách hàng này?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $row['id'];?>">
                            <button type="submit" class="btn btn-delete">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile;?>
        <?php else: ?>
            <tr><td colspan="7">Chưa có khách hàng nào</td></tr>
        <?php endif; ?>
    </table>
</div>

<div class="form-popup" id="formPopup">
    <form method="post" class="form-container">
        <h3>Khách hàng</h3>
        <input type="hidden" name="action" id="action">
        <input type="hidden" name="id" id="id">
        <label>Username</label>
        <input type="text" name="username" id="username" required>
        <label>Họ tên</label>
        <input type="text" name="full_name" id="full_name" required>
        <label>Email</label>
        <input type="email" name="email" id="email" required>
        <label>Điện thoại</label>
        <input type="text" name="phone" id="phone">
        <label>Mật khẩu</label>
        <input type="password" name="password" id="password">
        <button type="submit" class="btn btn-edit">Lưu</button>
        <button type="button" class="btn btn-delete" onclick="closeForm()">Hủy</button>
    </form>
</div>
</body>
</html>
