<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate input
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required.']);
        exit;
    }

    // Query user from database
    $stmt = $conn->prepare('SELECT user_id, username, password, role, first_name, last_name FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }

    // For demo: plain text password comparison (in production, use password_hash and password_verify)
    if ($user['password'] !== $password) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
        exit;
    }

    // Set session variables
    $_SESSION['hotel_admin_logged_in'] = true;
    $_SESSION['hotel_admin_user'] = $user['username'];
    $_SESSION['hotel_admin_id'] = $user['user_id'];
    $_SESSION['hotel_admin_role'] = $user['role'];
    $_SESSION['hotel_admin_name'] = $user['first_name'] . ' ' . $user['last_name'];

    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'username' => $user['username'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'role' => $user['role']
        ]
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
