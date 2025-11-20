<style>
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
    }
    
    body { 
        font-family: 'Poppins', sans-serif; 
        background: #fffaf0; 
        color: #333;
        
        display: flex;
        flex-direction: column; 
        min-height: 100vh; 
    }
    

    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; }

    .header {
      background:#5a8b56;
      color: #ff5f9e;
      padding:10px 30px;; 
      
      font-size: 20px;
      font-weight: bold;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      border-radius: 0 0 25px 25px;
    }
    .header a {
      color: #ff5f9e;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
      padding: 0 30px;
    }
    .header a:hover {
      color: #ff90c2;
    }
    

    .menu1>li{float:left;}
    .menu1>li:hover>ul.menu2{
        display:block;
        }
    .menu2{display:none;
            position:absolute;
            margin-left:-20px   
    }
    .menu2 ul{flex-direction:column;}
    ul li:hover > ul {
      display: block !important;
    }


    .sidebar {
        background: #fffaf0;
        padding: 20px;
        min-height: auto; 
        box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
        width: 100%; 
        box-sizing: border-box;
        margin-bottom: 20px; 
        border-radius: 0;
    }


    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex; 
        flex-wrap: wrap; 
        justify-content: space-around; 
        gap: 10px; 
    }


    .sidebar li {
        width: 30%; 
        min-width: 200px; 
        box-sizing: border-box; 
        margin-bottom: 12px; 
    }

    .sidebar a {
        text-decoration: none;
        color: #333;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        border-radius: 8px;
        transition: 0.3s;
    }

    .sidebar a:hover {
        background: #ff5f9e;
        color: white;
    }

    .sidebar i {
        width: 20px;
        text-align: center;
    }
    

    .content {
      padding: 30px;
      background: #fffaf0;
      flex-grow: 1; 

    }
    
    .footer {
      background: #5a8b56;
      color: white;
      text-align: center;
      padding: 15px;
      font-size: 14px;Viền 
      flex-shrink: 0; 
    }
</style>
<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: login_admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quản trị - Savor Cake</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<table border="0">
    <tr>
        <td colspan="2" class="header" style="position:relative;">
            <img src="../images/35-mau-thiet-ke-logo-tiem-banh-dep-5-removebg-preview.png" alt="Logo" style="height:40px; vertical-align:middle; margin-right:10px;">
            Savor Cake
            <span style="float:right; font-weight:normal;">
                <a href="logout_admin.php">Đăng xuất</a>
            </span>
        </td>
    </tr>    <tr>
        <td width="220" valign="top" class="sidebar">
            <ul>
            <li><a href="manage_producttype.php"><i class="fas fa-list"></i> Quản lý danh mục</a></li>
            <li><a href="manage_products.php"><i class="fas fa-cake-candles"></i> Quản lý sản phẩm</a></li>
            <li><a href="manage_customers.php"><i class="fas fa-users"></i> Quản lý khách hàng</a></li>
            <li><a href="manage_orders.php"><i class="fas fa-shopping-cart"></i> Quản lý đơn hàng</a></li>
            <li><a href="manage_shipping.php"><i class="fas fa-truck"></i> Quản lý giao hàng</a></li>
            <li><a href="manage_reports.php"><i class="fas fa-chart-line"></i> Báo cáo</a></li>
            </ul>
        </td>
        <td class="content">
            
        </td>
    </tr>
    <tr>
        <td colspan="2" class="footer">
            &copy; 2025 Quản trị website - All rights reserved
        </td>
    </tr>
</table>
</body>
</html>