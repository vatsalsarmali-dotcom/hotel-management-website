<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT payment_id, booking_id, amount, payment_method, transaction_id, payment_date, status 
            FROM payments ORDER BY payment_id DESC LIMIT 100";

    $result = $conn->query($sql);
    $payments = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = [
                'payment_id' => (int)$row['payment_id'],
                'booking_id' => (int)$row['booking_id'],
                'amount' => (float)$row['amount'],
                'payment_method' => $row['payment_method'],
                'transaction_id' => $row['transaction_id'],
                'payment_date' => $row['payment_date'],
                'status' => $row['status'],
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $payments]);
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

    $bookingId = (int)($data['booking_id'] ?? 0);
    $amount = (float)($data['amount'] ?? 0);
    $paymentMethod = trim((string)($data['payment_method'] ?? 'Cash'));
    $transactionId = trim((string)($data['transaction_id'] ?? ''));
    $status = 'Success';

    if ($bookingId === 0 || $amount === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Booking ID and amount are required.']);
        exit;
    }

    $stmt = $conn->prepare(
        'INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) 
         VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->bind_param('idsss', $bookingId, $amount, $paymentMethod, $transactionId, $status);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to record payment: ' . $stmt->error]);
        exit;
    }

    // Update booking payment status
    $updateSql = "UPDATE bookings SET payment_status = 'Paid' WHERE booking_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param('i', $bookingId);
    $updateStmt->execute();

    echo json_encode(['success' => true, 'message' => 'Payment recorded successfully.']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
