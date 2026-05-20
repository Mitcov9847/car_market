<?php
require_once '../app/functions.php';

if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

global $pdo;
$stmt = $pdo->prepare("SELECT * FROM cars WHERE seller_id=? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$my_cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Профиль — Luxury Auto Garage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: #800039;
        }

        body {
            background: radial-gradient(circle at top, #111, #000);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 30px;
            color: #fff;
        }

        h2 {
            text-align: center;
            font-weight: 900;
            color: var(--accent);
            margin-bottom: 10px;
        }

        h3 {
            color: #ccc;
            margin-bottom: 20px;
        }

        .top-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }

        .btn {
            border-radius: 14px;
            padding: 8px 18px;
            font-weight: 600;
            border: none;
            transition: 0.3s;
        }

        .btn-success {
            background: var(--accent);
        }

        .btn-primary {
            background: #1a1a1a;
            border: 1px solid #333;
        }

        .btn-danger {
            background: #2a2a2a;
            border: 1px solid #444;
        }

        .btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.2);
        }

        .card {
            background: #0d0d0d;
            border: 1px solid #1f1f1f;
            border-radius: 16px;
            overflow: hidden;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 25px rgba(128, 0, 57, 0.25);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .card-body h5 {
            color: #fff;
        }

        .card-text {
            color: #aaa;
        }

        .price {
            color: var(--accent);
            font-weight: 700;
        }

        .btn-warning {
            background: var(--accent);
            border: none;
            color: #fff;
        }

        .btn-warning:hover {
            filter: brightness(1.2);
        }

        .btn-danger {
            background: #2a2a2a;
            border: 1px solid #444;
        }

        .text-muted {
            color: #888 !important;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Профиль</h2>
        <p class="text-center text-muted">Привет, <?= $_SESSION['user_name'] ?></p>

        <div class="top-buttons">
            <a href="add_car.php" class="btn btn-success">Добавить авто</a>
            <a href="index.php" class="btn btn-primary">Главная</a>
            <a href="logout.php" class="btn btn-danger">Выйти</a>
        </div>

        <h3>Мои объявления</h3>

        <?php if (empty($my_cars)): ?>
            <p class="text-center text-muted">Вы ещё не добавляли авто.</p>
        <?php else: ?>

            <div class="row">

                <?php foreach ($my_cars as $car): ?>
                    <div class="col-md-4 mb-4">

                        <div class="card h-100">

                            <img src="<?= $car['image'] ?? 'https://via.placeholder.com/300x200' ?>" class="card-img-top">

                            <div class="card-body">

                                <h5><?= sanitize($car['title']) ?></h5>

                                <p class="card-text">
                                    <?= sanitize($car['brand']) ?>         <?= sanitize($car['model']) ?>,
                                    <?= $car['year'] ?> г.
                                </p>

                                <p class="card-text">Пробег: <?= $car['mileage'] ?> км</p>

                                <p class="price"><?= $car['price'] ?> $</p>

                                <div class="d-flex justify-content-between mt-3">
                                    <a href="edit_car.php?id=<?= $car['id'] ?>" class="btn btn-warning btn-sm">Редактировать</a>
                                    <a href="delete_car.php?id=<?= $car['id'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Удалить объявление?')">Удалить</a>
                                </div>

                            </div>

                        </div>

                    </div>
                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</body>

</html>