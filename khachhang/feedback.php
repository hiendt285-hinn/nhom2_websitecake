<?php include 'header.php'; ?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback tháng 11/2025</title>

    <style>
        body {
            font-family: "Open Sans", sans-serif;
            margin: 0;
            background: #fffaf0;
        }

        .feedback-section {
            padding: 60px 20px;
            text-align: center;
        }

        .feedback-title {
            font-size: 42px;
            color: #000000;
            font-weight: 700;
        }

        .line {
            width: 120px;
            height: 6px;
            background: #000000;
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
            margin-bottom: 50px; /* Thêm khoảng cách dưới cho slider */
        }

        .slide img {
            width: 320px;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        /* === BẮT ĐẦU CSS STICKY FOOTER ĐÃ SỬA === */
        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .content-wrapper { 
            /* Phần này sẽ tự động giãn nở để đẩy footer xuống */
            flex: 1 0 auto; 
        }

        .footer {
            flex-shrink: 0; 
        }
        /* === KẾT THÚC CSS STICKY FOOTER ĐÃ SỬA === */
    </style>
</head>

<body>
    <div class="content-wrapper">

        <section class="feedback-section" id="feedback-thang-11">
            <h2 class="feedback-title">Feedback tháng 11/2025</h2>
            <div class="line"></div>

            <p class="feedback-desc">
                Sweet Cake đã nhận được nhiều phản hồi tích cực từ phía khách hàng khi sử dụng sản phẩm
                bánh sinh nhật, bánh ngọt của chúng mình… Cùng xem thử các mẫu bánh được khách yêu tin tưởng
                ủng hộ dưới đây nhé!
            </p>

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
            </div>
        </section>

        <section class="feedback-section" id="hoat-dong-xa-hoi">
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

    </div>
    <?php include 'footer.php'; ?>
</body>
</html>