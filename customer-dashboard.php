<?php
session_start();

if (!isset($_SESSION['hotel_user_logged_in']) || $_SESSION['hotel_user_logged_in'] !== true) {
    header('Location: auth/login-register.php');
    exit;
}

require_once __DIR__ . '/config/db.php';

// Get customer bookings
$userId = $_SESSION['hotel_user_id'];
$bookingsSQL = "SELECT booking_id, guest_name, phone, room_type, guest_count, check_in, check_out, 
                total_price, status, payment_status, created_at
                FROM bookings 
                WHERE guest_name = ? OR phone = ?
                ORDER BY booking_id DESC LIMIT 10";

$bookingsStmt = $conn->prepare($bookingsSQL);
$userName = $_SESSION['hotel_user_name'];
$userEmail = $_SESSION['hotel_user_email'];
$bookingsStmt->bind_param('ss', $userName, $userEmail);
$bookingsStmt->execute();
$bookingsResult = $bookingsStmt->get_result();
$bookings = $bookingsResult->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Bookings | GrandStay Hotel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #f7f5f2;
            color: #1c1c1c;
        }

        .header {
            background: linear-gradient(135deg, #173b2d, #0f2922);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.4rem;
        }

        .user-menu {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .user-menu a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .user-menu a:hover {
            opacity: 0.8;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .welcome-section {
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .welcome-section h2 {
            color: #173b2d;
            margin-bottom: 10px;
        }

        .welcome-section p {
            color: #7d7d7d;
            margin-bottom: 16px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #173b2d;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #0f2922;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #d8a75a;
        }

        .btn-secondary:hover {
            background: #c4964f;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .card h3 {
            color: #173b2d;
            margin-bottom: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 14px;
            text-align: left;
            border-bottom: 1px solid #e0d5c5;
        }

        th {
            background: #f4ebdf;
            color: #173b2d;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-confirmed {
            background: rgba(29, 77, 60, 0.12);
            color: #173b2d;
        }

        .status-pending {
            background: rgba(216, 167, 90, 0.18);
            color: #7d5d24;
        }

        .status-paid {
            background: rgba(76, 175, 80, 0.12);
            color: #2e7d32;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7d7d7d;
        }

        .empty-state h3 {
            color: #173b2d;
            margin-bottom: 10px;
        }

        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }

        .quick-link {
            background: linear-gradient(135deg, rgba(23, 59, 45, 0.1), rgba(216, 167, 90, 0.1));
            padding: 16px;
            border-radius: 10px;
            text-decoration: none;
            color: #173b2d;
            font-weight: 600;
            transition: all 0.2s;
            text-align: center;
        }

        .quick-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(17, 55, 45, 0.1);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 16px;
            }

            table {
                font-size: 0.85rem;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>GrandStay Hotel</h1>
        <div class="user-menu">
            <span>Welcome, <?= htmlspecialchars($_SESSION['hotel_user_name']) ?></span>
            <a href="auth/logout.php">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="welcome-section">
            <h2>Hello, <?= htmlspecialchars($_SESSION['hotel_user_name']) ?>!</h2>
            <p>Manage your hotel bookings and reservations from your dashboard.</p>
            <a href="index.html" class="btn">Book a Room</a>
            <a href="auth/login-register.php" class="btn btn-secondary">Back to Home</a>
        </div>

        <?php if (!empty($bookings)): ?>
        <div class="card">
            <h3>Your Bookings</h3>
            <table>
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Room Type</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Guests</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($booking['booking_id']) ?></td>
                        <td><?= htmlspecialchars($booking['room_type']) ?></td>
                        <td><?= date('d M Y', strtotime($booking['check_in'])) ?></td>
                        <td><?= date('d M Y', strtotime($booking['check_out'])) ?></td>
                        <td><?= $booking['guest_count'] ?></td>
                        <td>₹<?= number_format($booking['total_price'], 0) ?></td>
                        <td>
                            <span class="status-badge status-<?= strtolower($booking['status']) ?>">
                                <?= htmlspecialchars($booking['status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $booking['payment_status'] === 'Paid' ? 'paid' : 'pending' ?>">
                                <?= htmlspecialchars($booking['payment_status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="card">
            <div class="empty-state">
                <h3>No Bookings Yet</h3>
                <p>You haven't made any bookings yet. Start by booking your first room!</p>
                <a href="index.html" class="btn" style="margin-top: 16px;">Book Now</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3>Quick Actions</h3>
            <div class="quick-links">
                <a href="index.html" class="quick-link">🛏️ Book a Room</a>
                <a href="#invoices" class="quick-link">📄 My Invoices</a>
                <a href="#profile" class="quick-link">👤 My Profile</a>
                <a href="auth/logout.php" class="quick-link">🚪 Logout</a>
            </div>
        </div>
    </div>
</body>
</html>
