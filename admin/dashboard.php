<?php
session_start();

if (!isset($_SESSION['hotel_admin_logged_in']) || $_SESSION['hotel_admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | GrandStay</title>
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

        .topbar {
            background: linear-gradient(135deg, #173b2d, #0f2922);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .topbar h1 {
            font-size: 1.5rem;
        }

        .topbar-links {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .topbar a {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: opacity 0.2s;
        }

        .topbar a:hover {
            opacity: 0.8;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: 70px;
            width: 250px;
            height: calc(100vh - 70px);
            background: #fff;
            border-right: 1px solid #e0d5c5;
            overflow-y: auto;
        }

        .sidebar a {
            display: block;
            padding: 14px 20px;
            color: #173b2d;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.2s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: #f0e8df;
            border-left-color: #d8a75a;
        }

        .container {
            margin-left: 250px;
            padding: 30px;
            min-height: calc(100vh - 70px);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .stat-card h3 {
            color: #7d7d7d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #173b2d;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
        }

        .card h2 {
            margin: 0 0 20px;
            color: #173b2d;
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

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #173b2d;
            color: white;
        }

        .btn-primary:hover {
            background: #0f2922;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.85rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .container {
                margin-left: 200px;
            }
            .stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                display: none;
            }
            .container {
                margin-left: 0;
            }
            .stats {
                grid-template-columns: 1fr;
            }
        }

        .hide {
            display: none;
        }
    </style>
</head>
<body>
    <div class="topbar">
        <h1>GrandStay Hotel Management</h1>
        <div class="topbar-links">
            <span>Welcome, <?= htmlspecialchars($_SESSION['hotel_admin_name']) ?></span>
            <a href="logout.php">Logout</a>
        </div>
    </div>

    <div class="sidebar">
        <a href="#dashboard" class="sidebar-link active" onclick="showSection('dashboard')">Dashboard</a>
        <a href="#bookings" class="sidebar-link" onclick="showSection('bookings')">Bookings</a>
        <a href="#rooms" class="sidebar-link" onclick="showSection('rooms')">Rooms</a>
        <a href="#invoices" class="sidebar-link" onclick="showSection('invoices')">Invoices</a>
        <a href="#payments" class="sidebar-link" onclick="showSection('payments')">Payments</a>
    </div>

    <div class="container">
        <!-- Dashboard Section -->
        <section id="dashboard" class="section">
            <h2>Dashboard Overview</h2>
            <div class="stats" id="stats-container">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <div class="value" id="stat-bookings">0</div>
                </div>
                <div class="stat-card">
                    <h3>Confirmed</h3>
                    <div class="value" id="stat-confirmed">0</div>
                </div>
                <div class="stat-card">
                    <h3>Available Rooms</h3>
                    <div class="value" id="stat-available">0</div>
                </div>
                <div class="stat-card">
                    <h3>Total Guests</h3>
                    <div class="value" id="stat-guests">0</div>
                </div>
                <div class="stat-card">
                    <h3>Revenue</h3>
                    <div class="value" id="stat-revenue">₹0</div>
                </div>
                <div class="stat-card">
                    <h3>Paid Amount</h3>
                    <div class="value" id="stat-paid">₹0</div>
                </div>
            </div>

            <div class="card">
                <h2>Recent Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Payment</th>
                        </tr>
                    </thead>
                    <tbody id="dashboard-bookings-table">
                        <tr>
                            <td colspan="7">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Bookings Section -->
        <section id="bookings" class="section hide">
            <div class="card">
                <h2>All Bookings</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Guest</th>
                            <th>Phone</th>
                            <th>Room</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-table">
                        <tr>
                            <td colspan="8">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Rooms Section -->
        <section id="rooms" class="section hide">
            <div class="card">
                <h2>Room Management</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Room #</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Price/Night</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="rooms-table">
                        <tr>
                            <td colspan="6">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Invoices Section -->
        <section id="invoices" class="section hide">
            <div class="card">
                <h2>Invoices</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Booking ID</th>
                            <th>Amount</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="invoices-table">
                        <tr>
                            <td colspan="6">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Payments Section -->
        <section id="payments" class="section hide">
            <div class="card">
                <h2>Payment Transactions</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Booking ID</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="payments-table">
                        <tr>
                            <td colspan="6">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        const formatCurrency = (amount) => {
            return '₹' + parseFloat(amount).toLocaleString('en-IN', { maximumFractionDigits: 0 });
        };

        const formatDate = (date) => {
            return new Date(date).toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
        };

        const loadDashboard = async () => {
            try {
                const response = await fetch('api/dashboard.php');
                const result = await response.json();
                if (result.success) {
                    const data = result.data;
                    document.getElementById('stat-bookings').textContent = data.total_bookings;
                    document.getElementById('stat-confirmed').textContent = data.confirmed_bookings;
                    document.getElementById('stat-available').textContent = data.available_rooms;
                    document.getElementById('stat-guests').textContent = data.total_guests;
                    document.getElementById('stat-revenue').textContent = formatCurrency(data.revenue);
                    document.getElementById('stat-paid').textContent = formatCurrency(data.paid_amount);
                }
            } catch (error) {
                console.error('Error loading dashboard:', error);
            }
        };

        const loadBookings = async () => {
            try {
                const response = await fetch('api/bookings.php');
                const result = await response.json();
                if (result.success) {
                    const tbody = document.getElementById('bookings-table');
                    const dashboardTbody = document.getElementById('dashboard-bookings-table');
                    tbody.innerHTML = '';
                    dashboardTbody.innerHTML = '';

                    result.data.forEach((booking, index) => {
                        const statusClass = booking.status === 'Confirmed' ? 'status-confirmed' : 'status-pending';
                        const paymentClass = booking.payment_status === 'Paid' ? 'status-paid' : 'status-pending';
                        const row = `
                            <tr>
                                <td>${booking.booking_id}</td>
                                <td>${booking.guest_name}</td>
                                <td>${booking.phone}</td>
                                <td>${booking.room_type}</td>
                                <td>${formatDate(booking.check_in)}</td>
                                <td>${formatDate(booking.check_out)}</td>
                                <td>${formatCurrency(booking.total_price)}</td>
                                <td><span class="status-badge ${statusClass}">${booking.status}</span></td>
                            </tr>
                        `;
                        tbody.innerHTML += row;

                        if (index < 5) {
                            const dashboardRow = `
                                <tr>
                                    <td>${booking.guest_name}</td>
                                    <td>${booking.room_type}</td>
                                    <td>${formatDate(booking.check_in)}</td>
                                    <td>${formatDate(booking.check_out)}</td>
                                    <td>${formatCurrency(booking.total_price)}</td>
                                    <td><span class="status-badge ${statusClass}">${booking.status}</span></td>
                                    <td><span class="status-badge ${paymentClass}">${booking.payment_status}</span></td>
                                </tr>
                            `;
                            dashboardTbody.innerHTML += dashboardRow;
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading bookings:', error);
            }
        };

        const loadRooms = async () => {
            try {
                const response = await fetch('api/rooms.php');
                const result = await response.json();
                if (result.success) {
                    const tbody = document.getElementById('rooms-table');
                    tbody.innerHTML = '';
                    result.data.forEach((room) => {
                        const statusClass = room.status === 'Available' ? 'status-confirmed' : 'status-pending';
                        const row = `
                            <tr>
                                <td>${room.room_number}</td>
                                <td>${room.room_type}</td>
                                <td>${room.capacity}</td>
                                <td>${formatCurrency(room.price_per_night)}</td>
                                <td><span class="status-badge ${statusClass}">${room.status}</span></td>
                                <td><button class="btn btn-primary btn-small">Edit</button></td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                }
            } catch (error) {
                console.error('Error loading rooms:', error);
            }
        };

        const loadInvoices = async () => {
            try {
                const response = await fetch('api/invoices.php');
                const result = await response.json();
                if (result.success) {
                    const tbody = document.getElementById('invoices-table');
                    tbody.innerHTML = '';
                    result.data.forEach((invoice) => {
                        const statusClass = invoice.status === 'Paid' ? 'status-paid' : 'status-pending';
                        const row = `
                            <tr>
                                <td>${invoice.invoice_number}</td>
                                <td>${invoice.booking_id}</td>
                                <td>${formatCurrency(invoice.total_amount)}</td>
                                <td>${formatCurrency(invoice.paid_amount)}</td>
                                <td>${formatCurrency(invoice.balance)}</td>
                                <td><span class="status-badge ${statusClass}">${invoice.status}</span></td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                }
            } catch (error) {
                console.error('Error loading invoices:', error);
            }
        };

        const loadPayments = async () => {
            try {
                const response = await fetch('api/payments.php');
                const result = await response.json();
                if (result.success) {
                    const tbody = document.getElementById('payments-table');
                    tbody.innerHTML = '';
                    result.data.forEach((payment) => {
                        const statusClass = payment.status === 'Success' ? 'status-paid' : 'status-pending';
                        const row = `
                            <tr>
                                <td>${payment.payment_id}</td>
                                <td>${payment.booking_id}</td>
                                <td>${formatCurrency(payment.amount)}</td>
                                <td>${payment.payment_method}</td>
                                <td>${formatDate(payment.payment_date)}</td>
                                <td><span class="status-badge ${statusClass}">${payment.status}</span></td>
                            </tr>
                        `;
                        tbody.innerHTML += row;
                    });
                }
            } catch (error) {
                console.error('Error loading payments:', error);
            }
        };

        const showSection = (sectionId) => {
            const sections = document.querySelectorAll('.section');
            sections.forEach((section) => {
                section.classList.add('hide');
            });
            document.getElementById(sectionId).classList.remove('hide');

            const links = document.querySelectorAll('.sidebar-link');
            links.forEach((link) => {
                link.classList.remove('active');
            });
            event.target.classList.add('active');

            if (sectionId === 'bookings') loadBookings();
            if (sectionId === 'rooms') loadRooms();
            if (sectionId === 'invoices') loadInvoices();
            if (sectionId === 'payments') loadPayments();
        };

        // Load data on page load
        window.addEventListener('load', () => {
            loadDashboard();
            loadBookings();
        });
    </script>
</body>
</html>
