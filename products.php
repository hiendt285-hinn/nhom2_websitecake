<?php
if (!isset($_SESSION)) {
    session_start();
}
include 'connect.php'; // Kết nối DB

// === LẤY DỮ LIỆU TỪ URL ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Số sản phẩm/trang
$offset = ($page - 1) * $limit;

// === XÂY DỰNG TRUY VẤN ===
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $where[] = "p.name LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if ($category_id > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

$where_clause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Đếm tổng sản phẩm
$count_sql = "SELECT COUNT(*) as total FROM products p $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_products = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_products / $limit);

// Lấy sản phẩm
$sql = "SELECT p.id, p.name, p.price, p.image, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_clause 
        ORDER BY p.id DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Lấy danh mục để lọc
$cat_sql = "SELECT id, name FROM categories ORDER BY name";
$cat_result = $conn->query($cat_sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Anh Hoa Bakery</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* === TRANG SẢN PHẨM === */
        .products-page {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
            font-family: 'Poppins', sans-serif;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #5D4037;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 16px;
        }

        /* === BỘ LỌC + TÌM KIẾM === */
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
            align-items: center;
            background: #f8f5f0;
            padding: 15px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 180px;
        }

        .filter-group label {
            font-weight: 600;
            color: #5D4037;
            font-size: 14px;
        }

        .filter-group select,
        .filter-group input {
            padding: 10px 14px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            background: white;
        }

        .filter-group input {
            flex: 1;
        }

        .btn-search {
            background: #5D4037;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: 0.3s;
        }

        .btn-search:hover {
            background: #4E342E;
        }

        /* === GRID SẢN PHẨM === */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            text-align: center;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.12);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: 0.3s;
        }

        .product-card:hover img {
            transform: scale(1.05);
        }

        .product-info {
            padding: 15px;
        }

        .product-info h3 {
            font-size: 16px;
            color: #333;
            margin: 8px 0;
            font-weight: 600;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-category {
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 18px;
            color: #FFCA28;
            font-weight: bold;
            margin: 8px 0;
        }

        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
        }

        .btn-view {
            flex: 1;
            background: #5D4037;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-view:hover {
            background: #4E342E;
        }

        .btn-cart {
            background: #FFCA28;
            color: #5D4037;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-cart:hover {
            background: #FFB300;
        }

        /* === PHÂN TRANG === */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin: 40px 0;
        }

        .pagination a,
        .pagination span {
            padding: 10px 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-decoration: none;
            color: #5D4037;
            font-weight: 500;
            transition: 0.3s;
        }

        .pagination a:hover,
        .pagination .current {
            background: #5D4037;
            color: white;
            border-color: #5D4037;
        }

        .pagination .disabled {
            color: #aaa;
            cursor: not-allowed;
        }

        /* === KẾT QUẢ TÌM KIẾM === */
        .search-results {
            text-align: center;
            color: #666;
            font-style: italic;
            margin: 20px 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-group {
                min-width: 100%;
            }
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="products-page">
    <div class="page-header">
        <h1>Tất cả sản phẩm</h1>
        <p>Khám phá hương vị bánh ngọt tươi ngon mỗi ngày</p>
    </div>

    <!-- Bộ lọc -->
    <form method="GET" class="filters">
        <div class="filter-group">
            <label>Tìm kiếm</label>
            <input type="text" name="search" placeholder="Tên bánh..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="filter-group">
            <label>Danh mục</label>
            <select name="category">
                <option value="">Tất cả</option>
                <?php while ($cat = $cat_result->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn-search">
            <i class="fas fa-search"></i> Tìm kiếm
        </button>
    </form>

    <!-- Kết quả tìm kiếm -->
    <?php if ($search || $category_id): ?>
        <div class="search-results">
            Tìm thấy <strong><?php echo $total_products; ?></strong> sản phẩm
            <?php if ($search): ?> cho "<em><?php echo htmlspecialchars($search); ?></em>"<?php endif; ?>
            <?php if ($category_id): ?> trong danh mục đã chọn<?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Grid sản phẩm -->
    <div class="products-grid">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <a href="product-detail.php?id=<?php echo $row['id']; ?>">
                        <img src="images/products/<?php echo htmlspecialchars($row['image']); ?>" 
                             alt="<?php echo htmlspecialchars($row['name']); ?>">
                    </a>
                    <div class="product-info">
                        <div class="product-category"><?php echo htmlspecialchars($row['category_name']); ?></div>
                        <h3>
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </a>
                        </h3>
                        <div class="product-price">
                            <?php echo number_format($row['price'], 0, ',', '.'); ?>₫
                        </div>
                        <div class="product-actions">
                            <a href="product-detail.php?id=<?php echo $row['id']; ?>" class="btn-view">
                                Xem chi tiết
                            </a>
                            <button class="btn-cart" onclick="addToCart(<?php echo $row['id']; ?>)">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column: 1/-1; text-align:center; color:#999; font-size:18px;">
                Không tìm thấy sản phẩm nào.
            </p>
        <?php endif; ?>
    </div>

    <!-- Phân trang -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Trước</a>
            <?php else: ?>
                <span class="disabled">Trước</span>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Sau</a>
            <?php else: ?>
                <span class="disabled">Sau</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script>
    // Thêm vào giỏ hàng nhanh
    function addToCart(productId) {
        fetch('add-to-cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${productId}&quantity=1&size=20cm&flavor=Vani`
        })
        .then(() => {
            alert('Đã thêm vào giỏ hàng!');
            location.reload(); // Cập nhật số lượng giỏ
        });
    }
</script>

</body>
</html>