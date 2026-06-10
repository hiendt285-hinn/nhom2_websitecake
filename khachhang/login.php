<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $redirect = 'index.php';
            if (!empty($_POST['return'])) {
                $return = trim($_POST['return']);
                if (preg_match('#^[a-z0-9_\-\./]+\.php(\?.*)?$#i', $return) && strpos($return, '//') === false) {
                    $redirect = $return;
                }
            } elseif (!empty($_GET['return'])) {
                $return = trim($_GET['return']);
                if (preg_match('#^[a-z0-9_\-\./]+\.php(\?.*)?$#i', $return) && strpos($return, '//') === false) {
                    $redirect = $return;
                }
            }
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
        }
    } else {
        $error = "Không thể chuẩn bị truy vấn đăng nhập.";
    }
}

$page_title = 'Đăng nhập - Sweet Cake';
include 'header.php';

$register_href = 'register.php';
if (!empty($_GET['return'])) {
    $register_href .= '?return=' . urlencode($_GET['return']);
}
?>

<div class="auth-page">
    <nav class="auth-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <a href="account.php">Tài khoản</a>
        <span>/</span>
        <span>Đăng nhập</span>
    </nav>

    <div class="auth-layout">
        <aside class="auth-side-panel">
            <h1>Chào mừng trở lại Sweet Cake</h1>
            <p>Đăng nhập để đặt bánh nhanh hơn, theo dõi đơn hàng và nhận ưu đãi dành riêng cho bạn.</p>
            <ul class="auth-benefits">
                <li><i class="fas fa-birthday-cake"></i> Bánh tươi mỗi ngày, đặt online tiện lợi</li>
                <li><i class="fas fa-box"></i> Theo dõi trạng thái đơn hàng dễ dàng</li>
                <li><i class="fas fa-truck"></i> Thanh toán nhanh, giao hàng tận nơi</li>
            </ul>
        </aside>

        <div class="auth-card">
            <div class="auth-card-header">
                <h2>Đăng nhập</h2>
                <p>Nhập thông tin tài khoản của bạn</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="auth-alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="auth-form" action="login.php" method="POST">
                <?php if (!empty($_GET['return'])): ?>
                <input type="hidden" name="return" value="<?php echo htmlspecialchars($_GET['return']); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" required autocomplete="username"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-submit">Đăng nhập</button>
            </form>

            <p class="auth-switch">
                Chưa có tài khoản? <a href="<?php echo htmlspecialchars($register_href); ?>">Đăng ký ngay</a>
            </p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
