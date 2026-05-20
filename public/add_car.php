<?php
require_once '../app/functions.php';

if (!isLogged()) {
    header("Location: login.php");
    exit;
}

$error = '';
$imagePath = 'uploads/no-image.png';
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
        $original = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($ext, $allowed)) {
            $error = "Недопустимый формат изображения.";
        } else {
            $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9._-]/", "_", $original);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($fileTmp, $filePath)) {
                $imagePath = 'uploads/' . $fileName;
            } else {
                $error = "Ошибка загрузки файла.";
            }
        }
    }

    if (!$error) {
        $added = addCar(
            $_SESSION['user_id'],
            $title,
            $desc,
            $brand,
            $model,
            $body_type,
            $engine_volume,
            $year,
            $mileage,
            $price,
            $imagePath
        );

        if ($added) {
            header("Location: profile.php");
            exit;
        } else {
            $error = "Не удалось добавить автомобиль.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Добавить авто — Luxury Auto Garage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --accent: #800039;
            --card: #0d0d0d;
            --text: #eaeaea;
            --muted: #9ca3af;
        }

        body {
            background: radial-gradient(circle at top, #151515, #000);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 30px 15px;
            color: var(--text);
        }

        .wrapper {
            max-width: 650px;
            margin: 0 auto;
        }

        .top-link {
            display: inline-block;
            margin-bottom: 15px;
            color: #fff;
            text-decoration: none;
            border: 1px solid #333;
            background: #111;
            padding: 9px 15px;
            border-radius: 12px;
            font-weight: 600;
        }

        .top-link:hover {
            color: #fff;
            border-color: var(--accent);
        }

        .addcar-card {
            background: var(--card);
            border: 1px solid #1f1f1f;
            border-radius: 22px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(128, 0, 57, 0.25);
        }

        h2 {
            text-align: center;
            font-weight: 900;
            color: var(--accent);
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: var(--muted);
            font-size: 14px;
            margin-bottom: 25px;
        }

        label {
            font-size: 13px;
            color: #bbb;
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
            border-color: var(--accent);
            box-shadow: 0 0 12px rgba(128, 0, 57, 0.3);
            background: #111;
            color: #fff;
        }

        .form-select option {
            background: #111;
            color: #fff;
        }

        textarea.form-control {
            min-height: 105px;
            resize: vertical;
        }

        .btn-primary {
            width: 100%;
            border-radius: 14px;
            padding: 11px;
            font-weight: 800;
            background: var(--accent);
            border: none;
        }

        .alert {
            border-radius: 14px;
            text-align: center;
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.35);
            color: #ff8a8a;
        }
    </style>
</head>

<body>

    <div class="wrapper">

        <a href="profile.php" class="top-link">← Назад в профиль</a>

        <div class="addcar-card">

            <h2>Добавить автомобиль</h2>
            <div class="subtitle">Заполните данные автомобиля для публикации объявления</div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="mb-3">
                    <label>Название объявления</label>
                    <input class="form-control" name="title" placeholder="Например: BMW X3 2019" required>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Марка</label>
                        <input class="form-control" name="brand" placeholder="BMW" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Модель</label>
                        <input class="form-control" name="model" placeholder="X3" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Тип кузова</label>
                    <select class="form-select" name="body_type">
                        <option value="">Не указан</option>
                        <option value="Sedan">Седан</option>
                        <option value="SUV">SUV / Внедорожник</option>
                        <option value="Crossover">Кроссовер</option>
                        <option value="Hatchback">Хэтчбек</option>
                        <option value="Universal">Универсал</option>
                        <option value="Coupe">Купе</option>
                        <option value="Pickup">Пикап</option>
                        <option value="Minivan">Минивэн</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Объём двигателя</label>

                    <select class="form-select" name="engine_volume">

                        <option value="">Не указан</option>

                        <option value="1.0">1.0</option>
                        <option value="1.2">1.2</option>
                        <option value="1.4">1.4</option>
                        <option value="1.5">1.5</option>
                        <option value="1.6">1.6</option>
                        <option value="1.8">1.8</option>
                        <option value="2.0">2.0</option>
                        <option value="2.5">2.5</option>
                        <option value="3.0">3.0</option>
                        <option value="4.0">4.0</option>

                    </select>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Год</label>
                        <input class="form-control" name="year" type="number" placeholder="2019" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Пробег, км</label>
                        <input class="form-control" name="mileage" type="number" placeholder="120000" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label>Цена, $</label>
                        <input class="form-control" name="price" type="number" placeholder="25000" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label>Описание</label>
                    <textarea class="form-control" name="description"
                        placeholder="Опишите состояние, комплектацию, расход и особенности"></textarea>
                </div>

                <div class="mb-4">
                    <label>Фото автомобиля</label>
                    <input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,.gif">
                </div>

                <button class="btn btn-primary" type="submit">
                    Добавить авто
                </button>

            </form>

        </div>

    </div>

</body>

</html>