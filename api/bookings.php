<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT booking_id, guest_name, phone, email, room_type, guest_count, check_in, check_out, 
             total_price, status, payment_status, created_at
            FROM bookings
            ORDER BY booking_id DESC
            LIMIT 100";

    $result = $conn->query($sql);
    $bookings = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = [
                'booking_id' => (int)$row['booking_id'],
                'guest_name' => $row['guest_name'],
                'phone' => $row['phone'],
                'email' => $row['email'],
                'room_type' => $row['room_type'],
                'guest_count' => (int)$row['guest_count'],
                'check_in' => $row['check_in'],
                'check_out' => $row['check_out'],
                'total_price' => (float)$row['total_price'],
                'status' => $row['status'],
                'payment_status' => $row['payment_status'],
                'created_at' => $row['created_at'],
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $bookings]);
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
    $email = trim((string)($data['email'] ?? $_POST['email'] ?? ''));
    $roomType = trim((string)($data['room_type'] ?? $_POST['room_type'] ?? ''));
    $guestCount = (int)($data['guest_count'] ?? $_POST['guest_count'] ?? 1);
    $checkIn = trim((string)($data['check_in'] ?? $_POST['check_in'] ?? ''));
    $checkOut = trim((string)($data['check_out'] ?? $_POST['check_out'] ?? ''));

    if ($guestName === '' || $phone === '' || $roomType === '' || $checkIn === '' || $checkOut === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Please fill all required booking details.']);
        exit;
    }

    if (strtotime($checkOut) <= strtotime($checkIn)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Check-out date must be after check-in date.']);
        exit;
    }

    // Calculate total price based on room type and nights
    $prices = [
        'Deluxe Room' => 3200,
        'Executive Suite' => 5400,
        'Family Villa' => 7800,
        'Standard Room' => 2500,
        'Luxury Penthouse' => 12000
    ];
    $pricePerNight = $prices[$roomType] ?? 3200;
    $nights = (strtotime($checkOut) - strtotime($checkIn)) / 86400;
    $totalPrice = $pricePerNight * $nights;

    $stmt = $conn->prepare(
        'INSERT INTO bookings (guest_name, phone, email, room_type, guest_count, check_in, check_out, total_price, status, payment_status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, "Confirmed", "Unpaid")'
    );

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'SQL prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param('sssiiiss', $guestName, $phone, $email, $roomType, $guestCount, $checkIn, $checkOut, $totalPrice);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Booking could not be saved: ' . $stmt->error]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Booking confirmed successfully.']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
