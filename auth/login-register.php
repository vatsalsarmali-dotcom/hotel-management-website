<?php
session_start();
$error = '';
$success = '';

require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login form
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $error = 'Username and password are required.';
        } else {
            $stmt = $conn->prepare('SELECT user_id, username, password, role, first_name, last_name, email FROM users WHERE username = ?');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && $user['password'] === $password) {
                $_SESSION['hotel_user_logged_in'] = true;
                $_SESSION['hotel_user_id'] = $user['user_id'];
                $_SESSION['hotel_user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['hotel_user_email'] = $user['email'];
                $_SESSION['hotel_user_role'] = $user['role'];
                $_SESSION['hotel_user_username'] = $user['username'];

                if ($user['role'] === 'admin' || $user['role'] === 'staff') {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: customer-dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }
    } elseif (isset($_POST['register'])) {
        // Registration form
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $username = trim($_POST['reg_username'] ?? '');
        $password = trim($_POST['reg_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($username) || empty($password)) {
            $error = 'All fields are required.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            // Check if username exists
            $checkStmt = $conn->prepare('SELECT user_id FROM users WHERE username = ?');
            $checkStmt->bind_param('s', $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = 'Username already exists. Please choose another.';
            } else {
                // Register new user
                $role = 'customer';
                $stmt = $conn->prepare('INSERT INTO users (username, password, email, role, first_name, last_name, phone) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('sssssss', $username, $password, $email, $role, $firstName, $lastName, $phone);

                if ($stmt->execute()) {
                    $success = 'Registration successful! Please login with your credentials.';
                } else {
                    $error = 'Registration failed. Please try again.';
                }
            }
        }
    }
}

$isLoginPage = !isset($_POST['register']) && empty($error);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login & Register | GrandStay Hotel</title>
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
            padding: 20px;
        }

        .container {
            width: min(92vw, 420px);
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(17, 55, 45, 0.15);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #173b2d, #0f2922);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .header .brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.3rem;
        }

        .header h1 {
            margin: 0;
            font-size: 1.4rem;
        }

        .header p {
            margin: 5px 0 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .content {
            padding: 32px 28px;
        }

        .tabs {
            display: flex;
            gap: 0;
            margin-bottom: 24px;
            background: #f5f5f5;
            border-radius: 10px;
            padding: 4px;
        }

        .tab-button {
            flex: 1;
            padding: 12px;
            border: none;
            background: transparent;
            color: #7d7d7d;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .tab-button.active {
            background: white;
            color: #173b2d;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            color: #173b2d;
            font-weight: 600;
            font-size: 0.9rem;
        }

        input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d8d8d8;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.2s;
            font-family: inherit;
        }

        input:focus {
            outline: none;
            border-color: #173b2d;
            box-shadow: 0 0 0 3px rgba(23, 59, 45, 0.1);
        }

        .btn {
            width: 100%;
            padding: 11px 14px;
            background: linear-gradient(135deg, #173b2d, #0f2922);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(17, 55, 45, 0.2);
        }

        .btn:active {
            transform: translateY(0);
        }

        .divider {
            text-align: center;
            margin: 16px 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0d5c5;
        }

        .divider span {
            position: relative;
            background: white;
            padding: 0 10px;
            color: #7d7d7d;
            font-size: 0.85rem;
        }

        .back-link {
            display: inline-block;
            margin-top: 16px;
            padding: 10px 16px;
            background: #f5f5f5;
            color: #173b2d;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
        }

        .back-link:hover {
            background: #e8e8e8;
        }

        .info-text {
            text-align: center;
            margin-top: 12px;
            font-size: 0.85rem;
            color: #7d7d7d;
        }

        .info-text a {
            color: #173b2d;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .demo-box {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 12px;
            margin-top: 12px;
            font-size: 0.85rem;
            color: #0d47a1;
        }

        .demo-box strong {
            display: block;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="brand">
                <div class="brand-mark">G</div>
                <h1>GrandStay</h1>
            </div>
            <p>Hotel Management System</p>
        </div>

        <div class="content">
            <div class="tabs">
                <button class="tab-button active" onclick="switchTab('login')">Login</button>
                <button class="tab-button" onclick="switchTab('register')">Register</button>
            </div>

            <!-- Login Form -->
            <form id="login" class="form-section active" method="POST">
                <?php if ($error && isset($_POST['login'])): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" placeholder="Enter your username" required />
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Enter your password" required />
                </div>

                <button type="submit" name="login" class="btn">Login</button>

                <div class="demo-box">
                    <strong>Demo Credentials:</strong>
                    Admin: admin / admin123
                </div>

                <div class="divider">
                    <span>or</span>
                </div>

                <a href="index.html" class="back-link">Continue as Guest</a>
            </form>

            <!-- Register Form -->
            <form id="register" class="form-section" method="POST">
                <?php if ($error && isset($_POST['register'])): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success && !isset($_POST['register'])): ?>
                    <div class="success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input id="first_name" name="first_name" type="text" placeholder="First name" required />
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" type="text" placeholder="Last name" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="reg_email">Email</label>
                    <input id="reg_email" name="email" type="email" placeholder="your@email.com" required />
                </div>

                <div class="form-group">
                    <label for="reg_phone">Phone</label>
                    <input id="reg_phone" name="phone" type="tel" placeholder="9876543210" required />
                </div>

                <div class="form-group">
                    <label for="reg_username">Username</label>
                    <input id="reg_username" name="reg_username" type="text" placeholder="Choose a username" required />
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="reg_password">Password</label>
                        <input id="reg_password" name="reg_password" type="password" placeholder="Min 6 characters" required />
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input id="confirm_password" name="confirm_password" type="password" placeholder="Re-enter password" required />
                    </div>
                </div>

                <button type="submit" name="register" class="btn">Create Account</button>

                <div class="info-text">
                    Already have an account? <a onclick="switchTab('login')">Login here</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tabName) {
            // Hide all forms
            const forms = document.querySelectorAll('.form-section');
            forms.forEach(form => form.classList.remove('active'));

            // Show selected form
            document.getElementById(tabName).classList.add('active');

            // Update tab buttons
            const buttons = document.querySelectorAll('.tab-button');
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
        }

        // Prevent form submission on tab click
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
            });
        });
    </script>
</body>
</html>
