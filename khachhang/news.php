<?php
session_start();
require_once 'connect.php';

$conn->query("CREATE TABLE IF NOT EXISTS news (
  id int(11) NOT NULL AUTO_INCREMENT,
  title varchar(255) NOT NULL,
  slug varchar(255) DEFAULT NULL,
  summary varchar(500) DEFAULT NULL,
  content text DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  is_active tinyint(1) DEFAULT 1,
  created_at datetime DEFAULT current_timestamp(),
  updated_at datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (id),
  KEY slug (slug),
  KEY is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 9;
$offset = ($page - 1) * $perPage;

$list = [];
$total = 0;
$res = $conn->query("SELECT COUNT(*) AS c FROM news WHERE is_active = 1");
if ($res && $row = $res->fetch_assoc()) {
    $total = (int)$row['c'];
}
$totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;

$sql = "SELECT id, title, slug, summary, image, created_at FROM news WHERE is_active = 1 ORDER BY created_at DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
$res = $conn->query($sql);
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $list[] = $row;
    }
}

$featured = !empty($list) ? array_shift($list) : null;

$page_title = 'Tin tức - Sweet Cake';
include 'header.php';
?>

<div class="content-page news-page">
    <nav class="content-breadcrumb" aria-label="Breadcrumb">
        <a href="index.php">Trang chủ</a>
        <span>/</span>
        <span>Tin tức</span>
    </nav>

    <div class="content-page-header news-hero">
        <span class="section-kicker">Sweet Cake Journal</span>
        <h1>Tin tức & cảm hứng bánh ngọt</h1>
        <p>Cập nhật câu chuyện tiệm bánh, mẹo chọn bánh, sự kiện và ưu đãi mới nhất từ Sweet Cake</p>
    </div>

    <?php if (!$featured): ?>
        <div class="news-empty">
            <i class="fas fa-newspaper"></i>
            <p>Chưa có bài viết nào.</p>
        </div>
    <?php else: ?>
        <article class="news-featured">
            <a href="news-detail.php?id=<?php echo (int)$featured['id']; ?>">
                <div class="news-featured-thumb">
                    <?php if (!empty($featured['image'])): ?>
                        <img src="<?php echo htmlspecialchars(strpos($featured['image'], 'http') === 0 ? $featured['image'] : '../images/' . $featured['image']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                    <?php else: ?>
                        <div class="news-card-thumb fallback"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                </div>
                <div class="news-featured-body">
                    <span class="news-badge"><i class="fas fa-star"></i> Bài viết mới</span>
                    <h2><?php echo htmlspecialchars($featured['title']); ?></h2>
                    <?php if (!empty($featured['summary'])): ?>
                        <p><?php echo htmlspecialchars($featured['summary']); ?></p>
                    <?php endif; ?>
                    <div class="news-featured-meta">
                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($featured['created_at'])); ?></span>
                        <strong>Đọc bài viết <i class="fas fa-arrow-right"></i></strong>
                    </div>
                </div>
            </a>
        </article>

        <?php if (!empty($list)): ?>
        <div class="news-section-heading">
            <h2>Bài viết mới nhất</h2>
            <p>Những cập nhật và gợi ý hữu ích dành cho bạn</p>
        </div>

        <div class="news-grid">
            <?php foreach ($list as $item): ?>
            <article class="news-card">
                <a href="news-detail.php?id=<?php echo (int)$item['id']; ?>">
                    <?php if (!empty($item['image'])): ?>
                        <div class="news-card-thumb">
                            <img src="<?php echo htmlspecialchars(strpos($item['image'], 'http') === 0 ? $item['image'] : '../images/' . $item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        </div>
                    <?php else: ?>
                        <div class="news-card-thumb fallback"><i class="fas fa-image"></i></div>
                    <?php endif; ?>
                    <div class="news-card-body">
                        <h2 class="news-card-title"><?php echo htmlspecialchars($item['title']); ?></h2>
                        <?php if (!empty($item['summary'])): ?>
                            <p class="news-card-summary"><?php echo htmlspecialchars($item['summary']); ?></p>
                        <?php endif; ?>
                        <p class="news-card-date"><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($item['created_at'])); ?></p>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
        <nav class="content-pagination" aria-label="Phân trang tin tức">
            <?php if ($page > 1): ?>
                <a href="news.php?page=<?php echo $page - 1; ?>">&laquo; Trước</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="news.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="news.php?page=<?php echo $page + 1; ?>">Sau &raquo;</a>
            <?php endif; ?>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
