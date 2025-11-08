<?php
// header.php - Phần header chung cho website (dựa trên HTML cung cấp)

if (!isset($_SESSION)) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savor Cake - BST Women's Day</title> <!-- Title từ HTML, có thể thay đổi động nếu cần -->
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>"> <!-- Link CSS với cache-busting -->
</head>
<body>

<header class="navbar">
    <div class="logo">
        <a href="index.php"><img src="images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Logo"></a>
    </div>
    <nav>
        <ul>
            <li><a href="products.php">Sản phẩm</a></li> 
            <li><a href="#">Tin tức</a></li> <!-- Có thể link đến trang chính sách nếu có -->
            <li><a href="#">Khuyến mại</a></li> <!-- Có thể link đến trang địa chỉ -->
            <li><a href="#">Feedback</a></li>
            <li><a href="contact.php">Liên hệ</a></li> <!-- Link đến contact.php theo gợi ý -->
            <li><a href="cart.php">Giỏ hàng (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)</a></li> <!-- Thêm giỏ hàng từ mẫu trước -->
        </ul>
    </nav>
    <!-- Thêm phần đăng nhập/đăng ký từ mẫu trước để hoàn chỉnh -->
    <div class="user-account">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span>Xin chào, <?php echo $_SESSION['username']; ?></span>
            <a href="logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="login.php">Đăng nhập</a>
            <a href="register.php">Đăng ký</a>
        <?php endif; ?>
    </div>
    <!-- Thêm thanh tìm kiếm từ mẫu trước -->
    <div class="search-bar">
        <form action="products.php" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm bánh ngọt...">
            <button type="submit">Tìm</button>
        </form>
    </div>
</header>

