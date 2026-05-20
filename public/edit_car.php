<?php
require_once '../app/functions.php';

if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$car_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($car_id <= 0) {
    die("Некорректный ID объявления.");
}

global $pdo;

$stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ? AND seller_id = ?");
$stmt->execute([$car_id, $user_id]);
$car = $stmt->fetch();

if (!$car) {
    die("Объявление не найдено или доступ запрещён.");
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $body_type = trim($_POST['body_type'] ?? '');
    $engine_volume = trim($_POST['engine_volume'] ?? '');
    $year = (int) ($_POST['year'] ?? 0);
    $mileage = (int) ($_POST['mileage'] ?? 0);
    $price = (float) ($_POST['price'] ?? 0);
    $desc = trim($_POST['description'] ?? '');
    $image = $car['image'];

    if ($title === '' || $brand === '' || $model === '') {
        $error = "Заполните название, марку и модель автомобиля.";
    } elseif ($year < 1950 || $year > (int) date('Y') + 1) {
        $error = "Укажите корректный год выпуска.";
    } elseif ($mileage < 0) {
        $error = "Пробег не может быть отрицательным.";
    } elseif ($price <= 0) {
        $error = "Цена должна быть больше нуля.";
    }

    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

        $uploadDir = __DIR__ . '/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileTmp = $_FILES['image']['tmp_name'];
        $originalName = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Недопустимый формат изображения. Разрешены: jpg, jpeg, png, gif, webp.";
        } else {
            $safeName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $originalName);
            $filePath = $uploadDir . $safeName;

            if (move_uploaded_file($fileTmp, $filePath)) {
                $image = 'uploads/' . $safeName;
            } else {
                $error = "Не удалось загрузить изображение.";
            }
        }
    }

    if (!$error) {
        $stmt = $pdo->prepare("
            UPDATE cars 
            SET title = ?, brand = ?, model = ?, body_type = ?, engine_volume = ?, year = ?, mileage = ?, price = ?, description = ?, image = ?
            WHERE id = ? AND seller_id = ?
        ");

        $updated = $stmt->execute([
            $title,
            $brand,
            $model,
            $body_type,
            $engine_volume,
            $year,
            $mileage,
            $price,
            $desc,
            $image,
            $car_id,
            $user_id
        ]);

        if ($updated) {
            header('Location: profile.php');
            exit;
        } else {
            $error = "Не удалось сохранить изменения.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Редактировать авто — Luxury Auto Garage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: #800039;
            --card: #0d0d0d;
            --text: #eaeaea;
            --muted: #9ca3af;
        }

        body {
            background: radial-gradient(circle at top, #111, #000);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 30px 15px;
        }

        .edit-wrapper {
            max-width: 760px;
            margin: 0 auto;
        }

        .edit-card {
            background: var(--card);
            border: 1px solid #1f1f1f;
            border-radius: 22px;
            padding: 30px;
            box-shadow: 0 0 35px rgba(128, 0, 57, 0.25);
        }

        .page-title {
            text-align: center;
            color: var(--accent);
            font-weight: 900;
            margin-bottom: 8px;
        }

        .page-subtitle {
            text-align: center;
            color: var(--muted);
            margin-bottom: 25px;
            font-size: 14px;
        }

        label {
            color: #bbb;
            font-size: 13px;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            background: #111;
            border: 1px solid #242424;
            color: #fff;
            border-radius: 14px;
            padding: 11px 14px;
        }

        .form-control:focus,
        .form-select:focus {
            background: #111;
            color: #fff;
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(128, 0, 57, 0.3);
        }

        .form-select option {
            background: #111;
            color: #fff;
        }

        textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }

        .preview-box {
            background: #111;
            border: 1px solid #222;
            border-radius: 18px;
            padding: 12px;
            margin-bottom: 20px;
        }

        .preview-img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
        }

        .btn-save {
            background: var(--accent);
            border: none;
            color: #fff;
            border-radius: 14px;
            padding: 11px 20px;
            font-weight: 800;
            transition: 0.3s;
        }

        .btn-save:hover {
            color: #fff;
            filter: brightness(1.15);
            transform: translateY(-2px);
        }

        .btn-back {
            background: #161616;
            border: 1px solid #333;
            color: #fff;
            border-radius: 14px;
            padding: 11px 20px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back:hover {
            color: #fff;
            border-color: var(--accent);
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 14px;
            border: 1px solid rgba(220, 53, 69, 0.35);
            background: rgba(220, 53, 69, 0.1);
            color: #ff8a8a;
        }
    </style>
</head>

<body>

    <div class="edit-wrapper">

        <div class="edit-card">

            <h2 class="page-title">Редактировать объявление</h2>
            <div class="page-subtitle">Обновите данные автомобиля и сохраните изменения</div>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($car['image'])): ?>
                <div class="preview-box">
                    <img src="<?= htmlspecialchars($car['image']) ?>" class="preview-img" alt="Фото автомобиля">
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label>Название объявления</label>
                    <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($car['title']) ?>"
                        required>
                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Марка</label>
                        <input type="text" name="brand" class="form-control"
                            value="<?= htmlspecialchars($car['brand']) ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Модель</label>
                        <input type="text" name="model" class="form-control"
                            value="<?= htmlspecialchars($car['model']) ?>" required>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label>Тип кузова</label>
                        <select class="form-select" name="body_type">
                            <option value="">Не указан</option>
                            <option value="Sedan" <?= ($car['body_type'] ?? '') === 'Sedan' ? 'selected' : '' ?>>Седан
                            </option>
                            <option value="SUV" <?= ($car['body_type'] ?? '') === 'SUV' ? 'selected' : '' ?>>SUV /
                                Внедорожник</option>
                            <option value="Crossover" <?= ($car['body_type'] ?? '') === 'Crossover' ? 'selected' : '' ?>>
                                Кроссовер</option>
                            <option value="Hatchback" <?= ($car['body_type'] ?? '') === 'Hatchback' ? 'selected' : '' ?>>
                                Хэтчбек</option>
                            <option value="Universal" <?= ($car['body_type'] ?? '') === 'Universal' ? 'selected' : '' ?>>
                                Универсал</option>
                            <option value="Coupe" <?= ($car['body_type'] ?? '') === 'Coupe' ? 'selected' : '' ?>>Купе
                            </option>
                            <option value="Pickup" <?= ($car['body_type'] ?? '') === 'Pickup' ? 'selected' : '' ?>>Пикап
                            </option>
                            <option value="Minivan" <?= ($car['body_type'] ?? '') === 'Minivan' ? 'selected' : '' ?>>
                                Минивэн</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Объём двигателя</label>
                        <select class="form-select" name="engine_volume">
                            <option value="">Не указан</option>
                            <option value="1.0" <?= ($car['engine_volume'] ?? '') === '1.0' ? 'selected' : '' ?>>1.0
                            </option>
                            <option value="1.2" <?= ($car['engine_volume'] ?? '') === '1.2' ? 'selected' : '' ?>>1.2
                            </option>
                            <option value="1.4" <?= ($car['engine_volume'] ?? '') === '1.4' ? 'selected' : '' ?>>1.4
                            </option>
                            <option value="1.5" <?= ($car['engine_volume'] ?? '') === '1.5' ? 'selected' : '' ?>>1.5
                            </option>
                            <option value="1.6" <?= ($car['engine_volume'] ?? '') === '1.6' ? 'selected' : '' ?>>1.6
                            </option>
                            <option value="1.8" <?= ($car['engine_volume'] ?? '') === '1.8' ? 'selected' : '' ?>>1.8
                            </option>
                            <option value="2.0" <?= ($car['engine_volume'] ?? '') === '2.0' ? 'selected' : '' ?>>2.0
                            </option>
                            <option value="2.5" <?= ($car['engine_volume'] ?? '') === '2.5' ? 'selected' : '' ?>>2.5
                            </option>
                            <option value="3.0" <?= ($car['engine_volume'] ?? '') === '3.0' ? 'selected' : '' ?>>3.0
                            </option>
                            <option value="4.0" <?= ($car['engine_volume'] ?? '') === '4.0' ? 'selected' : '' ?>>4.0
                            </option>
                        </select>
                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">
                        <label>Год выпуска</label>
                        <input type="number" name="year" class="form-control"
                            value="<?= htmlspecialchars($car['year']) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Пробег, км</label>
                        <input type="number" name="mileage" class="form-control"
                            value="<?= htmlspecialchars($car['mileage']) ?>" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Цена, $</label>
                        <input type="number" name="price" class="form-control"
                            value="<?= htmlspecialchars($car['price']) ?>" required>
                    </div>

                </div>

                <div class="mb-3">
                    <label>Описание</label>
                    <textarea name="description"
                        class="form-control"><?= htmlspecialchars($car['description'] ?? '') ?></textarea>
                </div>

                <div class="mb-4">
                    <label>Новое изображение</label>
                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.gif,.webp">
                </div>

                <div class="d-flex gap-2 justify-content-between">
                    <a href="profile.php" class="btn-back">Отмена</a>

                    <button class="btn-save" type="submit">
                        Сохранить изменения
                    </button>
                </div>

            </form>

        </div>

    </div>

</body>

</html>