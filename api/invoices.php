<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT invoice_id, invoice_number, booking_id, total_amount, paid_amount, balance, 
             invoice_date, due_date, status FROM invoices ORDER BY invoice_id DESC LIMIT 100";

    $result = $conn->query($sql);
    $invoices = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $invoices[] = [
                'invoice_id' => (int)$row['invoice_id'],
                'invoice_number' => $row['invoice_number'],
                'booking_id' => (int)$row['booking_id'],
                'total_amount' => (float)$row['total_amount'],
                'paid_amount' => (float)$row['paid_amount'],
                'balance' => (float)$row['balance'],
                'invoice_date' => $row['invoice_date'],
                'due_date' => $row['due_date'],
                'status' => $row['status'],
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => $invoices]);
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
    $customerId = (int)($data['customer_id'] ?? 0);
    $totalAmount = (float)($data['total_amount'] ?? 0);
    $paidAmount = (float)($data['paid_amount'] ?? 0);

    if ($bookingId === 0 || $totalAmount === 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Booking ID and total amount are required.']);
        exit;
    }

    $invoiceNumber = 'INV-' . date('Ymd') . '-' . uniqid();
    $balance = $totalAmount - $paidAmount;
    $status = $paidAmount >= $totalAmount ? 'Paid' : 'Issued';
    $dueDate = date('Y-m-d', strtotime('+15 days'));

    $stmt = $conn->prepare(
        'INSERT INTO invoices (booking_id, customer_id, invoice_number, total_amount, paid_amount, balance, due_date, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->bind_param('iisddds', $bookingId, $customerId, $invoiceNumber, $totalAmount, $paidAmount, $balance, $dueDate, $status);

    if (!$stmt->execute()) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create invoice: ' . $stmt->error]);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Invoice created successfully.', 'invoice_number' => $invoiceNumber]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
