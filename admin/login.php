<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    require_once __DIR__ . '/../config/db.php';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required.';
    } else {
        $stmt = $conn->prepare('SELECT user_id, username, password, role, first_name, last_name FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && $user['password'] === $password) {
            $_SESSION['hotel_admin_logged_in'] = true;
            $_SESSION['hotel_admin_user'] = $user['username'];
            $_SESSION['hotel_admin_id'] = $user['user_id'];
            $_SESSION['hotel_admin_role'] = $user['role'];
            $_SESSION['hotel_admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login | GrandStay Hotel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #f6f1e7 0%, #e9f2ed 100%);
            min-height: 100vh;
            display: grid;
            place-items: center;
        }
        
        .login-container {
            width: min(92vw, 420px);
            background: white;
            padding: 40px 32px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(17, 55, 45, 0.15);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        
        .brand-mark {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #173b2d, #0f2922);
            color: white;
            font-weight: 700;
            border-radius: 12px;
            font-size: 1.4rem;
        }
        
        .brand h1 {
            margin: 0;
            font-size: 1.4rem;
            color: #173b2d;
        }
        
        .login-header p {
            margin: 0;
            color: #7d7d7d;
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #173b2d;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d8d8d8;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: border 0.2s;
        }
        
        input:focus {
            outline: none;
            border-color: #173b2d;
            box-shadow: 0 0 0 3px rgba(23, 59, 45, 0.1);
        }
        
        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px 14px;
            background: linear-gradient(135deg, #173b2d, #0f2922);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(17, 55, 45, 0.2);
        }
        
        .demo-info {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #e0d5c5;
            text-align: center;
            font-size: 0.85rem;
            color: #7d7d7d;
        }
        
        .demo-info strong {
            color: #173b2d;
            display: block;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="brand">
                <div class="brand-mark">G</div>
                <h1>GrandStay</h1>
            </div>
            <p>Admin Portal</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" name="username" type="text" placeholder="admin" required autofocus />
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" placeholder="admin123" required />
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <div class="demo-info">
            <strong>Demo Credentials</strong>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
    </div>
</body>
</html>
