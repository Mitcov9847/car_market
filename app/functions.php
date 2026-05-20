<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

/* =========================
   SANITIZE
========================= */
function sanitize($str)
{
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

/* =========================
   REGISTER
========================= */
function registerUser($name, $email, $password)
{
    global $pdo;

    $name = trim($name);
    $email = trim(strtolower($email));

    if (empty($name) || empty($email) || empty($password)) {
        return false;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $hash]);
    } catch (PDOException $e) {
        return false;
    }
}

/* =========================
   LOGIN
========================= */
function loginUser($email, $password)
{
    global $pdo;

    $email = trim(strtolower($email));

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            return true;
        }

        return false;
    } catch (PDOException $e) {
        return false;
    }
}

/* =========================
   CHECK LOGIN
========================= */
function isLogged()
{
    return isset($_SESSION['user_id']) && $_SESSION['user_id'] > 0;
}

/* =========================
   GET CARS (🔥 FIXED PRO)
========================= */
function getCars($limit = 200)
{
    global $pdo;

    $limit = (int) max(1, min(200, $limit));

    try {
        $stmt = $pdo->prepare("
            SELECT 
                cars.*,
                COALESCE(users.name, 'Unknown') AS seller
            FROM cars
            LEFT JOIN users ON cars.seller_id = users.id
            ORDER BY cars.created_at DESC
            LIMIT ?
        ");

        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}

/* =========================
   GET ONE CAR
========================= */
function getCar($id)
{
    global $pdo;

    $id = (int) $id;

    if ($id <= 0)
        return false;

    try {
        $stmt = $pdo->prepare("
            SELECT 
                cars.*,
                COALESCE(users.name, 'Unknown') AS seller
            FROM cars
            LEFT JOIN users ON cars.seller_id = users.id
            WHERE cars.id = ?
        ");

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        return false;
    }
}

/* =========================
   ADD CAR (SAFE VERSION)
========================= */
function addCar($seller_id, $title, $desc, $brand, $model, $body_type, $engine_volume, $year, $mileage, $price, $image)
{
    global $pdo;

    $seller_id = (int) $seller_id;
    $year = (int) $year;
    $mileage = (int) $mileage;
    $price = (float) $price;

    $title = trim($title);
    $desc = trim($desc);
    $brand = trim($brand);
    $model = trim($model);
    $body_type = trim($body_type);
    $engine_volume = trim($engine_volume);

    if ($seller_id <= 0 || empty($title) || empty($brand) || empty($model)) {
        return false;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO cars 
            (seller_id, title, description, brand, model, body_type, engine_volume, year, mileage, price, image, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW())
        ");

        return $stmt->execute([
            $seller_id,
            $title,
            $desc,
            $brand,
            $model,
            $body_type,
            $engine_volume,
            $year,
            $mileage,
            $price,
            $image
        ]);

    } catch (PDOException $e) {
        error_log($e->getMessage());
        return false;
    }
}
function predictCarPrice($brand, $body_type, $year, $mileage)
{
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT price, year, mileage
        FROM cars
        WHERE brand = ?
        AND body_type = ?
        AND status = 'available'
    ");

    $stmt->execute([$brand, $body_type]);

    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($cars) === 0) {
        return null;
    }

    $total = 0;
    $count = 0;

    foreach ($cars as $car) {

        $price = (float) $car['price'];

        $yearDiff = abs($year - (int) $car['year']);
        $price -= $yearDiff * 250;

        $mileageDiff = abs($mileage - (int) $car['mileage']);
        $price -= ($mileageDiff / 10000) * 100;

        if ($price > 0) {
            $total += $price;
            $count++;
        }
    }

    if ($count === 0) {
        return null;
    }

    return round($total / $count);
}