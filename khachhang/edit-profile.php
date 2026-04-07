<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once 'connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

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

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($full_name)) {
        $error = 'Vui lòng nhập họ và tên.';
    } elseif (empty($phone)) {
        $error = 'Vui lòng nhập số điện thoại.';
    } elseif (empty($address)) {
        $error = 'Vui lòng nhập địa chỉ.';
    } else {
        $update_sql = "UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?";
        if ($stmt = $conn->prepare($update_sql)) {
            $stmt->bind_param('sssi', $full_name, $phone, $address, $user_id);
            if ($stmt->execute()) {
                header('Location: account.php?updated=1');
                exit;
            }
            $error = 'Lỗi khi cập nhật. Vui lòng thử lại.';
            $stmt->close();
        } else {
            $error = 'Lỗi hệ thống. Vui lòng thử lại sau.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin - Sweet Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .edit-profile-page {
            max-width: 680px;
            margin: 0 auto;
            padding: 40px 20px 60px;
        }
        .edit-profile-breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #666;
        }
        .edit-profile-breadcrumb a {
            color: var(--main-brown, #9a7b5a);
            text-decoration: none;
            font-weight: 500;
        }
        .edit-profile-breadcrumb a:hover {
            text-decoration: underline;
        }
        .edit-profile-breadcrumb i {
            font-size: 10px;
            color: #999;
        }
        .edit-profile-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(139, 69, 19, 0.08);
            border: 1px solid rgba(139, 69, 19, 0.1);
            padding: 36px 40px;
        }
        .edit-profile-card h1 {
            font-size: 24px;
            color: var(--main-brown, #9a7b5a);
            margin-bottom: 8px;
            font-weight: 700;
        }
        .edit-profile-card .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 28px;
        }
        .edit-profile-message {
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .edit-profile-message.error {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        .edit-profile-message.success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        .edit-profile-message i {
            font-size: 18px;
        }
        .form-group {
            margin-bottom: 22px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 8px;
        }
        .form-group .input-wrap {
            position: relative;
        }
        .form-group .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
            width: 20px;
            text-align: center;
        }
        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 14px 14px 14px 46px;
            border: 2px solid #e8e4df;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--main-brown, #9a7b5a);
            box-shadow: 0 0 0 3px rgba(154, 123, 90, 0.15);
        }
        .form-group input:disabled {
            background: #f5f5f5;
            color: #666;
            cursor: not-allowed;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
            padding-left: 46px;
        }
        .form-group .hint {
            font-size: 12px;
            color: #999;
            margin-top: 6px;
        }
        .edit-profile-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-top: 32px;
            flex-wrap: wrap;
        }
        .btn-update {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            background: var(--main-brown, #9a7b5a);
            color: #fff;
        }
        .btn-update:hover {
            background: var(--brown-light, #A0522D);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(154, 123, 90, 0.3);
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 24px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            color: #666;
            background: #f5f5f5;
            border: 2px solid #e8e4df;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
        }
        .btn-back:hover {
            background: #eee;
            color: var(--main-brown, #9a7b5a);
            border-color: var(--main-brown, #9a7b5a);
        }
        @media (max-width: 768px) {
            .edit-profile-page { padding: 24px 16px 48px; }
            .edit-profile-card { padding: 24px 20px; }
            .edit-profile-actions { flex-direction: column; align-items: stretch; }
            .btn-update, .btn-back { justify-content: center; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="edit-profile-page">
    <nav class="edit-profile-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
        <i class="fas fa-chevron-right"></i>
        <a href="account.php">Tài khoản</a>
        <i class="fas fa-chevron-right"></i>
        <span>Chỉnh sửa thông tin</span>
    </nav>

    <div class="edit-profile-card">
        <h1><i class="fas fa-user-edit"></i> Chỉnh sửa thông tin cá nhân</h1>
        <p class="subtitle">Cập nhật họ tên, số điện thoại và địa chỉ. Email không thể thay đổi.</p>

        <?php if ($error): ?>
            <div class="edit-profile-message error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>" required placeholder="Nhập họ và tên">
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="text" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled aria-readonly="true">
                </div>
                <p class="hint">Email đăng ký không thể chỉnh sửa.</p>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <div class="input-wrap">
                    <i class="fas fa-phone"></i>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>" required placeholder="Nhập số điện thoại">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <div class="input-wrap">
                    <i class="fas fa-map-marker-alt"></i>
                    <textarea id="address" name="address" required placeholder="Nhập địa chỉ (số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố)"><?php echo htmlspecialchars($user['address'] ?: ''); ?></textarea>
                </div>
            </div>

            <div class="edit-profile-actions">
                <button type="submit" class="btn-update">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
                <a href="account.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Quay lại tài khoản
                </a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
