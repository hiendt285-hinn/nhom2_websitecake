<?php

if (!isset($_SESSION)) {
    session_start();
}

$nav_cur = basename($_SERVER['SCRIPT_NAME'] ?? '');
$nav_products_active = in_array($nav_cur, ['products.php', 'product-detail.php'], true);
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;

$css_file = __DIR__ . '/style.css';
$css_version = file_exists($css_file) ? filemtime($css_file) : time();
$page_title = $page_title ?? 'Sweet Cake - Bánh ngọt tươi mỗi ngày';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <link rel="stylesheet" href="style.css?v=<?php echo $css_version; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<div class="topbar">
    <div class="container d-flex flex-wrap justify-content-between align-items-center">
        <div class="topbar-left d-flex flex-wrap align-items-center gap-3">
            <span class="topbar-item"><i class="bi bi-telephone"></i> 1900 636 302</span>
            <span class="topbar-item"><i class="bi bi-envelope"></i> sweetcake05@gmail.com</span>
        </div>
        <div class="topbar-right d-flex flex-wrap align-items-center gap-3">
            <?php if (!empty($_SESSION['user_id']) || !empty($_SESSION['username'])): ?>
                <a href="account.php" class="topbar-link"><i class="bi bi-person-check"></i>
                    Xin chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Khách'); ?>
                </a>
                <a href="logout.php" class="topbar-link"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
            <?php else: ?>
                <a href="account.php" class="topbar-link"><i class="bi bi-person"></i> Tài khoản</a>
                <a href="login.php" class="topbar-link">Đăng nhập</a>
                <a href="register.php" class="topbar-link">Đăng ký</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<header class="site-header">
    <div class="header-inner">
        <div class="logo">
            <a href="index.php"><img src="../images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Sweet Cake"></a>
        </div>

        <button type="button" class="nav-toggle" id="navToggle" aria-label="Mở menu" aria-expanded="false">
            <i class="fas fa-bars"></i>
        </button>

        <nav class="navbar" id="mainNav">
            <ul>
                <li><a href="index.php" class="<?php echo $nav_cur === 'index.php' ? 'is-active' : ''; ?>">Trang chủ</a></li>
                <li><a href="products.php" class="<?php echo $nav_products_active ? 'is-active' : ''; ?>">Sản phẩm</a></li>
                <li><a href="promotion.php" class="<?php echo $nav_cur === 'promotion.php' ? 'is-active' : ''; ?>">Khuyến mãi</a></li>
                <li><a href="news.php" class="<?php echo $nav_cur === 'news.php' ? 'is-active' : ''; ?>">Tin tức</a></li>
                <li><a href="contact.php" class="<?php echo $nav_cur === 'contact.php' ? 'is-active' : ''; ?>">Liên hệ</a></li>
                <li>
                    <a href="cart.php" class="nav-cart-link <?php echo $nav_cur === 'cart.php' ? 'is-active' : ''; ?>">
                        <i class="fas fa-shopping-bag"></i> Giỏ hàng
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-badge"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="search-bar">
            <form action="products.php" method="GET">
                <input type="text" name="search" placeholder="Tìm bánh yêu thích..." aria-label="Tìm kiếm sản phẩm">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </div>

    <div class="header-search-mobile">
        <form action="products.php" method="GET">
            <input type="text" name="search" placeholder="Tìm bánh yêu thích..." aria-label="Tìm kiếm sản phẩm">
            <button type="submit"><i class="fas fa-search"></i></button>
        </form>
    </div>
</header>
