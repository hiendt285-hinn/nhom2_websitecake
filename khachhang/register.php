<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    if (empty($full_name)) {
        $error = "Vui lòng nhập họ và tên.";
    } elseif (empty($phone)) {
        $error = "Vui lòng nhập số điện thoại.";
    } elseif (empty($address)) {
        $error = "Vui lòng nhập địa chỉ.";
    } elseif ($password !== $confirm_password) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        if ($checkStmt) {
            $checkStmt->bind_param('s', $username);
            $checkStmt->execute();
            $checkStmt->store_result();
            $exists = $checkStmt->num_rows > 0;
            $checkStmt->close();

            if ($exists) {
                $error = "Tên đăng nhập đã tồn tại!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insStmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
                if ($insStmt) {
                    $insStmt->bind_param('ssssss', $username, $email, $hashed_password, $full_name, $phone, $address);
                    if ($insStmt->execute()) {
                        header('Location: login.php');
                        exit();
                    } else {
                        $error = "Đăng ký thất bại, vui lòng thử lại!";
                    }
                    $insStmt->close();
                } else {
                    $error = "Không thể chuẩn bị truy vấn đăng ký.";
                }
            }
        } else {
            $error = "Không thể kiểm tra tên đăng nhập.";
        }
    }
}

$page_title = 'Đăng ký - Sweet Cake';
include 'header.php';
?>

<div class="auth-page">
    <nav class="auth-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="account.php">Tài khoản</a>
        <span>/</span>
        <span>Đăng ký</span>
    </nav>

    <div class="auth-layout">
        <aside class="auth-side-panel">
            <h1>Tham gia Sweet Cake</h1>
            <p>Tạo tài khoản để đặt bánh online, lưu thông tin giao hàng và mua sắm nhanh hơn mỗi lần quay lại.</p>
            <ul class="auth-benefits">
                <li><i class="fas fa-user-check"></i> Lưu thông tin nhận hàng, không cần nhập lại</li>
                <li><i class="fas fa-history"></i> Xem lịch sử đơn hàng mọi lúc</li>
                <li><i class="fas fa-tags"></i> Nhận thông tin khuyến mãi mới nhất</li>
            </ul>
        </aside>

        <div class="auth-card">
            <div class="auth-card-header">
                <h2>Đăng ký tài khoản</h2>
                <p>Điền thông tin bên dưới để tạo tài khoản mới</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="auth-alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="auth-form" action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" required autocomplete="username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" required autocomplete="name"
                           value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required autocomplete="email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone">Số điện thoại</label>
                        <input type="tel" id="phone" name="phone" required autocomplete="tel"
                               placeholder="VD: 0901234567"
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Địa chỉ</label>
                    <input type="text" id="address" name="address" required autocomplete="street-address"
                           placeholder="Số nhà, đường, quận/huyện, tỉnh/thành"
                           value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Mật khẩu</label>
                        <input type="password" id="password" name="password" required autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Xác nhận mật khẩu</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn-submit">Đăng ký</button>
            </form>

            <p class="auth-switch">
                Đã có tài khoản? <a href="login.php">Đăng nhập</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
