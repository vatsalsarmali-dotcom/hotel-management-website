<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT room_id, room_number, room_type, capacity, price_per_night, description, amenities, status 
            FROM rooms 
            ORDER BY room_number ASC";

    $result = $conn->query($sql);
    $rooms = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rooms[] = [
                'room_id' => (int)$row['room_id'],
                'room_number' => $row['room_number'],
                'room_type' => $row['room_type'],
                'capacity' => (int)$row['capacity'],
                'price_per_night' => (float)$row['price_per_night'],
                'description' => $row['description'],
                'amenities' => $row['amenities'],
                'status' => $row['status'],
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $rooms]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['hotel_admin_logged_in'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    $roomNumber = trim((string)($data['room_number'] ?? ''));
    $roomType = trim((string)($data['room_type'] ?? ''));
    $capacity = (int)($data['capacity'] ?? 2);
    $price = (float)($data['price_per_night'] ?? 3200);
    $description = trim((string)($data['description'] ?? ''));
    $amenities = trim((string)($data['amenities'] ?? ''));
    $status = trim((string)($data['status'] ?? 'Available'));

    if (empty($roomNumber) || empty($roomType)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Room number and type are required.']);
        exit;
    }

    $stmt = $conn->prepare(
        'INSERT INTO rooms (room_number, room_type, capacity, price_per_night, description, amenities, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param('ssidsss', $roomNumber, $roomType, $capacity, $price, $description, $amenities, $status);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to add room: ' . $stmt->error]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Room added successfully.']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
