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

$page_title = 'Chỉnh sửa thông tin - Sweet Cake';
include 'header.php';
?>

<div class="content-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="account.php">Tài khoản</a>
        <span>/</span>
        <span>Chỉnh sửa thông tin</span>
    </nav>

    <div class="content-page-header">
        <h1>Chỉnh sửa thông tin</h1>
        <p>Cập nhật họ tên, số điện thoại và địa chỉ giao hàng</p>
    </div>

    <nav class="account-nav" aria-label="Tài khoản">
        <a href="account.php"><i class="fas fa-user"></i> Thông tin</a>
        <a href="edit-profile.php" class="is-active"><i class="fas fa-user-edit"></i> Chỉnh sửa</a>
        <a href="order_history.php"><i class="fas fa-history"></i> Lịch sử đơn hàng</a>
    </nav>

    <div class="account-card account-form-card">
        <?php if ($error): ?>
        <div class="content-alert-error" role="alert">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="post" class="account-form">
            <div class="form-group">
                <label for="full_name">Họ và tên</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?>" required placeholder="Nhập họ và tên">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled aria-readonly="true">
                <p class="hint">Email đăng ký không thể chỉnh sửa.</p>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?: ''); ?>" required placeholder="Nhập số điện thoại">
            </div>

            <div class="form-group">
                <label for="address">Địa chỉ</label>
                <textarea id="address" name="address" required placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố"><?php echo htmlspecialchars($user['address'] ?: ''); ?></textarea>
            </div>

            <div class="account-form-actions">
                <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Cập nhật</button>
                <a href="account.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
