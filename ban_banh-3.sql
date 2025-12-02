-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 02, 2025 at 12:00 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

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
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(50) DEFAULT '20cm',
  `flavor` varchar(50) DEFAULT 'Vani',
  `quantity` int(11) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Bánh kem tạo hình', 'banh-kem-tao-hinh', NULL, '2025-11-08 09:31:24', '2025-11-11 17:46:24'),
(2, 'Bánh kem giỏ hoa', 'banh-kem-gio-hoa', NULL, '2025-11-08 09:31:24', '2025-11-11 17:46:46'),
(3, 'Bánh kem Oreo ', 'banh-kem-oreo ', NULL, '2025-11-08 09:31:24', '2025-11-11 18:00:28'),
(4, 'Bánh kem thiên nga', 'banh-kem-thien-nga', NULL, '2025-11-08 09:31:24', '2025-11-11 18:02:11'),
(5, 'Bánh kem mousse', 'banh-kem-mousse', NULL, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(6, 'Bánh bông lan trứng muối', 'banh-bong-lan-trung-muoi', '', '2025-11-27 09:13:35', '2025-11-27 09:13:35'),
(7, 'Bánh hộp thiếc', 'banh-hop-thiec', '', '2025-12-02 14:50:27', '2025-12-02 14:50:27'),
(8, 'Bánh cupcake', 'banh-cup-cake', '', '2025-12-02 15:00:12', '2025-12-02 15:00:12'),
(9, 'Bánh kem sự kiện', 'banh-kem-su-kien', '', '2025-12-02 15:09:25', '2025-12-02 15:09:25');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `status` varchar(50) DEFAULT 'Pending',
  `payment_method` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` enum('news','promotion') DEFAULT 'news',
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `content`, `image`, `category`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Khuyến mãi 20% bánh sinh nhật', 'khuyen-mai-20-banh-sinh-nhat', 'Từ 1/11 - 15/11, giảm 20% tất cả bánh sinh nhật...', 'km-20.jpg', 'promotion', 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24'),
(2, 'Mở cửa hàng mới tại Quận 7', 'mo-cua-hang-quan-7', 'Anh Hoa Bakery chính thức khai trương...', 'store-q7.jpg', 'news', 1, '2025-11-08 09:31:24', '2025-11-08 09:31:24');

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
  `stock` int(11) DEFAULT 100,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `price`, `image`, `description`, `short_description`, `category_id`, `stock`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Bánh kem hoa hồng', NULL, 150000, '6927aef503a6c.webp', 'Bánh kem hoa hồng', '', 2, 100, 0, 1, '2025-11-27 08:52:53', '2025-11-27 09:32:50'),
(2, 'Bánh bông lan trứng muối gấu', NULL, 180000, '6927b4eaa5433.webp', 'Cốt bánh vani, bên trong có sốt bơ trứng và trứng muối nghiền ở mỗi lớp bánh, phủ bên ngoài là lớp chà bông heo đặc biệt, trang trí hình gấu', 'Cốt bánh vani, bên trong có sốt bơ trứng', 6, 40, 0, 1, '2025-11-27 09:18:18', '2025-11-27 09:32:56'),
(3, 'Bánh bông lan trứng muối trái tym', NULL, 180000, '6927b6193647d.webp', 'Phiên bản nhiều ruốc, nhiều trứng muối hơn, trang trí thêm hình trái tim trên mặt. Cốt bánh mềm ẩm, ngoài sốt dầu trứng còn có thêm sốt phô mai béo ngậy, ăn cùng chà bông heo và ruốc gà cay.', 'Phiên bản nhiều ruốc, nhiều trứng muối hơn', 6, 100, 0, 1, '2025-11-27 09:23:21', '2025-11-27 09:32:59'),
(4, 'Bông lan trứng muối Việt quất', NULL, 180000, '6927b64700922.webp', 'Cốt bông lan mềm mịn kết hợp sốt bơ trứng và sốt phô mai béo ngậy, xen giữa chà bông heo và trứng muối ở mỗi lớp bánh, trang trí thêm việt quất tươi', 'Cốt bông lan mềm mịn kết hợp sốt bơ trứng', 6, 100, 0, 1, '2025-11-27 09:24:07', '2025-11-27 09:33:28'),
(5, 'Bông lan trứng muối sốt phô mai', NULL, 180000, '6927b67d4a158.webp', 'Cốt vani, sốt bơ trứng. Trang trí thêm chà bông heo đặc biệt, sốt phô mai, trứng muối và chà bông gà cay', 'Cốt vani, sốt bơ trứng', 6, 100, 0, 1, '2025-11-27 09:25:01', '2025-11-27 09:33:31'),
(6, 'Bánh kem bó hoa tulip giấy hồng (hộp mica)', NULL, 199000, '6927b6db7850e.webp', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi, tạo hình bó hoa tulip tone màu hồng đào, điểm thêm phụ kiện bướm giấy và kẹo bi đường vô cùng xinh xắn', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi,...', 2, 100, 0, 1, '2025-11-27 09:26:35', '2025-11-27 09:33:34'),
(7, 'Bánh kem Bouquet Flowers (hộp mica)', NULL, 220000, '6927b8dc5b103.webp', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi, tạo hình bó hoa tulip tone màu hồng đào với những bông hoa từ socola', 'Bánh kem cốt vani, mứt xoài, nhân xoài tươi...', 2, 100, 0, 1, '2025-11-27 09:35:08', '2025-11-27 09:35:33'),
(8, 'Bánh kem bơ xoài việt quất', NULL, 150000, '6927b99ab8bf3.webp', 'Cốt vani và kem bơ, trang trí thêm hoa quả tươi mát gồm xoài và việt quất trên mặt bánh, xem kẽ các bông kem bơ béo ngậy, thơm ngon', 'Cốt vani và kem bơ, trang trí ...', 5, 100, 0, 1, '2025-11-27 09:38:18', '2025-11-27 09:38:57'),
(9, 'Bánh kem Cún Dâu', NULL, 180000, '6927bb96592f3.webp', 'Bánh kem cốt vani, mứt dâu, kem trắng, tạo hình chú cún đáng yêu cùng hoa quả trang trí: dâu tây, việt quất và nơ hồng xinh xắn', '', 1, 100, 0, 1, '2025-11-27 09:46:46', '2025-11-27 09:46:46'),
(10, 'Bánh kem Gà Bông', NULL, 150000, '6927bbb9d782e.webp', 'Bánh kem cốt vani, mứt xoài cùng nhân xoài dứa kết hợp với kem vị xoài thơm dịu, tạo hình chú gà bông màu vàng với mũ sinh nhật mini ngộ nghĩnh', '', 1, 100, 0, 1, '2025-11-27 09:47:21', '2025-11-27 09:47:21'),
(11, 'Bánh kem nến xoắn oreo', NULL, 150000, '6927bbed59d06.webp', 'Bánh kem cốt socola, kem tươi vị oreo, trang trí trên mặt bánh và thân bánh những chiếc bánh oreo ngộ nghĩnh.', '', 3, 100, 0, 1, '2025-11-27 09:48:13', '2025-11-27 09:48:13'),
(12, 'Bánh kem Oreo Flower', NULL, 150000, '6927bc0d41923.webp', 'Bánh kem cốt socola, kem tươi vị oreo, trang trí trên mặt bánh là bông kem và bánh quy oreo, phủ lá vàng cùng vụn bánh quy oreo ở giữa và chân bánh', '', 3, 10, 0, 1, '2025-11-27 09:48:45', '2025-11-27 09:48:45'),
(13, 'Bánh kem socola oreo dâu tây', NULL, 180000, '6927bc2e8495c.webp', 'Bánh kem cốt socola kết hợp với kem vị oreo, trang trí dâu tây và bánh quy oreo cùng với lớp socola sệt trên mặt bánh', '', 3, 100, 0, 1, '2025-11-27 09:49:18', '2025-11-27 09:49:18'),
(14, 'Bánh kem socola Oreo Party', NULL, 150000, '6927bc69136ba.webp', 'Bánh kem cốt socola kết hợp cùng kem vị oreo, trang trí vụn bánh quy oreo ở chân bánh và mặt bánh, kết hợp thêm những chiếc bánh quy oreo tạo hình ngộ nghĩnh cho bữa tiệc', '', 3, 100, 0, 1, '2025-11-27 09:50:17', '2025-11-27 09:50:17'),
(15, 'Bánh Kem Oreo Choco', NULL, 160000, '6927bcd901807.webp', 'Bánh kem cốt socola, kem socola và oreo, trang trí trên mặt bánh những \"chú\" oreo tinh nghịch dễ thương và vụn bánh oreo', '', 3, 100, 0, 1, '2025-11-27 09:52:09', '2025-11-27 09:52:09'),
(16, 'Bánh red velvet sữa chua việt quất', NULL, 150000, '6927bd0515b21.webp', 'Bánh kem cốt red velvet tròn, kem sữa chua, bên trên trang trí 3 quả việt quất. (Trang trí sao vàng mừng 30/4 áp dụng từ 12h 17/4/2025)', '', 5, 100, 0, 1, '2025-11-27 09:52:53', '2025-11-27 09:52:53'),
(17, 'Berry Lover Cake 500g', NULL, 259000, '692e9a6cb3134.webp', 'Chiếc bánh là bản tình ca mùa hè gửi đến những tâm hồn yêu trái cây đỏ mọng. Với 5 tầng hương vị đan xen, Berry Lover Cake chinh phục vị giác bằng sự cân bằng tinh tế giữa vị ngọt, vị chua dịu và độ béo mịn hoàn hảo: (1) Cốt bánh socola ẩm mịn, làm nền cho các tầng vị tỏa sáng, (2) Mousse dâu tây chua dịu, tươi mát, (3) Mousse mascarpone béo nhẹ, mềm mượt, (4) Mousse custard dâu thơm dịu, ngọt ngào như kem trứng mùa hè, (5) Lớp tráng gương dâu bóng mượt, như chiếc gương phản chiếu sắc đỏ rực rỡ. Bánh được hoàn thiện bằng trái cây tươi trang trí: dâu đỏ mọng, việt quất chua nhẹ.', 'Chiếc bánh là bản tình ca mùa hè gửi đến những tâm hồn yêu trái cây đỏ mọng.', 7, 100, 0, 1, '2025-12-02 14:51:08', '2025-12-02 14:51:08'),
(18, 'Combo \"CHOCO LOVER', NULL, 529000, '692e9ab7bad60.webp', '', 'Combo gồm: Choco Dream Cake 315g + Tiramisu classic 250g +Tiramisu matcha 250g', 7, 100, 0, 1, '2025-12-02 14:52:23', '2025-12-02 14:52:23'),
(19, 'Combo \"SÀNH ĐIỆU\"', NULL, 469000, '692e9adc7e97f.webp', '', 'Combo gồm: Olong Longan Cake 550g + Choco Tiramisu 400g', 7, 100, 0, 1, '2025-12-02 14:53:00', '2025-12-02 14:53:00'),
(20, 'Combo \"NHÀN NHÃ\"', NULL, 410000, '692e9b08ce02a.webp', 'Combo gồm: Oolong Longan Cake 550g + Oolong Longan Tiramisu 390g', 'Combo gồm: Oolong Longan Cake 550g + Oolong Longan Tiramisu 390g', 7, 100, 0, 1, '2025-12-02 14:53:44', '2025-12-02 14:53:44'),
(21, 'Pomelo Mango Pearl Cake 535g', NULL, 249000, '692e9b408c200.webp', 'Chiếc bánh mang hương vị nhiệt đới đầy tinh tế với cốt vani mềm nhẹ, kết hợp cùng lớp mousse xoài dừa tươi mát từ xoài chín mọng. Xen kẽ là tầng thạch bưởi hồng thơm dịu và mousse cream cheese béo ngậy, hòa quyện tạo nên sự cân bằng hài hòa giữa ngọt, chua và béo. Bề mặt bánh được điểm xuyết bằng những tép bưởi hồng căng mọng cùng trân châu trắng dai giòn, mang lại trải nghiệm thanh mát, lạ miệng và cuốn hút ngay từ lần đầu thưởng thức.', 'Chiếc bánh mang hương vị nhiệt đới đầy tinh tế với cốt vani mềm nhẹ,', 7, 100, 0, 1, '2025-12-02 14:54:40', '2025-12-02 14:54:40'),
(22, 'Tiramisu Matcha 250g', NULL, 179000, '692e9b7307bd0.webp', 'Bánh Tiramisu Matcha, bản giao hưởng tinh tế giữa lớp bánh lady finger nhúng nước trà xanh đậm vị kết hợp cùng rượu dark rum Captain Morgan, xen kẽ với lớp kem tiramisu mượt mà làm từ trứng gà, phô mai mascarpone và kem whipping - phía trên phủ lớp bột matcha Haru Nhật Bản, mang đến hậu vị thanh mát, nhẹ nhàng nhưng đầy lôi cuốn. Tặng kèm: thìa, túi giữ nhiệt, HDSD', 'Bánh Tiramisu Matcha, bản giao hưởng tinh tế giữa lớp bánh lady finger nhúng nước trà xanh đậm vị kết hợp cùng rượu dark rum...', 7, 100, 0, 1, '2025-12-02 14:55:31', '2025-12-02 14:55:31'),
(25, 'Bánh kem thiên nga hồng - Pink Ombre Swan', NULL, 450000, '692e9c263b677.webp', 'Bánh kem tạo hình thiên nga, cốt bánh vani, mứt xoài, nhân xoài tươi, trang trí lông vũ và cổ thiên ngà từ socola trắng, tạo hiệu ứng chuyển màu hồng cùng hoa quả tươi: dâu tây, việt quất', 'Bánh kem tạo hình thiên nga, cốt bánh vani, mứt xoài, nhân xoài tươi, ..', 4, 100, 0, 1, '2025-12-02 14:58:30', '2025-12-02 14:58:30'),
(26, 'Bánh kem thiên nga trắng - White Fruit Swan', NULL, 420000, '692e9c50e15d7.webp', 'Bánh kem cốt vani, mứt xoài, nhân xoài với tạo hình thiên nga trắng, những chiếc lông vũ hay cổ thiên nga đều được làm hoàn toàn từ socola trắng, trang trí thêm các loại hoa quả tươi: nho xanh, xoài, dâu tây tạo nên một vẻ đẹp điệu đà, đầy thuần khiết và tươi sáng, hứa hẹn về một khởi đầu mới', 'Bánh kem cốt vani, mứt xoài, nhân xoài với tạo hình thiên nga trắng, những chiếc lông vũ hay...', 4, 100, 0, 1, '2025-12-02 14:59:12', '2025-12-02 14:59:12'),
(27, 'Bánh kem thiên nga đen - Golden Berries Swan', NULL, 450000, '692e9c787eb14.webp', 'Bánh kem cốt socola, mứt dâu tây kết hợp cùng kem socola với tạo hình thiên nga đen, những chiếc lông vũ hay cổ thiên nga đều được làm hoàn toàn từ socola đen nguyên chất, trang trí cùng dâu tây và việt quất. Mỗi chiếc lông vũ đều được phủ bột nhũ vàng làm tăng thêm vẻ đẹp quyến rũ, sang trọng và đầy quý phái.', 'Bánh kem cốt socola, mứt dâu tây kết hợp cùng kem socola với tạo hình thiên nga đen, ..', 4, 100, 0, 1, '2025-12-02 14:59:52', '2025-12-02 14:59:52'),
(28, 'Set bánh cupcake chà bông trứng muối', NULL, 150000, '692e9cc38b7d9.webp', 'Cốt bánh vani kết hợp cùng nhân sốt phô mai và phủ bên trên lớp sốt bơ trứng, trang trí bắt hoa bằng kem topping cùng 2 loại chà bông và trứng muối', 'Cốt bánh vani kết hợp cùng nhân sốt phô mai và phủ bên trên lớp sốt bơ trứng,...', 8, 100, 0, 1, '2025-12-02 15:01:07', '2025-12-02 15:01:07'),
(29, 'Set bánh cupcake hoa quả', NULL, 150000, '692e9cea8567a.webp', 'Cốt bánh vani kết hợp cùng nhân mứt bên trong bánh gồm: mứt xoài và mứt việt quất, trang trí bắt hoa bằng kem topping cùng các loại hoa quả: dâu tây, nho, xoài, việt quất.', 'Cốt bánh vani kết hợp cùng nhân mứt bên trong bánh gồm: mứt xoài và mứt việt quất,...', 8, 100, 0, 1, '2025-12-02 15:01:46', '2025-12-02 15:01:46'),
(30, 'Set bánh cupcake tulip', NULL, 150000, '692e9d1664e8e.webp', 'Set bánh cupcake vị vani nhân mứt việt quất, trang trí tạo hình như những bó hoa tulip mini nhỏ xinh cùng bướm giấy và kẹo bi bạc trang trí', 'Set bánh cupcake vị vani nhân mứt việt quất, trang trí tạo hình như những bó hoa tulip...', 8, 100, 0, 1, '2025-12-02 15:02:30', '2025-12-02 15:02:30'),
(31, 'Mousse sữa chua việt quất', NULL, 220000, '692e9d8f67105.webp', 'Cốt vani xen kẽ các tầng bánh. Tầng dưới cùng là lớp mousse việt quất, tiếp theo là tầng mousse sữa chua và trên cùng là lớp thạch gelatin việt quất. Trang trí bằng việt quất, dâu tươi Đà Lạt, socola trắng và lá hương thảo', 'Cốt vani xen kẽ các tầng bánh. Tầng dưới cùng là lớp mousse việt quất, tiếp theo...', 5, 100, 0, 1, '2025-12-02 15:04:31', '2025-12-02 15:04:31'),
(32, 'Mousse bơ sữa dừa', NULL, 220000, '692e9dbb45914.webp', 'Chất bánh mousse mềm, mịn, ngọt bùi, kết hợp với 2 lớp mousse thơm ngậy của bơ và sữa dừa, bồng bềnh nhẹ nhàng như tan trong miệng. Trang trí thêm hoa quả bên trên, cùng một bông hoa nhỏ xinh ở giữa.', 'Chất bánh mousse mềm, mịn, ngọt bùi, kết hợp với 2 lớp mousse thơm ngậy của bơ và sữa dừa...', 5, 100, 0, 1, '2025-12-02 15:05:15', '2025-12-02 15:05:15'),
(33, 'Mousse Xoài', NULL, 220000, '692e9de25a845.webp', 'Bánh mousse mang đậm hương vị xoài ngọt mát dễ chịu, kem tươi whipping cream kết hợp cùng sữa chua cốt vani, trang trí thêm xoài trên mặt bánh để thêm đậm vị cùng socola', 'Bánh mousse mang đậm hương vị xoài ngọt mát dễ chịu, kem tươi whipping cream kết hợp cùng sữa...', 5, 100, 0, 1, '2025-12-02 15:05:54', '2025-12-02 15:05:54'),
(34, 'Mousse việt quất socola', NULL, 220000, '692e9e0c221b7.webp', 'Gồm 2 lớp mousse việt quất xen cùng 2 lớp cốt bánh vị socola. Trên cùng là lớp thạch tráng gương vị việt quất, trang trí thêm dâu, việt quất và nho xanh tươi', 'Gồm 2 lớp mousse việt quất xen cùng 2 lớp cốt bánh vị socola...', 5, 100, 0, 1, '2025-12-02 15:06:36', '2025-12-02 15:06:36'),
(35, 'Mousse sữa chua dâu tây', NULL, 220000, '692e9e3226f6f.webp', 'Cốt vani xen kẽ các tầng bánh. Dưới cùng là tầng bánh mousse dâu tây, tiếp theo là tầng mousse sữa chua và trên cùng là lớp thạch gelatin dâu tây. Trang trí bằng dâu tươi Đà Lạt, việt quất và socola trắng', 'Cốt vani xen kẽ các tầng bánh. Dưới cùng là tầng bánh mousse dâu tây, tiếp theo ...', 5, 100, 0, 1, '2025-12-02 15:07:14', '2025-12-02 15:07:14'),
(36, 'Bánh Tiramisu Cacao', NULL, 240000, '692e9e5e1c803.webp', 'Bánh tiramisu sử dụng cốt bánh bông lan cà phê và bánh lady finger chuẩn vị, kết hợp hài hòa cùng rượu Rhum, café, pha thêm vị béo của kem tươi, phô mai Mascarpone Ý cùng lòng đỏ trứng. Tạo nên vị thanh ngọt nhẹ nhàng, hơi hơi nồng hương rượu rất hấp dẫn, bên trên phủ lớp kem topping vị cà phê cốt dừa và bột cacao nguyên chất.', 'Bánh tiramisu sử dụng cốt bánh bông lan cà phê và bánh lady finger chuẩn vị, kết hợp hài hòa ...', 5, 100, 0, 1, '2025-12-02 15:07:58', '2025-12-02 15:07:58'),
(37, 'Set bánh su kem Singapore hoa quả chữ nhật', NULL, 550000, '692e9ee20eaa0.webp', 'Phù hợp cho các dịp tổ chức sự kiện lớn, tổng kết năm học, v.v với số lượng 48 bánh/set. Lớp vỏ dai mềm kết hợp cùng nhân kem vani mát lạnh, béo ngậy từ kem whipping. Trang trí kem topping cùng hoa quả tươi, mang đến cảm giác tươi mát: nho xanh, việt quất, xoài cát ngọt và dâu tây', 'Phù hợp cho các dịp tổ chức sự kiện lớn, tổng kết năm học, v.v với số lượng 48 bánh/set....', 9, 100, 0, 1, '2025-12-02 15:10:10', '2025-12-02 15:10:10'),
(38, 'Set bánh cốc mix (mousse, bông lan trứng muối, bánh kem bắp)', NULL, 120000, '692e9f078c645.webp', 'Set quà tinh tế, tiện lợi: Không cần chia cắt, mix nhiều vị đa dạng, tặng kèm thiệp 20/10 xinh xắn. Nhận Pre-order các đơn hàng đến hết 15/10 (khu vực TP HCM) và 17/10 (Khu vực Hà Nội).', 'Set quà tinh tế, tiện lợi: Không cần chia cắt, mix nhiều vị đa dạng, tặng kèm thiệp 20/10 xinh xắn...', 9, 100, 0, 1, '2025-12-02 15:10:47', '2025-12-02 15:10:47'),
(39, 'Bông lan trứng muối chữ nhật cắt miếng viết chữ', NULL, 480000, '692e9f8f1f937.webp', 'Cốt bánh vani, kết hợp cùng sốt phô mai và sốt bơ trứng thơm ngậy, bên trên trang trí ruốc gà cay, chà bông heo và trứng muối, các miếng bánh được chia sẵn tiện lợi và có thể viết chữ theo yêu cầu', 'Cốt bánh vani, kết hợp cùng sốt phô mai và sốt bơ trứng thơm ngậy, bên trên trang trí ruốc gà cay,...', 9, 100, 0, 1, '2025-12-02 15:13:03', '2025-12-02 15:13:03'),
(40, 'Bánh kem chữ nhật vẽ logo', NULL, 480000, '692e9fc2acfd0.webp', 'Kem tươi vị sữa chua, mứt việt quất, cốt vani. Trang trí thêm hoa quả tươi gồm nho, xoài, dâu tây, việt quất. Bánh chữ nhật nguyên khối chưa cắt miếng, chữ viết tùy chọn', 'Kem tươi vị sữa chua, mứt việt quất, cốt vani. Trang trí thêm hoa quả tươi gồm nho, xoài, dâu tây, việt quất...', 9, 100, 0, 1, '2025-12-02 15:13:54', '2025-12-02 15:13:54'),
(41, 'Set bánh su kem Singapore bó hoa', NULL, 160000, '692ea00d962a9.webp', 'Bánh su kem Singapore với lớp vỏ dai mềm kết hợp cùng kem vani mát lạnh và béo ngậy từ kem whipping. Trang trí tạo hình bó hoa với kem topping và kẹo bi bạc trang trí cùng chiếc nơ voan trắng xinh xắn.', 'Bánh su kem Singapore với lớp vỏ dai mềm kết hợp cùng kem vani mát lạnh và béo ngậy...', 2, 100, 0, 1, '2025-12-02 15:15:09', '2025-12-02 15:15:09');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `is_approved` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
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
(3, 'hien123', 'hien@gmail.com', '$2y$10$X4xUKJon6RvDk9QcF8ZxiOMg3Ig59AkbgSrlRn/kxLUV8ZDO6VDVK', 'Đỗ Thu Hiền', '', NULL, 'customer', 1, '2025-11-11 17:53:24', '2025-11-24 13:14:45'),
(4, 'admin', 'admin@savorcake.com', '$2y$10$5CTm9hLBUl56XMiPlfxeK.KUCEN/tJn5HLd0x9WpM5dAvTJb1HgJC', NULL, NULL, NULL, 'admin', 1, '2025-11-19 09:43:20', '2025-11-19 09:43:20'),
(5, 'quynhanh', 'quynhanh123@gmail.com', '$2y$10$Y70EZ0Kz929SVs/7U3o3LeCIlsR1.k392FlgIj3z612/P6d28yOfi', 'Hồ Quỳnh Anh', '0420032044', NULL, 'customer', 1, '2025-11-24 13:14:25', '2025-11-24 13:14:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`session_id`,`product_id`,`size`,`flavor`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flavors`
--
ALTER TABLE `flavors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

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
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flavors`
--
ALTER TABLE `flavors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sizes`
--
ALTER TABLE `sizes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
