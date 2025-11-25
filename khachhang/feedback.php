<?php include 'header.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback tháng 11/2025</title>

    <!-- CSS đặt chung trong file -->
    <style>
        body {
            font-family: "Poppins", sans-serif;
            margin: 0;
            background: #fffaf0;
        }

        .feedback-section {
            padding: 60px 20px;
            text-align: center;
        }

        .feedback-title {
            font-size: 42px;
            color: #5d9159;
            font-weight: 700;
        }

        .line {
            width: 120px;
            height: 6px;
            background: #5d9159;
            border-radius: 6px;
            margin: 10px auto 30px;
        }

        .feedback-desc {
            max-width: 900px;
            font-size: 20px;
            margin: 0 auto 50px;
            color: #666;
            line-height: 1.6;
        }

        /* Slider */
        .feedback-slider {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 25px;
        }

        .slide img {
            width: 320px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
    </style>
</head>

<body>

    <section class="feedback-section" id="feedback">
        <h2 class="feedback-title">Feedback tháng 11/2025</h2>
        <div class="line"></div>

        <p class="feedback-desc">
            Sweet Cake đã nhận được nhiều phản hồi tích cực từ phía khách hàng khi sử dụng sản phẩm
            bánh sinh nhật, bánh ngọt của chúng mình… Cùng xem thử các mẫu bánh được khách yêu tin tưởng
            ủng hộ dưới đây nhé!
        </p>

        <!-- Slider ảnh feedback -->
        <div class="feedback-slider">
            <div class="slide">
                <img src="../images/2.webp" alt="feedback 1">
            </div>

            <div class="slide">
                <img src="../images/8.webp" alt="feedback 2">
            </div>

            <div class="slide">
                <img src="../images/9.webp" alt="feedback 3">
            </div>

            <div class="slide">
                <img src="../images/13.webp" alt="feedback 4">
            </div>
            <section class="feedback-section" id="feedback">
        <h2 class="feedback-title">Hoạt động xã hội</h2>
        <div class="line"></div>
         <div class="feedback-slider">
            <div class="slide">
                <img src="../images/Trung20cho201.webp" alt="feedback 1">
            </div>

            <div class="slide">
                <img src="../images/z7086621318727_8cc60a9b74b28b9e61af31dcdb106aac-1.webp" alt="feedback 2">
            </div>

            <div class="slide">
                <img src="../images/Trung20cho202.webp" alt="feedback 3">
            </div>
        </div>
    </section>
    <?php include 'footer.php'; ?>
</body>
</html>


