-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 09, 2026 at 03:54 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ban_banh`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Bánh kem tạo hình', 'banh-kem-tao-hinh', 'Bánh kem tạo hình theo yêu cầu', '2025-11-08 09:31:24', '2025-11-11 17:46:24'),
(2, 'Bánh kem giỏ hoa', 'banh-kem-gio-hoa', 'Bánh kem trang trí giỏ hoa', '2025-11-08 09:31:24', '2025-11-11 17:46:46'),
(3, 'Bánh kem Oreo', 'banh-kem-oreo', 'Bánh kem vị Oreo', '2025-11-08 09:31:24', '2025-11-11 18:00:28'),
(4, 'Bánh kem thiên nga', 'banh-kem-thien-nga', 'Bánh kem tạo hình thiên nga', '2025-11-08 09:31:24', '2025-11-11 18:02:11'),
(5, 'Bánh kem mousse', 'banh-kem-mousse', 'Bánh kem mousse mát lạnh', '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(6, 'Bánh bông lan trứng muối', 'banh-bong-lan-trung-muoi', 'Bánh bông lan trứng muối thơm ngon', '2025-11-27 09:13:35', '2025-11-27 09:13:35'),
(7, 'Bánh hộp thiếc', 'banh-hop-thiec', 'Bánh hộp thiếc cao cấp', '2025-12-02 14:50:27', '2025-12-02 14:50:27'),
(8, 'Bánh cupcake', 'banh-cup-cake', 'Bánh cupcake set quà', '2025-12-02 15:00:12', '2025-12-02 15:00:12'),
(9, 'Bánh kem sự kiện', 'banh-kem-su-kien', 'Bánh kem cho sự kiện, tiệc', '2025-12-02 15:09:25', '2025-12-02 15:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `status` varchar(50) DEFAULT 'new',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `user_id`, `name`, `email`, `phone`, `message`, `status`, `created_at`) VALUES
(1, 6, 'Đỗ Quang Hưng', 'hung123@gmail.com', '0248384933', 'Bánh ngon', 'new', '2026-03-03 13:24:47'),
(2, 3, 'Đỗ Thu Hiền', 'hien@gmail.com', '0900000001', 'Tôi muốn hỏi về bánh sinh nhật cho bé 1 tuổi', 'new', '2025-12-03 08:30:00'),
(3, NULL, 'Lê Văn Nam', 'namlevan@gmail.com', '0933222111', 'Có giao hàng tận nơi ở Bình Dương không ạ?', 'read', '2025-12-02 15:45:00'),
(4, 3, 'Đỗ Thu Hiền', 'hien@gmail.com', '0900000001', 'Bánh rất ngon, lần sau sẽ ghé tiếp', 'new', '2026-03-09 08:13:34'),
(5, 3, 'Đỗ Thu Hiền', 'hien@gmail.com', '0900000001', 'Bánh rất ngon, lần sau sẽ ghé tiếp', 'new', '2026-03-09 08:13:41'),
(6, 3, 'Đỗ Thu Hiền', 'hien@gmail.com', '0900000001', 'Bánh rất ngon, lần sau sẽ ghé tiếp', 'new', '2026-03-09 08:17:55'),
(7, 3, 'Đỗ Thu Hiền', 'hien@gmail.com', '0900000001', 'Cảm ơn bạn rất nhiều', 'new', '2026-03-09 08:19:10'),
(8, 8, 'Nguyễn Thanh Hương', 'huong123@gmail.com', '0834798273', 'Ngon', 'new', '2026-03-09 09:42:17');

-- --------------------------------------------------------

--
-- Table structure for table `flavors`
--

CREATE TABLE `flavors` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flavors`
--

INSERT INTO `flavors` (`id`, `name`) VALUES
(6, 'Cốt Cà Phê + Kem Cà Phê'),
(5, 'Cốt Socola + Kem Socola'),
(7, 'Cốt Trà Xanh + Kem Trà Xanh'),
(4, 'Cốt Vani + Mứt Cherry'),
(2, 'Cốt Vani + Mứt Dâu Tây'),
(1, 'Cốt Vani + Mứt Việt Quất'),
(3, 'Cốt Vani + Mứt Xoài (kèm xoài tươi)');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `summary` varchar(500) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `summary`, `content`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '🍰 Sweet Cake – Nơi Mỗi Chiếc Bánh Là Một Câu Chuyện Ngọt Ngào', NULL, 'Trong cuộc sống bận rộn ngày nay, đôi khi chúng ta chỉ cần một chút ngọt ngào để làm dịu tâm hồn. Và tại Sweet Cake, chúng tôi tin rằng mỗi chiếc bánh không chỉ là món tráng miệng – mà còn là một khoảnh khắc hạnh phúc được tạo nên từ sự yêu thương và tỉ mỉ.', '🎂 Vì Sao Nên Chọn Sweet Cake?\r\n1️⃣ Nguyên liệu tươi ngon – An toàn tuyệt đối\r\nSweet Cake sử dụng nguyên liệu chất lượng cao, được chọn lọc kỹ càng mỗi ngày. Từ bột mì, trứng, sữa cho đến trái cây tươi – tất cả đều đảm bảo độ tươi mới và an toàn vệ sinh thực phẩm.\r\n2️⃣ Đa dạng mẫu mã – Phù hợp mọi dịp\r\nDù là sinh nhật, kỷ niệm, tiệc công ty hay chỉ đơn giản là một buổi trà chiều nhẹ nhàng, Sweet Cake đều có mẫu bánh phù hợp với bạn:\r\nBánh kem sinh nhật\r\nBánh mousse\r\nBánh tiramisu\r\nBánh bông lan truyền thống\r\nBánh theo yêu cầu thiết kế riêng\r\n3️⃣ Đặt bánh dễ dàng – Giao hàng nhanh chóng\r\nChỉ với vài thao tác đơn giản trên website, bạn có thể:\r\nXem danh sách sản phẩm\r\nChọn size và hương vị\r\nThêm vào giỏ hàng\r\nĐặt hàng nhanh chóng\r\nHệ thống được thiết kế thân thiện, dễ sử dụng và tối ưu trải nghiệm người dùng.\r\n💝 Bánh Không Chỉ Để Ăn – Mà Để Gửi Gắm Yêu Thương\r\nMột chiếc bánh sinh nhật là lời chúc mừng.\r\nMột chiếc bánh kỷ niệm là lời nhắc về những khoảnh khắc đáng nhớ.\r\nMột chiếc bánh nhỏ trong ngày thường cũng có thể là cách bạn tự thưởng cho chính mình.\r\nSweet Cake mong muốn trở thành cầu nối giúp bạn trao gửi những cảm xúc ấy một cách trọn vẹn nhất.\r\n📦 Cam Kết Từ Sweet Cake\r\n✔️ Hình ảnh sản phẩm đúng với thực tế\r\n✔️ Giá cả minh bạch, rõ ràng\r\n✔️ Hỗ trợ khách hàng nhanh chóng\r\n✔️ Quản lý đơn hàng chính xác và tiện lợi\r\nChúng tôi không chỉ bán bánh – chúng tôi mang đến trải nghiệm mua sắm tiện lợi, hiện đại và đáng tin cậy.\r\n🌷 Đặt Bánh Ngay Hôm Nay!\r\nHãy để Sweet Cake đồng hành cùng bạn trong những dịp đặc biệt sắp tới.\r\nTruy cập website, chọn chiếc bánh bạn yêu thích và để chúng tôi mang sự ngọt ngào đến tận tay bạn.\r\nSweet Cake – Ngọt ngào từng khoảnh khắc. 🍰✨', 'premium_photo-1716152291350-4137853b4a73.jpeg', 1, '2026-03-03 13:56:33', '2026-03-03 13:56:33'),
(2, '🍰 Vì Sao Khách Hàng Lựa Chọn Sweet Cake? – Không Chỉ Là Bánh, Mà Là Cảm Xúc', NULL, 'Trong thị trường bánh ngọt ngày càng cạnh tranh, khách hàng không chỉ tìm kiếm một chiếc bánh ngon. Họ tìm kiếm trải nghiệm, cảm xúc và sự tin tưởng.\r\nSweet Cake ra đời với mong muốn trở thành thương hiệu mang đến nhiều hơn một sản phẩm – mà là những khoảnh khắc đáng nhớ.', '🎯 1. Định Vị Thương Hiệu: Ngọt Ngào – Hiện Đại – Tiện Lợi\r\nSweet Cake hướng tới nhóm khách hàng:\r\nNgười trẻ 18–30 tuổi yêu thích đặt hàng online\r\nNhân viên văn phòng cần đặt bánh nhanh cho sự kiện\r\nGia đình muốn đặt bánh sinh nhật tiện lợi\r\nChúng tôi tập trung vào 3 yếu tố cốt lõi:\r\n✨ Chất lượng sản phẩm\r\n✨ Thiết kế hiện đại, bắt trend\r\n✨ Trải nghiệm mua hàng đơn giản\r\nWebsite được xây dựng tối ưu theo hành vi người dùng: dễ tìm kiếm, dễ đặt hàng, dễ theo dõi đơn.', 'az.webp', 1, '2026-03-03 13:59:35', '2026-03-03 13:59:35');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `note` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `promo_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `status` varchar(50) DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `full_name`, `phone`, `address`, `note`, `total_amount`, `promo_code`, `discount_amount`, `status`, `payment_method`, `created_at`) VALUES
(1, 3, 'Đỗ Thu Hiền', '0900000001', '123 Đường ABC, Quận 1, TP.HCM', 'Giao giờ hành chính', 330000.00, NULL, 0.00, 'delivered', 'cod', '2025-12-01 10:00:00'),
(2, 5, 'Hồ Quỳnh Anh', '0420032044', '456 Đường XYZ, Quận 7, TP.HCM', NULL, 199000.00, NULL, 0.00, 'delivered', 'cod', '2025-12-02 14:30:00'),
(3, 5, 'Hồ Quỳnh Anh', '0420032044', 'Hà Nội', '', 127500.00, '0', 22500.00, 'delivered', 'cod', '2026-03-03 07:17:22'),
(6, 5, 'Hồ Quỳnh Anh', '0420032044', 'Hà Nội', '', 408000.00, '0', 72000.00, 'delivered', 'cod', '2026-03-03 07:40:19'),
(7, 5, 'Hồ Quỳnh Anh', '0420032044', 'Hà nội', '', 127500.00, '0', 22500.00, 'delivered', 'cod', '2026-03-03 07:48:57'),
(8, 5, 'Hồ Quỳnh Anh', '0420032044', 'Hà nội', '', 140000.00, '0', 0.00, 'delivered', 'cod', '2026-03-06 14:57:31'),
(9, 5, 'Hồ Quỳnh Anh', '0420032044', 'Hà Nội', '', 1100000.00, '0', 0.00, 'delivered', 'cod', '2026-03-08 09:57:42'),
(10, 8, 'Nguyễn Thanh Hương', '0834798274', 'Bình Dương', '', 754849.00, '0', 151.00, 'shipping', 'cod', '2026-03-08 16:57:34'),
(11, 8, 'Nguyễn Thanh Hương', '0834798274', 'Bình Dương', '', 240000.00, '', 0.00, 'shipping', 'cod', '2026-03-09 00:49:36'),
(12, 8, 'Nguyễn Thanh Hương', '0834798274', 'Bình Dương', '', 439912.00, 'GIAM20%', 88.00, 'delivered', 'banking', '2026-03-09 00:55:24'),
(13, 3, 'Đỗ Thu Hiền', '0900000001', 'Hà Nội', '', 1339000.00, 'FREESHIP350', 30000.00, 'delivered', 'banking', '2026-03-09 00:56:51'),
(14, 8, 'Nguyễn Thanh Hương', '0834798274', 'Bình Dương', '', 150000.00, '', 0.00, 'pending', 'cod', '2026-03-09 01:57:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(100) NOT NULL,
  `flavor` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `flavor`, `quantity`, `unit_price`) VALUES
(1, 1, 1, '17cm x 8cm', 'Cốt Vani + Mứt Dâu Tây', 1, 150000.00),
(2, 1, 6, '17cm x 8cm', 'Cốt Vani + Mứt Xoài (kèm xoài tươi)', 1, 199000.00),
(3, 2, 6, '21cm x 8cm', 'Cốt Vani + Mứt Việt Quất', 1, 199000.00),
(4, 3, 42, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 150000.00),
(5, 6, 40, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 480000.00),
(6, 7, 42, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 150000.00),
(7, 8, 43, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 140000.00),
(8, 9, 33, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 5, 220000.00),
(9, 10, 43, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 205000.00),
(10, 10, 37, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 550000.00),
(11, 11, 36, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 240000.00),
(12, 12, 32, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 220000.00),
(13, 12, 35, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 220000.00),
(14, 13, 36, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 240000.00),
(15, 13, 25, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 450000.00),
(16, 13, 10, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 150000.00),
(17, 13, 18, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 529000.00),
(18, 14, 42, '13cm x 6cm', 'Cốt Cà Phê + Kem Cà Phê', 1, 150000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) DEFAULT NULL,
  `price` decimal(12,0) NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `price`, `image`, `description`, `short_description`, `category_id`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Bánh kem hoa hồng', NULL, 150000, '6927aef503a6c.webp', 'Bánh kem hoa hồng', '', 2, 0, 1, '2025-11-27 08:52:53', '2025-11-27 09:32:50'),
(2, 'Bánh bông lan trứng muối gấu', NULL, 180000, '6927b4eaa5433.webp', 'Cốt bánh vani, bên trong có sốt bơ trứng và trứng muối nghiền ở mỗi lớp bánh, phủ bên ngoài là lớp chà bông heo đặc biệt, trang trí hình gấu', 'Cốt bánh vani, bên trong có sốt bơ trứng', 6, 0, 1, '2025-11-27 09:18:18', '2025-11-27 09:32:56'),
(3, 'Bánh bông lan trứng muối trái tym', NULL, 180000, '6927b6193647d.webp', 'Phiên bản nhiều ruốc, nhiều trứng muối hơn, trang trí thêm hình trái tim trên mặt. Cốt bánh mềm ẩm, ngoài sốt dầu trứng còn có thêm sốt phô mai béo ngậy, ăn cùng chà bông heo và ruốc gà cay.', 'Phiên bản nhiều ruốc, nhiều trứng muối hơn', 6, 0, 1, '2025-11-27 09:23:21', '2025-11-27 09:32:59'),
(4, 'Bông lan trứng muối Việt quất', NULL, 180000, '6927b64700922.webp', 'Cốt bông lan mềm mịn kết hợp sốt bơ trứng và sốt phô mai béo ngậy, xen giữa chà bông heo và trứng muối ở mỗi lớp bánh, trang trí thêm việt quất tươi', 'Cốt bông lan mềm mịn kết hợp sốt bơ trứng', 6, 0, 1, '2025-11-27 09:24:07', '2025-11-27 09:33:28'),
(5, 'Bông lan trứng muối sốt phô mai', NULL, 180000, '6927b67d4a158.webp', 'Cốt vani, sốt bơ trứng. Trang trí thêm chà bông heo đặc biệt, sốt phô mai, trứng muối và chà bông gà cay', 'Cốt vani, sốt bơ trứng', 6, 0, 1, '2025-11-27 09:25:01', '2025-11-27 09:33:31'),
(6, 'Bánh kem bó hoa tulip giấy hồng (hộp mica)', NULL, 199000, '6927b6db7850e.webp', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi, tạo hình bó hoa tulip tone màu hồng đào, điểm thêm phụ kiện bướm giấy và kẹo bi đường vô cùng xinh xắn', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi,...', 2, 0, 1, '2025-11-27 09:26:35', '2025-11-27 09:33:34'),
(7, 'Bánh kem Bouquet Flowers (hộp mica)', NULL, 220000, '6927b8dc5b103.webp', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi, tạo hình bó hoa tulip tone màu hồng đào với những bông hoa từ socola', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi...', 2, 0, 1, '2025-11-27 09:35:08', '2025-11-27 09:35:33'),
(8, 'Bánh kem bơ xoài việt quất', NULL, 150000, '6927b99ab8bf3.webp', 'Cốt vani và kem bơ, trang trí thêm hoa quả tươi mát gồm xoài và việt quất trên mặt bánh, xem kẽ các bông kem bơ béo ngậy, thơm ngon', 'Cốt vani và kem bơ, trang trí ...', 5, 0, 1, '2025-11-27 09:38:18', '2025-11-27 09:38:57'),
(9, 'Bánh kem Cún Dâu', NULL, 180000, '6927bb96592f3.webp', 'Bánh kem cốt vani, mứt dâu, kem trắng, tạo hình chú cún đáng yêu cùng hoa quả trang trí: dâu tây, việt quất và nơ hồng xinh xắn', '', 1, 0, 1, '2025-11-27 09:46:46', '2025-11-27 09:46:46'),
(10, 'Bánh kem Gà Bông', NULL, 150000, '6927bbb9d782e.webp', 'Bánh kem cốt vani, mứt xoài cùng nhân xoài dứa kết hợp với kem vị xoài thơm dịu, tạo hình chú gà bông màu vàng với mũ sinh nhật mini ngộ nghĩnh', '', 1, 0, 1, '2025-11-27 09:47:21', '2025-11-27 09:47:21'),
(11, 'Bánh kem nến xoắn oreo', NULL, 150000, '6927bbed59d06.webp', 'Bánh kem cốt socola, kem tươi vị oreo, trang trí trên mặt bánh và thân bánh những chiếc bánh oreo ngộ nghĩnh.', '', 3, 0, 1, '2025-11-27 09:48:13', '2025-11-27 09:48:13'),
(12, 'Bánh kem Oreo Flower', NULL, 150000, '6927bc0d41923.webp', 'Bánh kem cốt socola, kem tươi vị oreo, trang trí trên mặt bánh là bông kem và bánh quy oreo, phủ lá vàng cùng vụn bánh quy oreo ở giữa và chân bánh', '', 3, 0, 1, '2025-11-27 09:48:45', '2025-11-27 09:48:45'),
(13, 'Bánh kem socola oreo dâu tây', NULL, 180000, '6927bc2e8495c.webp', 'Bánh kem cốt socola kết hợp với kem vị oreo, trang trí dâu tây và bánh quy oreo cùng với lớp socola sệt trên mặt bánh', '', 3, 0, 1, '2025-11-27 09:49:18', '2025-11-27 09:49:18'),
(14, 'Bánh kem socola Oreo Party', NULL, 150000, '6927bc69136ba.webp', 'Bánh kem cốt socola kết hợp cùng kem vị oreo, trang trí vụn bánh quy oreo ở chân bánh và mặt bánh, kết hợp thêm những chiếc bánh quy oreo tạo hình ngộ nghĩnh cho bữa tiệc', '', 3, 0, 1, '2025-11-27 09:50:17', '2025-11-27 09:50:17'),
(15, 'Bánh Kem Oreo Choco', NULL, 160000, '6927bcd901807.webp', 'Bánh kem cốt socola, kem socola và oreo, trang trí trên mặt bánh những \"chú\" oreo tinh nghịch dễ thương và vụn bánh oreo', '', 3, 0, 1, '2025-11-27 09:52:09', '2025-11-27 09:52:09'),
(16, 'Bánh red velvet sữa chua việt quất', NULL, 150000, '6927bd0515b21.webp', 'Bánh kem cốt red velvet tròn, kem sữa chua, bên trên trang trí 3 quả việt quất. (Trang trí sao vàng mừng 30/4 áp dụng từ 12h 17/4/2025)', '', 5, 0, 1, '2025-11-27 09:52:53', '2025-11-27 09:52:53'),
(17, 'Berry Lover Cake 500g', NULL, 259000, '692e9a6cb3134.webp', 'Chiếc bánh là bản tình ca mùa hè gửi đến những tâm hồn yêu trái cây đỏ mọng. Với 5 tầng hương vị đan xen, Berry Lover Cake chinh phục vị giác bằng sự cân bằng tinh tế giữa vị ngọt, vị chua dịu và độ béo mịn hoàn hảo: (1) Cốt bánh socola ẩm mịn, làm nền cho các tầng vị tỏa sáng, (2) Mousse dâu tây chua dịu, tươi mát, (3) Mousse mascarpone béo nhẹ, mềm mượt, (4) Mousse custard dâu thơm dịu, ngọt ngào như kem trứng mùa hè, (5) Lớp tráng gương dâu bóng mượt, như chiếc gương phản chiếu sắc đỏ rực rỡ. Bánh được hoàn thiện bằng trái cây tươi trang trí: dâu đỏ mọng, việt quất chua nhẹ.', 'Chiếc bánh là bản tình ca mùa hè gửi đến những tâm hồn yêu trái cây đỏ mọng.', 7, 0, 1, '2025-12-02 14:51:08', '2025-12-02 14:51:08'),
(18, 'Combo \"CHOCO LOVER\"', NULL, 529000, '692e9ab7bad60.webp', 'Combo gồm: Choco Dream Cake 315g + Tiramisu classic 250g + Tiramisu matcha 250g', 'Combo gồm: Choco Dream Cake 315g + Tiramisu classic 250g + Tiramisu matcha 250g', 7, 0, 1, '2025-12-02 14:52:23', '2025-12-02 14:52:23'),
(19, 'Combo \"SÀNH ĐIỆU\"', NULL, 469000, '692e9adc7e97f.webp', '', 'Combo gồm: Olong Longan Cake 550g + Choco Tiramisu 400g', 7, 0, 1, '2025-12-02 14:53:00', '2025-12-02 14:53:00'),
(20, 'Combo \"NHÀN NHÃ\"', NULL, 410000, '692e9b08ce02a.webp', 'Combo gồm: Oolong Longan Cake 550g + Oolong Longan Tiramisu 390g', 'Combo gồm: Oolong Longan Cake 550g + Oolong Longan Tiramisu 390g', 7, 0, 1, '2025-12-02 14:53:44', '2025-12-02 14:53:44'),
(21, 'Pomelo Mango Pearl Cake 535g', NULL, 249000, '692e9b408c200.webp', 'Chiếc bánh mang hương vị nhiệt đới đầy tinh tế với cốt vani mềm nhẹ, kết hợp cùng lớp mousse xoài dừa tươi mát từ xoài chín mọng. Xen kẽ là tầng thạch bưởi hồng thơm dịu và mousse cream cheese béo ngậy, hòa quyện tạo nên sự cân bằng hài hòa giữa ngọt, chua và béo. Bề mặt bánh được điểm xuyết bằng những tép bưởi hồng căng mọng cùng trân châu trắng dai giòn, mang lại trải nghiệm thanh mát, lạ miệng và cuốn hút ngay từ lần đầu thưởng thức.', 'Chiếc bánh mang hương vị nhiệt đới đầy tinh tế với cốt vani mềm nhẹ,', 7, 0, 1, '2025-12-02 14:54:40', '2025-12-02 14:54:40'),
(22, 'Tiramisu Matcha 250g', NULL, 179000, '692e9b7307bd0.webp', 'Bánh Tiramisu Matcha, bản giao hưởng tinh tế giữa lớp bánh lady finger nhúng nước trà xanh đậm vị kết hợp cùng rượu dark rum Captain Morgan, xen kẽ với lớp kem tiramisu mượt mà làm từ trứng gà, phô mai mascarpone và kem whipping - phía trên phủ lớp bột matcha Haru Nhật Bản, mang đến hậu vị thanh mát, nhẹ nhàng nhưng đầy lôi cuốn. Tặng kèm: thìa, túi giữ nhiệt, HDSD', 'Bánh Tiramisu Matcha, bản giao hưởng tinh tế giữa lớp bánh lady finger nhúng nước trà xanh đậm vị kết hợp cùng rượu dark rum...', 7, 0, 1, '2025-12-02 14:55:31', '2025-12-02 14:55:31'),
(25, 'Bánh kem thiên nga hồng - Pink Ombre Swan', NULL, 450000, '692e9c263b677.webp', 'Bánh kem tạo hình thiên nga, cốt bánh vani, mứt xoài, nhân xoài tươi, trang trí lông vũ và cổ thiên ngà từ socola trắng, tạo hiệu ứng chuyển màu hồng cùng hoa quả tươi: dâu tây, việt quất', 'Bánh kem tạo hình thiên nga, cốt bánh vani, mứt xoài, nhân xoài tươi, ..', 4, 0, 1, '2025-12-02 14:58:30', '2025-12-02 14:58:30'),
(26, 'Bánh kem thiên nga trắng - White Fruit Swan', NULL, 420000, '692e9c50e15d7.webp', 'Bánh kem cốt vani, mứt xoài, nhân xoài với tạo hình thiên nga trắng, những chiếc lông vũ hay cổ thiên nga đều được làm hoàn toàn từ socola trắng, trang trí thêm các loại hoa quả tươi: nho xanh, xoài, dâu tây tạo nên một vẻ đẹp điệu đà, đầy thuần khiết và tươi sáng, hứa hẹn về một khởi đầu mới', 'Bánh kem cốt vani, mứt xoài, nhân xoài với tạo hình thiên nga trắng, những chiếc lông vũ hay...', 4, 0, 1, '2025-12-02 14:59:12', '2025-12-02 14:59:12'),
(27, 'Bánh kem thiên nga đen - Golden Berries Swan', NULL, 450000, '692e9c787eb14.webp', 'Bánh kem cốt socola, mứt dâu tây kết hợp cùng kem socola với tạo hình thiên nga đen, những chiếc lông vũ hay cổ thiên nga đều được làm hoàn toàn từ socola đen nguyên chất, trang trí cùng dâu tây và việt quất. Mỗi chiếc lông vũ đều được phủ bột nhũ vàng làm tăng thêm vẻ đẹp quyến rũ, sang trọng và đầy quý phái.', 'Bánh kem cốt socola, mứt dâu tây kết hợp cùng kem socola với tạo hình thiên nga đen, ..', 4, 0, 1, '2025-12-02 14:59:52', '2025-12-02 14:59:52'),
(28, 'Set bánh cupcake chà bông trứng muối', NULL, 150000, '692e9cc38b7d9.webp', 'Cốt bánh vani kết hợp cùng nhân sốt phô mai và phủ bên trên lớp sốt bơ trứng, trang trí bắt hoa bằng kem topping cùng 2 loại chà bông và trứng muối', 'Cốt bánh vani kết hợp cùng nhân sốt phô mai và phủ bên trên lớp sốt bơ trứng,...', 8, 0, 1, '2025-12-02 15:01:07', '2025-12-02 15:01:07'),
(29, 'Set bánh cupcake hoa quả', NULL, 150000, '692e9cea8567a.webp', 'Cốt bánh vani kết hợp cùng nhân mứt bên trong bánh gồm: mứt xoài và mứt việt quất, trang trí bắt hoa bằng kem topping cùng các loại hoa quả: dâu tây, nho, xoài, việt quất.', 'Cốt bánh vani kết hợp cùng nhân mứt bên trong bánh gồm: mứt xoài và mứt việt quất,...', 8, 0, 1, '2025-12-02 15:01:46', '2025-12-02 15:01:46'),
(30, 'Set bánh cupcake tulip', NULL, 150000, '692e9d1664e8e.webp', 'Set bánh cupcake vị vani nhân mứt việt quất, trang trí tạo hình như những bó hoa tulip mini nhỏ xinh cùng bướm giấy và kẹo bi bạc trang trí', 'Set bánh cupcake vị vani nhân mứt việt quất, trang trí tạo hình như những bó hoa tulip...', 8, 0, 1, '2025-12-02 15:02:30', '2025-12-02 15:02:30'),
(31, 'Mousse sữa chua việt quất', NULL, 220000, '692e9d8f67105.webp', 'Cốt vani xen kẽ các tầng bánh. Tầng dưới cùng là lớp mousse việt quất, tiếp theo là tầng mousse sữa chua và trên cùng là lớp thạch gelatin việt quất. Trang trí bằng việt quất, dâu tươi Đà Lạt, socola trắng và lá hương thảo', 'Cốt vani xen kẽ các tầng bánh. Tầng dưới cùng là lớp mousse việt quất, tiếp theo...', 5, 0, 1, '2025-12-02 15:04:31', '2025-12-02 15:04:31'),
(32, 'Mousse bơ sữa dừa', NULL, 220000, '692e9dbb45914.webp', 'Chất bánh mousse mềm, mịn, ngọt bùi, kết hợp với 2 lớp mousse thơm ngậy của bơ và sữa dừa, bồng bềnh nhẹ nhàng như tan trong miệng. Trang trí thêm hoa quả bên trên, cùng một bông hoa nhỏ xinh ở giữa.', 'Chất bánh mousse mềm, mịn, ngọt bùi, kết hợp với 2 lớp mousse thơm ngậy của bơ và sữa dừa...', 5, 0, 1, '2025-12-02 15:05:15', '2025-12-02 15:05:15'),
(33, 'Mousse Xoài', NULL, 220000, '692e9de25a845.webp', 'Bánh mousse mang đậm hương vị xoài ngọt mát dễ chịu, kem tươi whipping cream kết hợp cùng sữa chua cốt vani, trang trí thêm xoài trên mặt bánh để thêm đậm vị cùng socola', '0', 5, 1, 1, '2025-12-02 15:05:54', '2026-03-09 00:09:37'),
(34, 'Mousse việt quất socola', NULL, 220000, '692e9e0c221b7.webp', 'Gồm 2 lớp mousse việt quất xen cùng 2 lớp cốt bánh vị socola. Trên cùng là lớp thạch tráng gương vị việt quất, trang trí thêm dâu, việt quất và nho xanh tươi', 'Gồm 2 lớp mousse việt quất xen cùng 2 lớp cốt bánh vị socola...', 5, 0, 1, '2025-12-02 15:06:36', '2025-12-02 15:06:36'),
(35, 'Mousse sữa chua dâu tây', NULL, 220000, '692e9e3226f6f.webp', 'Cốt vani xen kẽ các tầng bánh. Dưới cùng là tầng bánh mousse dâu tây, tiếp theo là tầng mousse sữa chua và trên cùng là lớp thạch gelatin dâu tây. Trang trí bằng dâu tươi Đà Lạt, việt quất và socola trắng', 'Cốt vani xen kẽ các tầng bánh. Dưới cùng là tầng bánh mousse dâu tây, tiếp theo ...', 5, 0, 1, '2025-12-02 15:07:14', '2025-12-02 15:07:14'),
(36, 'Bánh Tiramisu Cacao', NULL, 240000, '692e9e5e1c803.webp', 'Bánh tiramisu sử dụng cốt bánh bông lan cà phê và bánh lady finger chuẩn vị, kết hợp hài hòa cùng rượu Rhum, café, pha thêm vị béo của kem tươi, phô mai Mascarpone Ý cùng lòng đỏ trứng. Tạo nên vị thanh ngọt nhẹ nhàng, hơi hơi nồng hương rượu rất hấp dẫn, bên trên phủ lớp kem topping vị cà phê cốt dừa và bột cacao nguyên chất.', '0', 5, 1, 1, '2025-12-02 15:07:58', '2026-03-09 00:10:48'),
(37, 'Set bánh su kem Singapore hoa quả chữ nhật', NULL, 550000, '692e9ee20eaa0.webp', 'Phù hợp cho các dịp tổ chức sự kiện lớn, tổng kết năm học, v.v với số lượng 48 bánh/set. Lớp vỏ dai mềm kết hợp cùng nhân kem vani mát lạnh, béo ngậy từ kem whipping. Trang trí kem topping cùng hoa quả tươi, mang đến cảm giác tươi mát: nho xanh, việt quất, xoài cát ngọt và dâu tây', '0', 9, 1, 1, '2025-12-02 15:10:10', '2026-03-09 00:10:37'),
(38, 'Set bánh cốc mix (mousse, bông lan trứng muối, bánh kem bắp)', NULL, 120000, '692e9f078c645.webp', 'Set quà tinh tế, tiện lợi: Không cần chia cắt, mix nhiều vị đa dạng, tặng kèm thiệp 20/10 xinh xắn. Nhận Pre-order các đơn hàng đến hết 15/10 (khu vực TP HCM) và 17/10 (Khu vực Hà Nội).', 'Set quà tinh tế, tiện lợi: Không cần chia cắt, mix nhiều vị đa dạng, tặng kèm thiệp 20/10 xinh xắn...', 9, 0, 1, '2025-12-02 15:10:47', '2025-12-02 15:10:47'),
(39, 'Bông lan trứng muối chữ nhật cắt miếng viết chữ', NULL, 480000, '692e9f8f1f937.webp', 'Cốt bánh vani, kết hợp cùng sốt phô mai và sốt bơ trứng thơm ngậy, bên trên trang trí ruốc gà cay, chà bông heo và trứng muối, các miếng bánh được chia sẵn tiện lợi và có thể viết chữ theo yêu cầu', 'Cốt bánh vani, kết hợp cùng sốt phô mai và sốt bơ trứng thơm ngậy, bên trên trang trí ruốc gà cay,...', 9, 0, 1, '2025-12-02 15:13:03', '2025-12-02 15:13:03'),
(40, 'Bánh kem chữ nhật vẽ logo', NULL, 480000, '692e9fc2acfd0.webp', 'Kem tươi vị sữa chua, mứt việt quất, cốt vani. Trang trí thêm hoa quả tươi gồm nho, xoài, dâu tây, việt quất. Bánh chữ nhật nguyên khối chưa cắt miếng, chữ viết tùy chọn', 'Kem tươi vị sữa chua, mứt việt quất, cốt vani. Trang trí thêm hoa quả tươi gồm nho, xoài, dâu tây, việt quất...', 9, 0, 1, '2025-12-02 15:13:54', '2025-12-02 15:13:54'),
(41, 'Set bánh su kem Singapore bó hoa', NULL, 160000, '692ea00d962a9.webp', 'Bánh su kem Singapore với lớp vỏ dai mềm kết hợp cùng kem vani mát lạnh và béo ngậy từ kem whipping. Trang trí tạo hình bó hoa với kem topping và kẹo bi bạc trang trí cùng chiếc nơ voan trắng xinh xắn.', '0', 2, 0, 1, '2025-12-02 15:15:09', '2026-03-07 01:20:59'),
(42, 'Mousse sữa chua việt quất', NULL, 150000, '69a6834e9bf51.webp', 'Cốt vani xen kẽ các tầng bánh. Tầng dưới cùng là lớp mousse việt quất, tiếp theo là tầng mousse sữa chua và trên cùng là lớp thạch gelatin việt quất. Trang trí bằng việt quất, dâu tươi Đà Lạt, socola trắng và lá hương thảo', '0', 5, 1, 1, '2026-03-03 13:44:30', '2026-03-09 00:09:52'),
(43, 'Bánh bông lan', NULL, 205000, '69a6934ecfde7.webp', '', '0', 6, 1, 0, '2026-03-03 14:52:46', '2026-03-08 17:20:51');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `discount_type` enum('percent','fixed') NOT NULL DEFAULT 'percent',
  `discount_value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `min_order_amount` decimal(10,2) DEFAULT 0.00,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `code`, `title`, `discount_type`, `discount_value`, `min_order_amount`, `valid_from`, `valid_to`, `is_active`, `created_at`) VALUES
(2, 'FREESHIP350', 'Freeship đơn từ 350K', 'fixed', 30000.00, 350000.00, '2026-03-08 11:18:00', '2026-03-29 11:18:00', 1, '2026-03-03 04:16:54'),
(3, 'SWEET10', 'Giảm 10% đơn hàng', 'percent', 10.00, 200000.00, '2026-03-02 11:18:00', '2026-03-15 11:18:00', 1, '2026-03-03 04:16:54'),
(4, 'GIAM20%', 'Giảm 20% đơn hàng từ 150k', 'percent', 0.02, 150000.00, '2026-03-02 23:16:00', '2026-03-31 23:16:00', 1, '2026-03-08 23:16:54');

-- --------------------------------------------------------

--
-- Table structure for table `promotion_products`
-- (Sản phẩm được áp dụng mã; không có dòng nào = áp dụng toàn bộ giỏ)
--

CREATE TABLE `promotion_products` (
  `promotion_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sizes`
--

CREATE TABLE `sizes` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sizes`
--

INSERT INTO `sizes` (`id`, `name`) VALUES
(1, '13cm x 6cm'),
(2, '17cm x 8cm'),
(3, '21cm x 8cm');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'hien123', 'hien@gmail.com', '$2y$10$X4xUKJon6RvDk9QcF8ZxiOMg3Ig59AkbgSrlRn/kxLUV8ZDO6VDVK', 'Đỗ Thu Hiền', '0900000001', 'Thường Tín, Hà Nội', 'customer', 1, '2025-11-11 17:53:24', '2026-03-09 08:19:45'),
(4, 'admin', 'admin@savorcake.com', '$2y$10$5CTm9hLBUl56XMiPlfxeK.KUCEN/tJn5HLd0x9WpM5dAvTJb1HgJC', 'Quản trị viên', NULL, NULL, 'admin', 1, '2025-11-19 09:43:20', '2025-11-19 09:43:20'),
(5, 'quynhanh', 'quynhanh123@gmail.com', '$2y$10$Y70EZ0Kz929SVs/7U3o3LeCIlsR1.k392FlgIj3z612/P6d28yOfi', 'Hồ Quỳnh Anh', '0420032044', NULL, 'customer', 1, '2025-11-24 13:14:25', '2025-11-24 13:14:25'),
(6, 'hung', 'hung123@gmail.com', '$2y$10$w4MeA7QjJ3sD/twl6Yzx1.g7tqGrUJsUOXaWy36.ble9fUOqCocre', 'Đỗ Quang Hưng', '0248384933', 'Hà Nội', 'customer', 1, '2026-03-03 13:24:34', '2026-03-03 13:24:34'),
(8, 'huong', 'huong123@gmail.com', '$2y$10$3NEJa9AgYZpddnFfPIvO.eTPv9NGqocWLrE5P5KltOUr9fbxnaukm', 'Nguyễn Thanh Hương', '0834798273', 'Bình Dương', 'customer', 1, '2026-03-08 17:14:05', '2026-03-09 08:58:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `flavors`
--
ALTER TABLE `flavors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `slug` (`slug`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD PRIMARY KEY (`promotion_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sizes`
--
ALTER TABLE `sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `flavors`
--
ALTER TABLE `flavors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotion_products`
--
ALTER TABLE `promotion_products`
  ADD CONSTRAINT `promotion_products_ibfk_1` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_products_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
