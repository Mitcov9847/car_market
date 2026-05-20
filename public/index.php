<?php
require_once '../app/functions.php';

$brand = $_GET['brand'] ?? '';
$model = $_GET['model'] ?? '';
$bodyType = $_GET['body_type'] ?? '';
$yearFrom = $_GET['year_from'] ?? '';
$yearTo = $_GET['year_to'] ?? '';
$priceFrom = $_GET['price_from'] ?? '';
$priceTo = $_GET['price_to'] ?? '';
$mileageTo = $_GET['mileage_to'] ?? '';
$sort = $_GET['sort'] ?? 'new';

global $pdo;

$sql = "SELECT * FROM cars WHERE 1=1";
$params = [];

if (!empty($brand)) {
    $sql .= " AND brand LIKE ?";
    $params[] = "%$brand%";
}

if (!empty($model)) {
    $sql .= " AND model LIKE ?";
    $params[] = "%$model%";
}

if (!empty($bodyType)) {
    $sql .= " AND body_type = ?";
    $params[] = $bodyType;
}

if (!empty($yearFrom)) {
    $sql .= " AND year >= ?";
    $params[] = (int) $yearFrom;
}

if (!empty($yearTo)) {
    $sql .= " AND year <= ?";
    $params[] = (int) $yearTo;
}

if (!empty($priceFrom)) {
    $sql .= " AND price >= ?";
    $params[] = (float) $priceFrom;
}

if (!empty($priceTo)) {
    $sql .= " AND price <= ?";
    $params[] = (float) $priceTo;
}

if (!empty($mileageTo)) {
    $sql .= " AND mileage <= ?";
    $params[] = (int) $mileageTo;
}

if ($sort === 'price_asc') {
    $sql .= " ORDER BY price ASC";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY price DESC";
} elseif ($sort === 'year_desc') {
    $sql .= " ORDER BY year DESC";
} elseif ($sort === 'mileage_asc') {
    $sql .= " ORDER BY mileage ASC";
} else {
    $sql .= " ORDER BY created_at DESC";
}

$perPage = 8;

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

$offset = ($page - 1) * $perPage;

$countSql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);

$totalCars = (int) $countStmt->fetchColumn();

$totalPages = ceil($totalCars / $perPage);

$sql .= " LIMIT " . (int) $perPage . " OFFSET " . (int) $offset;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Luxury Auto Garage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --accent: #800039;
            --accent-light: #b30052;
            --bg: #050505;
            --card: #151515;
            --text: #eaeaea;
            --muted: #aaa;
            --border: #2a2a2a;
        }

        body {
            background: radial-gradient(circle at top, #181818, #050505 55%);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .top-marquee {
            background: #000;
            border-bottom: 1px solid #1f1f1f;
            overflow: hidden;
            padding: 12px 0;
        }

        .marquee {
            white-space: nowrap;
            display: flex;
            gap: 55px;
            animation: move 25s linear infinite;
            font-size: 14px;
            font-weight: 700;
        }

        .marquee span {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .marquee i {
            color: var(--accent-light);
        }

        @keyframes move {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .navbar {
            background: #000 !important;
            border-bottom: 2px solid var(--accent);
            padding: 14px 0;
        }

        .logo {
            color: var(--accent-light) !important;
            font-weight: 900;
            font-size: 1.5rem;
        }

        .nav-btn {
            margin-left: 10px;
            padding: 8px 14px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-login {
            border: 1px solid var(--accent);
            color: #fff;
        }

        .btn-login:hover,
        .btn-contacts:hover {
            background: var(--accent);
            color: #fff;
        }

        .btn-register {
            background: var(--accent);
            color: #fff;
        }

        .btn-register:hover {
            color: #fff;
            filter: brightness(1.15);
        }

        .btn-contacts {
            background: #111;
            border: 1px solid var(--accent);
            color: #fff;
        }

        h1 {
            text-align: center;
            font-weight: 900;
            margin: 30px 0;
            color: #fff;
        }

        .filter-box {
            background: #101010;
            padding: 20px;
            border-radius: 20px;
            border: 1px solid var(--border);
            margin-bottom: 30px;
            box-shadow: 0 0 25px rgba(128, 0, 57, 0.12);
        }

        .form-control,
        .form-select {
            background: #181818 !important;
            border: 1px solid #333 !important;
            color: #fff !important;
            border-radius: 13px !important;
            padding: 10px 12px;
        }

        .form-control::placeholder {
            color: #777;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--accent-light) !important;
            box-shadow: 0 0 12px rgba(179, 0, 82, 0.25) !important;
        }

        .form-select option {
            background: #111;
            color: #fff;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border: none;
            border-radius: 13px;
            font-weight: 800;
        }

        .btn-primary:hover {
            filter: brightness(1.12);
        }

        .btn-secondary {
            background: #181818;
            border: 1px solid #333;
            border-radius: 13px;
            font-weight: 700;
            color: #fff;
        }

        .btn-secondary:hover {
            background: #222;
            color: #fff;
        }

        .ai-box {
            margin-top: 35px;
            margin-bottom: 40px;
            background: linear-gradient(145deg, #101010, #171717);
            border: 1px solid rgba(179, 0, 82, 0.55);
            border-radius: 26px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 0 35px rgba(128, 0, 57, 0.20);
        }

        .ai-box::before {
            content: "";
            position: absolute;
            top: -100px;
            right: -100px;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(179, 0, 82, 0.22), transparent 70%);
            pointer-events: none;
        }

        .ai-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
        }

        .ai-icon {
            width: 64px;
            height: 64px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(145deg, var(--accent), var(--accent-light));
            color: #fff;
            font-size: 28px;
            box-shadow: 0 0 20px rgba(179, 0, 82, 0.45);
        }

        .ai-title {
            font-size: 25px;
            font-weight: 900;
            color: #fff;
        }

        .ai-subtitle {
            color: var(--muted);
            font-size: 14px;
            margin-top: 3px;
        }

        .ai-textarea {
            background: #151515 !important;
            border: 1px solid #333 !important;
            border-radius: 18px !important;
            color: #fff !important;
            padding: 16px !important;
            resize: none;
            font-size: 15px;
        }

        .ai-button {
            margin-top: 16px;
            background: linear-gradient(145deg, var(--accent), var(--accent-light));
            color: #fff;
            border: none;
            border-radius: 16px;
            padding: 12px 22px;
            font-weight: 800;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: 0.3s;
        }

        .ai-button:hover {
            transform: translateY(-3px);
            filter: brightness(1.1);
        }

        .ai-response {
            margin-top: 22px;
        }

        .cars-title {
            color: #fff;
            font-weight: 900;
            margin-bottom: 20px;
        }

        .car-card {
            background: #171717;
            border: 1px solid #303030;
            border-radius: 22px;
            overflow: hidden;
            transition: 0.3s;
            height: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
        }

        .car-card:hover {
            transform: translateY(-7px);
            border-color: var(--accent-light);
            box-shadow: 0 20px 35px rgba(128, 0, 57, 0.32);
        }

        .car-image-wrapper {
            position: relative;
            height: 220px;
            overflow: hidden;
            background: #202020;
        }

        .car-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.35s;
        }

        .car-card:hover .car-image {
            transform: scale(1.06);
        }

        .car-status {
            position: absolute;
            top: 12px;
            left: 12px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: white;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 800;
        }

        .car-body {
            padding: 18px;
        }

        .car-title {
            color: #fff;
            font-weight: 900;
            font-size: 18px;
            margin-bottom: 6px;
            min-height: 44px;
            line-height: 1.3;
        }

        .car-brand {
            color: #bcbcbc;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .car-price {
            color: #ff2d75;
            font-size: 25px;
            font-weight: 900;
            margin-bottom: 15px;
        }

        .car-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 16px;
        }

        .car-info div {
            background: #222;
            border: 1px solid #373737;
            padding: 9px 10px;
            border-radius: 12px;
            color: #e4e4e4;
            font-size: 13px;
            line-height: 1.2;
        }

        .car-info div:nth-child(3) {
            grid-column: 1 / 3;
        }

        .car-btn {
            display: block;
            text-align: center;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 14px;
            font-weight: 800;
            transition: 0.3s;
        }

        .car-btn:hover {
            transform: translateY(-2px);
            filter: brightness(1.1);
            color: white;
        }

        .empty-box {
            background: #151515;
            border: 1px solid #303030;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            color: #aaa;
        }

        footer {
            margin-top: 55px;
            background: #000;
            border-top: 2px solid var(--accent);
            padding: 20px;
            text-align: center;
            color: #aaa;
        }

        .pagination-box {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 35px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }

        .pagination-btn {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #151515;
            border: 1px solid #333;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
        }

        .pagination-btn:hover {
            border-color: #800039;
            color: #fff;
        }

        .active-page {
            background: #800039;
            border-color: #800039;
        }
    </style>
</head>

<body>

    <div class="top-marquee">
        <div class="marquee">
            <span><i class="fa-solid fa-shield-halved"></i> Гарантия</span>
            <span><i class="fa-solid fa-car"></i> Большой выбор</span>
            <span><i class="fa-solid fa-magnifying-glass"></i> Проверка авто</span>
            <span><i class="fa-solid fa-flag-checkered"></i> Тест-драйв</span>
            <span><i class="fa-solid fa-gem"></i> Надёжные автомобили</span>
            <span><i class="fa-solid fa-robot"></i> AI подбор авто</span>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">

            <a href="index.php" class="navbar-brand logo">
                <i class="fas fa-car"></i> Luxury Auto Garage
            </a>

            <div class="ms-auto d-flex align-items-center">

                <?php if (isLogged()): ?>

                    <a href="add_car.php" class="nav-btn btn-register">
                        <i class="fa fa-plus"></i> Добавить
                    </a>

                    <a href="profile.php" class="nav-btn btn-contacts">
                        <i class="fa fa-user"></i> Профиль
                    </a>

                    <a href="contacts.php" class="nav-btn btn-contacts">
                        <i class="fa fa-phone"></i> Контакты
                    </a>

                    <a href="logout.php" class="nav-btn btn-login">
                        <i class="fa fa-right-from-bracket"></i>
                    </a>

                <?php else: ?>

                    <a href="login.php" class="nav-btn btn-login">Вход</a>
                    <a href="register.php" class="nav-btn btn-register">Регистрация</a>



                <?php endif; ?>


            </div>
        </div>
    </nav>

    <div class="container">

        <h1>Актуальные машины</h1>

        <form method="GET" class="filter-box row g-2">

            <div class="col-md-2">

                <?php
                $brandStmt = $pdo->query("
        SELECT DISTINCT brand
        FROM cars
        WHERE brand IS NOT NULL
        AND brand != ''
        ORDER BY brand ASC
    ");

                $brands = $brandStmt->fetchAll(PDO::FETCH_COLUMN);
                ?>

                <select class="form-select" name="brand">

                    <option value="">Выберите марку</option>

                    <?php foreach ($brands as $b): ?>

                        <option value="<?= htmlspecialchars($b) ?>" <?= $brand === $b ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </div>

            <div class="col-md-2">
                <input class="form-control" name="model" placeholder="Модель" value="<?= htmlspecialchars($model) ?>">
            </div>

            <div class="col-md-2">
                <select class="form-select" name="body_type">
                    <option value="">Тип кузова</option>
                    <option value="Sedan" <?= $bodyType === 'Sedan' ? 'selected' : '' ?>>Седан</option>
                    <option value="SUV" <?= $bodyType === 'SUV' ? 'selected' : '' ?>>SUV</option>
                    <option value="Crossover" <?= $bodyType === 'Crossover' ? 'selected' : '' ?>>Кроссовер</option>
                    <option value="Hatchback" <?= $bodyType === 'Hatchback' ? 'selected' : '' ?>>Хэтчбек</option>
                    <option value="Universal" <?= $bodyType === 'Universal' ? 'selected' : '' ?>>Универсал</option>
                    <option value="Coupe" <?= $bodyType === 'Coupe' ? 'selected' : '' ?>>Купе</option>
                    <option value="Pickup" <?= $bodyType === 'Pickup' ? 'selected' : '' ?>>Пикап</option>
                    <option value="Minivan" <?= $bodyType === 'Minivan' ? 'selected' : '' ?>>Минивэн</option>
                </select>
            </div>

            <div class="col-md-2">
                <input class="form-control" name="year_from" type="number" placeholder="Год от"
                    value="<?= htmlspecialchars($yearFrom) ?>">
            </div>

            <div class="col-md-2">
                <input class="form-control" name="price_to" type="number" placeholder="Цена до"
                    value="<?= htmlspecialchars($priceTo) ?>">
            </div>

            <div class="col-md-2">
                <select class="form-select" name="sort">
                    <option value="new" <?= $sort === 'new' ? 'selected' : '' ?>>Сначала новые</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Цена ↑</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Цена ↓</option>
                    <option value="year_desc" <?= $sort === 'year_desc' ? 'selected' : '' ?>>Свежие</option>
                    <option value="mileage_asc" <?= $sort === 'mileage_asc' ? 'selected' : '' ?>>Меньше пробег</option>
                </select>
            </div>

            <div class="col-12 text-end mt-2">
                <button class="btn btn-primary px-4">
                    <i class="fa fa-filter"></i> Фильтр
                </button>

                <a href="index.php" class="btn btn-secondary px-4">
                    Сбросить
                </a>
            </div>

        </form>

        <div class="ai-box">

            <div class="ai-header">
                <div class="ai-icon">
                    <i class="fa-solid fa-robot"></i>
                </div>

                <div>
                    <div class="ai-title">AI подбор авто</div>
                    <div class="ai-subtitle">
                        Опишите желаемый автомобиль и система подберёт лучшие варианты
                    </div>
                </div>
            </div>

            <textarea id="msg" class="form-control ai-textarea" rows="4"
                placeholder="Например: хочу экономичную машину и высокую"></textarea>

            <button type="button" class="ai-button" onclick="askAI()">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Подобрать автомобиль
            </button>

            <div id="res" class="ai-response"></div>

        </div>

        <h2 class="cars-title">Объявления</h2>

        <?php if (empty($cars)): ?>

            <div class="empty-box">
                Автомобили не найдены. Попробуйте изменить фильтры.
            </div>

        <?php else: ?>

            <div class="row">

                <?php foreach ($cars as $car): ?>

                    <div class="col-md-4 col-lg-3 mb-4">

                        <div class="car-card">

                            <div class="car-image-wrapper">
                                <img src="<?= !empty($car['image']) ? htmlspecialchars($car['image']) : 'https://via.placeholder.com/400x250' ?>"
                                    class="car-image" alt="<?= htmlspecialchars($car['title']) ?>">

                                <div class="car-status">В наличии</div>
                            </div>

                            <div class="car-body">

                                <div class="car-title">
                                    <?= htmlspecialchars($car['title']) ?>
                                </div>

                                <div class="car-brand">
                                    <?= htmlspecialchars($car['brand']) ?>
                                    <?= htmlspecialchars($car['model']) ?>
                                </div>

                                <div class="car-price">
                                    <?= number_format((float) $car['price'], 0, '.', ' ') ?> $
                                </div>

                                <div class="car-info">
                                    <div>📅 <?= (int) $car['year'] ?></div>
                                    <div>🛣 <?= number_format((int) $car['mileage'], 0, '.', ' ') ?> км</div>
                                    <div>🚗 <?= htmlspecialchars($car['body_type'] ?: 'Кузов не указан') ?></div>
                                </div>

                                <a href="car.php?id=<?= (int) $car['id'] ?>" class="car-btn">
                                    Подробнее
                                </a>

                            </div>

                        </div>

                    </div>

                <?php endforeach; ?>
                <div class="row">

                    <?php foreach ($cars as $car): ?>

                        <!-- карточка машины -->

                    <?php endforeach; ?>

                </div>

                <?php if ($totalPages > 1): ?>

                    <div class="pagination-box">

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                                class="pagination-btn <?= $page == $i ? 'active-page' : '' ?>">
                                <?= $i ?>
                            </a>

                        <?php endfor; ?>

                    </div>

                <?php endif; ?>
            </div>

        <?php endif; ?>

    </div>

    <footer>
        © <?= date('Y') ?> Luxury Auto Garage
    </footer>

    <script>
        async function askAI() {
            let text = document.getElementById("msg").value.trim();
            let resultBox = document.getElementById("res");

            if (!text) {
                resultBox.innerHTML = "Напишите запрос.";
                return;
            }

            resultBox.innerHTML = "⏳ AI анализирует автомобили...";

            try {
                let res = await fetch("chat_api.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "message=" + encodeURIComponent(text)
                });

                let data = await res.json();
                resultBox.innerHTML = data.reply;

            } catch (e) {
                console.error(e);
                resultBox.innerHTML = "Ошибка AI помощника";
            }
        }
    </script>

</body>

</html>