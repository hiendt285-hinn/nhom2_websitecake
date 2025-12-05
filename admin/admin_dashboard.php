<style>
    * { 
        margin: 0; 
        padding: 0; 
        box-sizing: border-box; 
    }
    
    body { 
        font-family: 'Open Sans', sans-serif; 
        background: #F5F1E8; 
        color: #333;
        display: flex;
        flex-direction: column; 
        min-height: 100vh; 
    }
    

    table { width: 100%; border-collapse: collapse; }
    td { vertical-align: top; }
    

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
        background: #ffffff;
        padding: 20px;
        min-height: auto; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.06); 
        width: 100%; 
        box-sizing: border-box;
        margin-bottom: 20px; 
        border-radius: 12px;
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
        background: #F5F1E8;
        color: black;
    }

    .sidebar i {
        width: 20px;
        text-align: center;
    }
    

    .content {
      padding: 30px;
      background: #F5F1E8;
      flex-grow: 1; 
    }
    
    .footer {
      background: #8B6F47;
      color: white;
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
<title>Quản trị - Savor Cake</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
<table border="0">
    <tr>
        <td width="220" valign="top" class="sidebar">
            <ul>
            <li><a href="manage_sizes.php"><i class="fas fa-expand-arrows-alt"></i> Quản lý cỡ bánh</a></li>
            <li><a href="manage_flavors.php"><i class="fas fa-cube"></i> Quản lý hương vị</a></li>
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