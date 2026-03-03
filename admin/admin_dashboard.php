<?php ob_start(); ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html {
            font-family: 'Open Sans', sans-serif;
            background: #f5f5f5;
            color: #2c3e50;
            overflow-x: hidden;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .admin-layout {
            display: flex;
            flex: 1 0 auto;
            min-height: 0;
            width: 100%;
        }
        .sidebar {
            width: 220px;
            min-width: 220px;
            flex-shrink: 0;
            background: #fff;
            padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.04);
            align-self: stretch;
        }
        .sidebar ul { list-style: none; padding: 0; margin: 0; }
        .sidebar li { list-style: none; }
        .sidebar a {
            text-decoration: none;
            color: #2c2c2c;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 15px;
            border-radius: 8px;
            transition: 0.2s;
        }
        .sidebar a:hover { background: #f5f5f5; color: #9a7b5a; }
        .sidebar i { width: 20px; text-align: center; }

        .content-wrapper {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            padding: 30px;
            background: transparent;
        }
        .content-inner {
            flex: 1 1 auto;
            min-height: 200px;
        }
        .footer {
            background: #9a7b5a;
            color: #fff;
            text-align: center;
            padding: 15px;
            font-size: 14px;
            flex-shrink: 0;
        }
    </style>
    <?php
    include 'admin_header.php';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị - Sweet Cake</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_style.css">
    <?php if (isset($_GET['page']) && $_GET['page'] === 'reports'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <?php endif; ?>
    </head>
    <body>
    <div class="admin-layout">
        <aside class="sidebar">
            <ul>
                <li><a href="admin_dashboard.php?page=sizes"><i class="fas fa-expand-arrows-alt"></i> Quản lý cỡ bánh</a></li>
                <li><a href="admin_dashboard.php?page=flavors"><i class="fas fa-cube"></i> Quản lý hương vị</a></li>
                <li><a href="admin_dashboard.php?page=producttype"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
                <li><a href="admin_dashboard.php?page=products"><i class="fas fa-cake-candles"></i> Quản lý sản phẩm</a></li>
                <li><a href="admin_dashboard.php?page=customers"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
                <li><a href="admin_dashboard.php?page=orders"><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</a></li>
                <li><a href="admin_dashboard.php?page=auto_orders"><i class="fas fa-sync-alt"></i> Tự động cập nhật đơn</a></li>
                <li><a href="admin_dashboard.php?page=shipping"><i class="fas fa-truck"></i> Quản lý giao hàng</a></li>
                <li><a href="admin_dashboard.php?page=contact"><i class="fas fa-envelope"></i> Quản lý liên hệ</a></li>
                <li><a href="admin_dashboard.php?page=news"><i class="fas fa-newspaper"></i> Quản lý tin tức</a></li>
                <li><a href="admin_dashboard.php?page=promotions"><i class="fas fa-tag"></i> Mã giảm giá</a></li>
                <li><a href="admin_dashboard.php?page=reports"><i class="fas fa-chart-line"></i> Báo cáo</a></li>
            </ul>
        </aside>
        <main class="content-wrapper">
            <div class="content-inner">
                 <?php
                    if (isset($_GET['page'])) {
                        switch ($_GET['page']) {
                        case 'customers':
                        include 'manage_customers.php';
                        break;
                        case 'sizes':
                        include 'manage_sizes.php';
                        break;
                        case 'flavors':
                        include 'manage_flavors.php';
                        break;
                        case 'producttype':
                        include 'manage_producttype.php';
                        break;
                        case 'products':
                        include 'manage_products.php';
                        break;
                        case 'orders':
                        include 'manage_orders.php';
                        break;
                        case 'auto_orders':
                        include 'auto_update_orders.php';
                        break;
                        case 'shipping':
                        include 'manage_shipping.php';
                        break;
                        case 'reports':
                        include 'manage_reports.php';
                        break;
                        case 'contact':
                        include 'manage_contact.php';
                        break;
                        case 'news':
                        include 'manage_news.php';
                        break;
                        case 'promotions':
                        include 'manage_promotions.php';
                        break;
                    default:
                        echo '<h3>Chào mừng Admin</h3>';
                }
            }
        ?>
            </div>
        </main>
    </div>
    <footer class="footer">
                &copy; 2025 Quản trị website - All rights reserved
    </footer>
    </body>
    </html>