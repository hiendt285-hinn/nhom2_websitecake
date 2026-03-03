<?php
session_start();
require_once 'connect.php';

// Tạo bảng news nếu chưa có
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
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức - Sweet Cake</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .news-page { max-width: 1100px; margin: 30px auto; padding: 0 20px 40px; font-family: 'Open Sans', sans-serif; }
        .news-header { text-align: center; margin-bottom: 36px; }
        .news-title { font-size: 32px; font-weight: 800; color: var(--text-black, #333); }
        .news-line { width: 120px; height: 4px; background: var(--main-brown, #9a7b5a); margin: 12px auto; border-radius: 4px; }
        .news-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; }
        .news-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.06); transition: transform 0.2s; }
        .news-card:hover { transform: translateY(-4px); }
        .news-card a { text-decoration: none; color: inherit; display: block; }
        .news-card-thumb {
            width: 100%;
            aspect-ratio: 16 / 10;
            overflow: hidden;
            background: #f0ede8;
            display: block;
        }
        .news-card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .news-card-thumb.fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 2.5rem;
        }
        .news-card-body { padding: 16px; }
        .news-card-title { font-size: 18px; font-weight: 700; margin-bottom: 8px; color: #333; line-height: 1.4; }
        .news-card-summary { font-size: 14px; color: #666; line-height: 1.5; margin-bottom: 8px; }
        .news-card-date { font-size: 13px; color: #888; }
        .news-empty { text-align: center; padding: 60px 20px; color: #666; }
        .news-pagination { display: flex; justify-content: center; gap: 8px; margin-top: 32px; flex-wrap: wrap; }
        .news-pagination a, .news-pagination span { padding: 8px 14px; border-radius: 8px; text-decoration: none; color: #2c2c2c; background: #f0ede8; font-weight: 600; }
        .news-pagination a:hover { background: var(--main-brown, #9a7b5a); color: #fff; }
        .news-pagination .current { background: var(--main-brown, #9a7b5a); color: #fff; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="news-page">
    <div class="news-header">
        <h1 class="news-title">Tin tức</h1>
        <div class="news-line"></div>
        <p style="color:#666;">Cập nhật tin tức, sự kiện và ưu đãi từ Sweet Cake</p>
    </div>

    <?php if (empty($list)): ?>
        <div class="news-empty">
            <i class="fas fa-newspaper" style="font-size:48px; color:#ddd;"></i>
            <p>Chưa có bài viết nào.</p>
        </div>
    <?php else: ?>
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

        <?php if ($totalPages > 1): ?>
        <nav class="news-pagination">
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
</body>
</html>
