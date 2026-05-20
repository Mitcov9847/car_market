<?php
require_once '../app/functions.php';
if(!isLogged()) { header('Location: login.php'); exit; }

$user_id = $_SESSION['user_id'];
$car_id = $_GET['id'] ?? 0;

// Удаляем только своё авто
global $pdo;
$stmt = $pdo->prepare("DELETE FROM cars WHERE id=? AND seller_id=?");
$stmt->execute([$car_id,$user_id]);

header('Location: profile.php'); exit;