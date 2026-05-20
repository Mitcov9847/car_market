<?php
require_once '../app/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    if (registerUser($name, $email, $pass)) {
        header('Location: login.php');
        exit;
    } else {
        $error = "Ошибка регистрации! Попробуйте другой email.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <title>Регистрация — Luxury Auto Garage</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --accent: #800039;
            --black: #000;
            --bg: #050505;
            --card: #0d0d0d;
            --text: #eaeaea;
            --muted: #9ca3af;
        }

        body {
            background: radial-gradient(circle at top, #111, #000);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
        }

        .register-card {
            width: 100%;
            max-width: 430px;
            background: var(--card);
            border: 1px solid #1f1f1f;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 0 30px rgba(128, 0, 57, 0.25);
            transition: 0.3s;
        }

        .register-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0 40px rgba(128, 0, 57, 0.4);
        }

        h2 {
            text-align: center;
            font-weight: 900;
            color: var(--accent);
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .form-control {
            background: #111;
            border: 1px solid #222;
            color: #fff;
            border-radius: 12px;
            padding: 10px 14px;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 10px rgba(128, 0, 57, 0.3);
            background: #111;
            color: #fff;
        }

        .btn-success {
            width: 100%;
            border-radius: 14px;
            padding: 10px;
            font-weight: 700;
            background: var(--accent);
            border: none;
            transition: 0.3s;
        }

        .btn-success:hover {
            filter: brightness(1.2);
        }

        .error-message {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
            padding: 8px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .footer-text {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
            color: var(--muted);
        }

        .footer-text a {
            color: var(--accent);
            text-decoration: none;
        }
    </style>
</head>

<body>

    <div class="register-card">

        <h2><i class="fa fa-user-plus"></i> Регистрация</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">

            <div class="mb-3">
                <input type="text" name="name" class="form-control" placeholder="Имя" required>
            </div>

            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>

            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Пароль" required>
            </div>

            <button type="submit" class="btn btn-success">
                Создать аккаунт
            </button>

        </form>

        <div class="footer-text">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </div>

    </div>

</body>

</html>