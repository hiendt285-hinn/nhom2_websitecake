<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once 'connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Lấy thông tin người dùng hiện tại
$user_sql = "SELECT id, username, email, full_name, phone, address FROM users WHERE id = ? LIMIT 1";
$user = null;
if ($stmt = $conn->prepare($user_sql)) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;
    $stmt->close();
}

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Xử lý form update
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    // Email không cho edit ở đây, nếu muốn thì thêm

    if (empty($full_name)) {
        $error = 'Vui lòng nhập họ và tên.';
    } elseif (empty($phone)) {
        $error = 'Vui lòng nhập số điện thoại.';
    } elseif (empty($address)) {
        $error = 'Vui lòng nhập địa chỉ.';
    } else {
        // Update DB
        $update_sql = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?";
        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param('sssi', $full_name, $phone, $address, $user_id);
            if ($stmt->execute()) {
                $success = 'Cập nhật thông tin thành công!';
                // Update session nếu cần, nhưng ở đây không lưu session user info
                header('Location: account.php');
                exit;
            } else {
                $error = 'Lỗi khi cập nhật: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = 'Lỗi chuẩn bị query: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin - Savor Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .edit-page { max-width: 800px; margin: 30px auto; padding: 0 20px; font-family: 'Poppins', sans-serif; }
        .page-header { text-align: center; margin-bottom: 40px; }
        .page-header h1 { font-size: 32px; color: #5D4037; font-weight: 700; margin-bottom: 10px; }
        .edit-form { background: #fffaf0; padding: 30px; border-radius: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .edit-form label { display: block; font-weight: 600; color: #5D4037; font-size: 14px; margin-bottom: 5px; }
        .edit-form input[type="text"], .edit-form textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 16px; margin-bottom: 20px; }
        .edit-form textarea { resize: vertical; height: 100px; }
        .btn { color: white; border: none; padding: 12px 24px; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.3s; display: inline-block; text-align: center; min-width: 120px; text-decoration: none; }
        .btn-submit { background: #4caf50; }
        .btn-submit:hover { background: #388e3c; }
        .btn-back { background: #2196f3; margin-left: 10px; }
        .btn-back:hover { background: #1976d2; }
        .message { margin-bottom: 20px; padding: 10px; border-radius: 8px; font-weight: 600; }
        .error { background: #ffebee; color: #c62828; }
        .success { background: #e8f5e9; color: #2e7d32; }
        @media (max-width: 768px) { .edit-form { padding: 20px; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="edit-page">
    <div class="page-header">
        <h1>Chỉnh sửa thông tin cá nhân</h1>
    </div>

    <div class="edit-form">
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="full_name">Họ và tên</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>" required>

            <label for="email">Email (không thể chỉnh sửa)</label>
            <input type="text" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>

            <label for="phone">Số điện thoại</label>
            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>" required>

            <label for="address">Địa chỉ</label>
            <textarea id="address" name="address" required><?php echo htmlspecialchars($user['address'] ?: ''); ?></textarea>

            <button type="submit" class="btn btn-submit">Cập nhật</button>
            <a href="account.php" class="btn btn-back">Quay lại</a>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>