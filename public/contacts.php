<?php
require_once '../app/functions.php';
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Контакты — Luxury Auto Garage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --accent: #800039;
        }

        body {
            background: radial-gradient(circle at top, #111, #000);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            color: #fff;
        }

        .box {
            width: 100%;
            max-width: 900px;
            background: #0d0d0d;
            border: 1px solid #1f1f1f;
            border-radius: 18px;
            padding: 35px;
            box-shadow: 0 0 35px rgba(128, 0, 57, 0.25);
        }

        h1 {
            text-align: center;
            font-weight: 900;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #aaa;
            margin-bottom: 25px;
        }

        .top-btn {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        .btn {
            border-radius: 14px;
            padding: 8px 16px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-primary {
            background: #1a1a1a;
            border: 1px solid #333;
        }

        .btn-success {
            background: var(--accent);
        }

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.2);
        }

        .section {
            background: #111;
            border: 1px solid #222;
            border-radius: 14px;
            padding: 18px;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        .section:hover {
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(128, 0, 57, 0.15);
        }

        .section h3 {
            font-size: 16px;
            margin-bottom: 8px;
            color: #fff;
        }

        .icon {
            color: var(--accent);
            margin-right: 8px;
        }

        .text {
            color: #bbb;
            line-height: 1.7;
            font-size: 14px;
        }

        .footer-line {
            margin-top: 20px;
            text-align: center;
            color: #666;
            font-size: 13px;
        }
    </style>
</head>

<body>

    <div class="box">

        <div class="top-btn">
            <a href="index.php" class="btn btn-primary">🏠 Главная</a>
            <a href="https://www.google.com/maps?q=Strada+Ștefan+cel+Mare+123+Chișinău" target="_blank"
                class="btn btn-success">📍 Открыть карту</a>
        </div>

        <h1>Luxury Auto Garage</h1>
        <div class="subtitle">Контакты и информация о нашем автосалоне</div>

        <div class="section">
            <h3><i class="fa-solid fa-location-dot icon"></i>Адрес</h3>
            <div class="text">
                г. Кишинёв, ул. Штефан чел Маре 123<br>
                Центральный район, удобный подъезд и парковка<br>
                Рядом: кафе, сервисы и торговые центры
            </div>
        </div>

        <div class="section">
            <h3><i class="fa-solid fa-phone icon"></i>Контакты</h3>
            <div class="text">
                Телефон: +373 600 00 000<br>
                WhatsApp / Viber: +373 600 00 000<br>
                Email: support@luxurygarage.md<br>
                Telegram: @luxurygarage
            </div>
        </div>

        <div class="section">
            <h3><i class="fa-solid fa-clock icon"></i>Рабочее время</h3>
            <div class="text">
                Пн – Пт: 09:00 – 18:00<br>
                Сб: 10:00 – 15:00<br>
                Вс: выходной<br><br>
                Онлайн поддержка: 09:00 – 21:00
            </div>
        </div>

        <div class="section">
            <h3><i class="fa-solid fa-circle-info icon"></i>О нас</h3>
            <div class="text">
                • Премиальные автомобили с проверенной историей<br>
                • Поддержка при покупке и оформлении<br>
                • Возможность тест-драйва<br>
                • Обмен авто (trade-in)<br>
                • Честные цены без скрытых комиссий
            </div>
        </div>

        <div class="footer-line">
            © Luxury Auto Garage — Premium Car Marketplace
        </div>

    </div>

</body>

</html>