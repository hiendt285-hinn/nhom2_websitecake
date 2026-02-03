<?php

if (!isset($_SESSION)) {
    session_start();
}

$css_file = __DIR__ . '/style.css';
$css_version = file_exists($css_file) ? filemtime($css_file) : time();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Savor Cake - BST Women's Day</title>

    <link rel="stylesheet" href="style.css?v=<?php echo $css_version; ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<div class="topbar">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">

        <div class="topbar-left d-flex flex-wrap align-items-center gap-3">
            <span class="topbar-item"><i class="bi bi-telephone"></i> 1900 636 302</span>
            <span class="topbar-item"><i class="bi bi-envelope"></i> sweetcake05@gmail.com</span>
        </div>

        <div class="topbar-right d-flex flex-wrap align-items-center gap-3">
            <?php if (isset($_SESSION['user'])): ?>
                <span class="topbar-link"><i class="bi bi-person-check"></i>
                    Xin chào, <?php echo $_SESSION['user']['username']; ?>
                </span>
                <a href="logout.php" class="topbar-link">Đăng xuất</a>
            <?php else: ?>
                <a href="account.php" class="topbar-link"><i class="bi bi-person"></i> Tài khoản</a>
                <a href="login.php" class="topbar-link">Đăng nhập</a>
                <a href="register.php" class="topbar-link">Đăng ký</a>
            <?php endif; ?>
        </div>

    </div>
</div>

<header class="site-header">

    <nav class="navbar">
        <div class="logo">
            <a href="index.php"><img src="../images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Logo"></a>
        </div>
        <ul>
            <li><a href="products.php">Sản phẩm</a></li>
            <li><a href="policy.php">Chính sách</a></li>
            <li><a href="feedback.php">Feedback</a></li>
            <li><a href="contact.php">Liên hệ</a></li>
            <li><a href="promotion.php">Khuyến mãi</a></li>
            <li>
                <a href="cart.php">
                    Giỏ hàng (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)
                </a>
            </li>
        </ul>
    </nav>

    <div class="search-bar">
        <form action="products.php" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm bánh ngọt..." required>
            <button type="submit">Tìm</button>
        </form>
    </div>

</header>