<?php
session_start();

if (!isset($_SESSION['hotel_admin_logged_in']) || $_SESSION['hotel_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';

$statsSql = "
    SELECT
        COUNT(*) AS total_bookings,
        SUM(CASE WHEN status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed_bookings,
        COUNT(DISTINCT room_type) AS room_types
    FROM bookings
";

$stats = $conn->query($statsSql)->fetch_assoc();

$bookingsSql = "SELECT booking_id, guest_name, phone, room_type, guest_count, check_in, check_out, status
                FROM bookings
                ORDER BY booking_id DESC";

$bookingsResult = $conn->query($bookingsSql);
$bookings = $bookingsResult ? $bookingsResult->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | GrandStay</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f7f5f2;
            color: #1c1c1c;
        }
        .topbar {
            background: #173b2d;
            color: white;
            display: flex;
            justify-content: space-between;
            padding: 18px 30px;
            align-items: center;
        }
        .topbar h1 {
            margin: 0;
            font-size: 1.5rem;
        }
        .topbar a {
            color: white;
            text-decoration: none;
            font-weight: 700;
        }
        .container {
            max-width: 1200px;
            margin: 32px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 24px 20px;
            box-shadow: 0 10px 20px rgba(31, 64, 54, 0.05);
        }
        .card h3 {
            margin: 0 0 10px;
            color: #173b2d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .card .value {
            font-size: 2rem;
            font-weight: 700;
            color: #173b2d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.05);
        }
        th, td {
            border-bottom: 1px solid #eae1d3;
            padding: 16px 18px;
            text-align: left;
        }
        th {
            background: #f4ebdf;
            color: #173b2d;
        }
        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(29, 77, 60, 0.12);
            color: #173b2d;
            font-weight: 700;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>GrandStay Admin Dashboard</h1>
        <a href="logout.php">Logout</a>
    </div>

    <div class="container">
        <div class="stats">
            <div class="card">
                <h3>Total Bookings</h3>
                <div class="value"><?= (int)($stats['total_bookings'] ?? 0) ?></div>
            </div>
            <div class="card">
                <h3>Confirmed</h3>
                <div class="value"><?= (int)($stats['confirmed_bookings'] ?? 0) ?></div>
            </div>
            <div class="card">
                <h3>Room Types</h3>
                <div class="value"><?= (int)($stats['room_types'] ?? 0) ?></div>
            </div>
        </div>

        <div class="card">
            <h2>Recent Reservations</h2>
            <table>
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Phone</th>
                        <th>Room</th>
                        <th>Guests</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7">No bookings found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?= htmlspecialchars($booking['guest_name']) ?></td>
                                <td><?= htmlspecialchars($booking['phone']) ?></td>
                                <td><?= htmlspecialchars($booking['room_type']) ?></td>
                                <td><?= (int)$booking['guest_count'] ?></td>
                                <td><?= htmlspecialchars($booking['check_in']) ?></td>
                                <td><?= htmlspecialchars($booking['check_out']) ?></td>
                                <td><span class="status"><?= htmlspecialchars($booking['status']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
