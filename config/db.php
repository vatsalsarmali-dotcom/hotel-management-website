<?php
$host = 'localhost';
$dbName = 'hotel_db';
$dbUser = 'root';
$dbPass = '';

$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error,
    ]);
    exit;
}

$conn->set_charset('utf8mb4');
