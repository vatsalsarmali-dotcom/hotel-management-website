<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['hotel_admin_logged_in'] = true;
        $_SESSION['hotel_admin_user'] = $username;
        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid username or password.';
} else {
    $error = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login | GrandStay</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f4efe8, #e9f2ed);
            display: grid;
            place-items: center;
            min-height: 100vh;
        }
        .login-box {
            width: min(92vw, 420px);
            background: #fff;
            padding: 32px 28px;
            border-radius: 18px;
            box-shadow: 0 18px 35px rgba(17, 55, 45, 0.1);
        }
        h1 {
            margin-top: 0;
            color: #173b2d;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 10px;
            color: #214c3d;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 12px 14px;
            margin-bottom: 18px;
            border: 1px solid #d8d8d8;
            border-radius: 10px;
        }
        button {
            width: 100%;
            background: #1d4d3c;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 16px;
            font-weight: 700;
            cursor: pointer;
        }
        .error {
            color: #b00020;
            margin-bottom: 14px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>GrandStay Admin</h1>
        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" placeholder="admin" required />

            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="admin123" required />

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
