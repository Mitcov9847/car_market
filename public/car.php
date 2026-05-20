<?php
require_once '../app/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die("Машина не найдена");
}

global $pdo;

/* ТЕКУЩАЯ МАШИНА */
$stmt = $pdo->prepare("
    SELECT 
        cars.*,
        COALESCE(users.name, 'Неизвестный продавец') AS seller
    FROM cars
    LEFT JOIN users ON cars.seller_id = users.id
    WHERE cars.id = ?
");
$stmt->execute([$id]);
$car = $stmt->fetch();

if (!$car) {
    die("Машина не найдена");
}

$predictedPrice = predictCarPrice(
    $car['brand'],
    $car['body_type'],
    $car['year'],
    $car['mileage']
);
/* ПОХОЖИЕ МАШИНЫ */
$stmt2 = $pdo->prepare("
    SELECT * FROM cars 
    WHERE id != ? 
    AND (
        brand = ? 
        OR model = ? 
        OR ABS(price - ?) <= 5000
    )
    ORDER BY created_at DESC
    LIMIT 4
");
$stmt2->execute([
    $id,
    $car['brand'],
    $car['model'],
    $car['price']
]);
$related = $stmt2->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($car['title']) ?> — Luxury Auto Garage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --accent: #800039;
            --bg: #050505;
            --card: #0d0d0d;
            --text: #eaeaea;
            --muted: #9ca3af;
        }

        body {
            background: radial-gradient(circle at top, #151515, #050505 55%);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .navbar-custom {
            background: #000;
            border-bottom: 2px solid var(--accent);
            padding: 14px 0;
        }

        .logo {
            color: var(--accent);
            font-size: 22px;
            font-weight: 900;
            text-decoration: none;
        }

        .page-wrapper {
            padding: 35px 0;
        }

        .car-box {
            background: var(--card);
            border: 1px solid #1f1f1f;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 0 35px rgba(128, 0, 57, 0.22);
        }

        .car-img {
            width: 100%;
            height: 470px;
            object-fit: cover;
            background: #111;
        }

        .car-info {
            padding: 30px;
        }

        .car-title {
            color: #fff;
            font-size: 32px;
            font-weight: 900;
            margin-bottom: 8px;
        }

        .car-subtitle {
            color: var(--muted);
            font-size: 15px;
            margin-bottom: 18px;
        }

        .price {
            color: var(--accent);
            font-size: 30px;
            font-weight: 900;
            margin-bottom: 22px;
        }

        .spec-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 25px;
        }

        .spec-card {
            background: #111;
            border: 1px solid #222;
            border-radius: 16px;
            padding: 14px;
        }

        .spec-card i {
            color: var(--accent);
            margin-bottom: 8px;
            font-size: 18px;
        }

        .spec-label {
            color: var(--muted);
            font-size: 12px;
        }

        .spec-value {
            color: #fff;
            font-weight: 800;
            margin-top: 3px;
        }

        .description-box {
            background: #111;
            border: 1px solid #222;
            border-radius: 18px;
            padding: 18px;
            color: #ccc;
            line-height: 1.7;
            margin-bottom: 22px;
        }

        .seller-box {
            background: linear-gradient(145deg, #0d0d0d, #151015);
            border: 1px solid rgba(128, 0, 57, 0.5);
            border-radius: 18px;
            padding: 18px;
            margin-bottom: 22px;
        }

        .seller-title {
            color: var(--accent);
            font-weight: 900;
            margin-bottom: 6px;
        }

        .btn-custom {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 14px;
            text-decoration: none;
            font-weight: 800;
            transition: 0.3s;
        }

        .btn-back {
            background: var(--accent);
            color: #fff;
        }

        .btn-back:hover {
            color: #fff;
            filter: brightness(1.15);
            transform: translateY(-2px);
        }

        .btn-seller {
            background: #111;
            border: 1px solid var(--accent);
            color: #fff;
            margin-left: 10px;
        }

        .btn-seller:hover {
            color: #fff;
            background: var(--accent);
            transform: translateY(-2px);
        }

        .related-title {
            margin-top: 42px;
            margin-bottom: 18px;
            color: var(--accent);
            font-weight: 900;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .related-link {
            text-decoration: none;
            color: #fff;
        }

        .related-card {
            background: var(--card);
            border: 1px solid #1f1f1f;
            border-radius: 18px;
            overflow: hidden;
            transition: 0.3s;
            height: 100%;
        }

        .related-card:hover {
            transform: translateY(-6px);
            border-color: var(--accent);
            box-shadow: 0 15px 30px rgba(128, 0, 57, 0.25);
        }

        .related-card img {
            width: 100%;
            height: 145px;
            object-fit: cover;
            background: #111;
        }

        .related-body {
            padding: 12px;
        }

        .related-name {
            font-weight: 800;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .related-price {
            color: var(--accent);
            font-weight: 900;
        }

        .empty-related {
            color: var(--muted);
            background: #111;
            border: 1px solid #222;
            border-radius: 16px;
            padding: 18px;
        }

        @media (max-width: 992px) {
            .spec-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .related-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .car-img {
                height: 330px;
            }
        }

        @media (max-width: 576px) {

            .spec-grid,
            .related-grid {
                grid-template-columns: 1fr;
            }

            .btn-seller {
                margin-left: 0;
                margin-top: 10px;
            }

            .btn-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar-custom">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-car"></i> Luxury Auto Garage
            </a>

            <a href="index.php" class="btn-custom btn-back">
                <i class="fa-solid fa-arrow-left"></i> Назад
            </a>
        </div>
    </nav>

    <div class="container page-wrapper">

        <div class="car-box">

            <img class="car-img"
                src="<?= !empty($car['image']) ? htmlspecialchars($car['image']) : 'https://via.placeholder.com/1000x500' ?>"
                alt="<?= htmlspecialchars($car['title']) ?>">

            <div class="car-info">

                <div class="car-title">
                    <?= htmlspecialchars($car['title']) ?>
                </div>

                <div class="car-subtitle">
                    <?= htmlspecialchars($car['brand']) ?>
                    <?= htmlspecialchars($car['model']) ?>
                    • <?= (int) $car['year'] ?> год
                </div>

                <div class="price">
                    <?= htmlspecialchars($car['price']) ?> $
                </div>
                <?php if ($predictedPrice): ?>

                    <div style="
        background:#111;
        border:1px solid #800039;
        border-radius:16px;
        padding:16px;
        margin-bottom:20px;
    ">

                        <div style="
            color:#800039;
            font-weight:900;
            margin-bottom:8px;
        ">
                            🤖 AI-прогноз стоимости
                        </div>

                        <div style="color:#ddd;">
                            Средняя рыночная стоимость похожих автомобилей:
                            <b>
                                <?= number_format($predictedPrice, 0, '.', ' ') ?> $
                            </b>
                        </div>

                        <div style="margin-top:8px;color:#aaa;">

                            <?php if ($car['price'] > $predictedPrice): ?>

                                Цена автомобиля выше средней рыночной стоимости.

                            <?php elseif ($car['price'] < $predictedPrice): ?>

                                Цена автомобиля ниже средней рыночной стоимости.

                            <?php else: ?>

                                Цена автомобиля соответствует рыночной стоимости.

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endif; ?>
                <div class="spec-grid">

                    <div class="spec-card">
                        <i class="fa-solid fa-industry"></i>
                        <div class="spec-label">Марка</div>
                        <div class="spec-value"><?= htmlspecialchars($car['brand']) ?></div>
                    </div>

                    <div class="spec-card">
                        <i class="fa-solid fa-car-side"></i>
                        <div class="spec-label">Модель</div>
                        <div class="spec-value"><?= htmlspecialchars($car['model']) ?></div>
                    </div>

                    <div class="spec-card">
                        <i class="fa-solid fa-calendar"></i>
                        <div class="spec-label">Год выпуска</div>
                        <div class="spec-value"><?= (int) $car['year'] ?></div>
                    </div>

                    <div class="spec-card">
                        <i class="fa-solid fa-road"></i>
                        <div class="spec-label">Пробег</div>
                        <div class="spec-value"><?= (int) $car['mileage'] ?> км</div>
                    </div>
                    <div class="spec-card">
                        <i class="fa-solid fa-gauge-high"></i>

                        <div class="spec-label">
                            Объём двигателя
                        </div>

                        <div class="spec-value">
                            <?= htmlspecialchars($car['engine_volume'] ?: 'Не указан') ?>
                        </div>
                    </div>

                </div>

                <div class="seller-box">
                    <div class="seller-title">
                        <i class="fa-solid fa-user"></i> Продавец
                    </div>

                    <div>
                        <?= htmlspecialchars($car['seller']) ?>
                    </div>
                </div>

                <div class="description-box">
                    <?= !empty($car['description'])
                        ? nl2br(htmlspecialchars($car['description']))
                        : 'Описание автомобиля отсутствует.' ?>
                </div>

                <div>
                    <a href="index.php" class="btn-custom btn-back">
                        <i class="fa-solid fa-arrow-left"></i> Вернуться к каталогу
                    </a>

                    <a href="https://wa.me/37360000000?text=Здравствуйте,%20интересует%20автомобиль:%20<?= urlencode($car['title']) ?>"
                        class="btn-custom btn-seller" target="_blank">
                        <i class="fa-brands fa-whatsapp"></i> Написать продавцу
                    </a>
                </div>

            </div>

        </div>

        <h3 class="related-title">
            Похожие автомобили
        </h3>

        <?php if (empty($related)): ?>

            <div class="empty-related">
                Похожие автомобили пока не найдены.
            </div>

        <?php else: ?>

            <div class="related-grid">

                <?php foreach ($related as $r): ?>

                    <a href="car.php?id=<?= (int) $r['id'] ?>" class="related-link">

                        <div class="related-card">

                            <img src="<?= !empty($r['image']) ? htmlspecialchars($r['image']) : 'https://via.placeholder.com/300x200' ?>"
                                alt="<?= htmlspecialchars($r['title']) ?>">

                            <div class="related-body">

                                <div class="related-name">
                                    <?= htmlspecialchars($r['brand']) ?>
                                    <?= htmlspecialchars($r['model']) ?>
                                </div>

                                <div style="color:#999; font-size:13px;">
                                    <?= (int) $r['year'] ?> г. • <?= (int) $r['mileage'] ?> км
                                </div>

                                <div class="related-price">
                                    <?= htmlspecialchars($r['price']) ?> $
                                </div>

                            </div>

                        </div>

                    </a>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>