<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get dashboard statistics
    $stats = [];

    // Total bookings
    $result = $conn->query('SELECT COUNT(*) as count FROM bookings');
    $stats['total_bookings'] = (int)($result->fetch_assoc()['count'] ?? 0);

    // Confirmed bookings
    $result = $conn->query('SELECT COUNT(*) as count FROM bookings WHERE status = "Confirmed"');
    $stats['confirmed_bookings'] = (int)($result->fetch_assoc()['count'] ?? 0);

    // Available rooms
    $result = $conn->query('SELECT COUNT(*) as count FROM rooms WHERE status = "Available"');
    $stats['available_rooms'] = (int)($result->fetch_assoc()['count'] ?? 0);

    // Total guests
    $result = $conn->query('SELECT SUM(guest_count) as count FROM bookings WHERE status != "Cancelled"');
    $stats['total_guests'] = (int)($result->fetch_assoc()['count'] ?? 0);

    // Revenue
    $result = $conn->query('SELECT SUM(total_price) as total FROM bookings WHERE status = "Confirmed" OR status = "Checked-in" OR status = "Checked-out"');
    $stats['revenue'] = (float)($result->fetch_assoc()['total'] ?? 0);

    // Paid amount
    $result = $conn->query('SELECT SUM(amount) as total FROM payments WHERE status = "Success"');
    $stats['paid_amount'] = (float)($result->fetch_assoc()['total'] ?? 0);

    echo json_encode(['success' => true, 'data' => $stats]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
