<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT booking_id, guest_name, phone, room_type, guest_count, check_in, check_out, status
            FROM bookings
            ORDER BY booking_id DESC";

    $result = $conn->query($sql);
    $bookings = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = [
                'booking_id' => (int)$row['booking_id'],
                'guest_name' => $row['guest_name'],
                'phone' => $row['phone'],
                'room_type' => $row['room_type'],
                'guest_count' => (int)$row['guest_count'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out'],
                'status' => $row['status'],
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $bookings,
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (!is_array($data)) {
        $data = $_POST;
    }

    $guestName = trim((string)($data['guest_name'] ?? $_POST['guest_name'] ?? ''));
    $phone = trim((string)($data['phone'] ?? $_POST['phone'] ?? ''));
    $roomType = trim((string)($data['room_type'] ?? $_POST['room_type'] ?? ''));
    $guestCount = (int)($data['guest_count'] ?? $_POST['guest_count'] ?? 1);
    $checkIn = trim((string)($data['check_in'] ?? $_POST['check_in'] ?? ''));
    $checkOut = trim((string)($data['check_out'] ?? $_POST['check_out'] ?? ''));

    if ($guestName === '' || $phone === '' || $roomType === '' || $checkIn === '' || $checkOut === '') {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required booking details.',
        ]);
        exit;
    }

    if (strtotime($checkOut) <= strtotime($checkIn)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Check-out date must be after check-in date.',
        ]);
        exit;
    }

    $stmt = $conn->prepare(
        'INSERT INTO bookings (guest_name, phone, room_type, guest_count, check_in, check_out, status) VALUES (?, ?, ?, ?, ?, ?, "Confirmed")'
    );

    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'SQL prepare failed: ' . $conn->error,
        ]);
        exit;
    }

    $stmt->bind_param('sssiis', $guestName, $phone, $roomType, $guestCount, $checkIn, $checkOut);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Booking could not be saved: ' . $stmt->error,
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Booking confirmed successfully.',
    ]);
    exit;
}

http_response_code(405);
echo json_encode([
    'success' => false,
    'message' => 'Method not allowed.',
]);
