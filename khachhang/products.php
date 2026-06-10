<?php
if (!isset($_SESSION)) {
    session_start();
}
include 'connect.php'; // Kết nối DB

// === LẤY DỮ LIỆU TỪ URL ===
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$sort = isset($_GET['sort']) ? $_GET['sort'] : '';
if (!in_array($sort, ['price_asc', 'price_desc'], true)) {
    $sort = '';
}
$orderBy = 'p.id DESC';
if ($sort === 'price_asc') {
    $orderBy = 'p.price ASC, p.id DESC';
} elseif ($sort === 'price_desc') {
    $orderBy = 'p.price DESC, p.id DESC';
}

$limit = 12; // Số sản phẩm/trang
$offset = ($page - 1) * $limit;

// === XÂY DỰNG TRUY VẤN ===
$where = [];
$params = [];
$types = '';

// Chỉ hiển thị sản phẩm đang hoạt động trên cửa hàng
$where[] = "p.is_active = 1";

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
        ORDER BY $orderBy 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Lấy danh mục (dùng cho sidebar + form lọc)
$cat_sql = "SELECT id, name FROM categories ORDER BY name";
$cat_result = $conn->query($cat_sql);
$categories = [];
while ($row = $cat_result->fetch_assoc()) {
    $categories[] = $row;
}

// Tham số GET cơ bản (giữ search, sort khi chuyển danh mục)
$baseQuery = [];
if ($search !== '') $baseQuery['search'] = $search;
if ($sort !== '') $baseQuery['sort'] = $sort;

$page_title = 'Sản phẩm - Sweet Cake';
include 'header.php';
?>

<div class="products-page">
    <div class="page-header">
        <h1>Tất cả sản phẩm</h1>
        <p>Khám phá hương vị bánh ngọt tươi ngon mỗi ngày</p>
    </div>

    <div class="products-layout">
        <!-- Cột trái: Danh mục bánh -->
        <aside class="products-sidebar">
            <h3>Danh mục bánh</h3>
            <ul class="sidebar-categories">
                <li>
                    <a href="products.php<?php echo $baseQuery ? '?' . http_build_query($baseQuery) : ''; ?>" class="<?php echo $category_id === 0 ? 'active' : ''; ?>">Tất cả</a>
                </li>
                <?php foreach ($categories as $cat): ?>
                <li>
                    <a href="products.php?<?php echo http_build_query(array_merge($baseQuery, ['category' => (int)$cat['id']])); ?>" class="<?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="products-main">
            <!-- Bộ lọc - thanh tìm kiếm kích thước bằng nhau -->
            <form method="GET" class="filters">
                <div class="filter-group filter-search">
                    <label>Tìm kiếm</label>
                    <input type="text" name="search" placeholder="Tên bánh..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group filter-category">
                    <label>Danh mục</label>
                    <select name="category">
                        <option value="">Tất cả</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group filter-sort">
                    <label>Sắp xếp</label>
                    <select name="sort">
                        <option value="" <?php echo $sort === '' ? 'selected' : ''; ?>>Mặc định</option>
                        <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Giá tăng dần</option>
                        <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Giá giảm dần</option>
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
                    <img src="../images/<?php echo htmlspecialchars($row['image']); ?>" 
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
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
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
    </div>
</div>

<?php include 'footer.php'; ?>