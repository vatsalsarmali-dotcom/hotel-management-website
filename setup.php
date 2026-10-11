<?php
/**
 * Hotel Management System - Setup Script
 * This script sets up the database with all required tables and sample data
 */

$host = 'localhost';
$dbName = 'database'; // Your existing database name
$dbUser = 'root';
$dbPass = '';

// Create connection
$conn = new mysqli($host, $dbUser, $dbPass, $dbName);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Hotel Management Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #173b2d; }
        .success { color: #4caf50; padding: 10px; background: #f1f8f4; border-left: 4px solid #4caf50; margin: 10px 0; }
        .error { color: #f44336; padding: 10px; background: #ffebee; border-left: 4px solid #f44336; margin: 10px 0; }
        .info { color: #2196f3; padding: 10px; background: #e3f2fd; border-left: 4px solid #2196f3; margin: 10px 0; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        .step { margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #173b2d; color: white; }
    </style>
</head>
<body>
<div class='container'>
<h1>🏨 GrandStay Hotel Management - Setup</h1>";

// SQL statements to create tables
$tables = array(
    'users' => "
        CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(150),
            role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
            first_name VARCHAR(100),
            last_name VARCHAR(100),
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ",
    'rooms' => "
        CREATE TABLE IF NOT EXISTS rooms (
            room_id INT AUTO_INCREMENT PRIMARY KEY,
            room_number VARCHAR(20) NOT NULL UNIQUE,
            room_type ENUM('Deluxe Room', 'Executive Suite', 'Family Villa', 'Standard Room', 'Luxury Penthouse') DEFAULT 'Standard Room',
            capacity INT NOT NULL DEFAULT 2,
            price_per_night DECIMAL(10, 2) NOT NULL,
            description TEXT,
            amenities VARCHAR(500),
            status ENUM('Available', 'Occupied', 'Maintenance', 'Reserved') DEFAULT 'Available',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ",
    'customers' => "
        CREATE TABLE IF NOT EXISTS customers (
            customer_id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(150),
            phone VARCHAR(20) NOT NULL,
            address VARCHAR(255),
            city VARCHAR(100),
            country VARCHAR(100),
            passport_number VARCHAR(50),
            id_proof VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ",
    'bookings' => "
        CREATE TABLE IF NOT EXISTS bookings (
            booking_id INT AUTO_INCREMENT PRIMARY KEY,
            customer_id INT,
            room_id INT,
            guest_name VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            email VARCHAR(150),
            room_type VARCHAR(100),
            guest_count INT DEFAULT 1,
            check_in DATE NOT NULL,
            check_out DATE NOT NULL,
            number_of_nights INT GENERATED ALWAYS AS (DATEDIFF(check_out, check_in)) STORED,
            total_price DECIMAL(10, 2),
            status ENUM('Pending', 'Confirmed', 'Checked-in', 'Checked-out', 'Cancelled') DEFAULT 'Pending',
            payment_status ENUM('Unpaid', 'Paid', 'Partial') DEFAULT 'Unpaid',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
            FOREIGN KEY (room_id) REFERENCES rooms(room_id)
        )
    ",
    'invoices' => "
        CREATE TABLE IF NOT EXISTS invoices (
            invoice_id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            customer_id INT NOT NULL,
            invoice_number VARCHAR(50) NOT NULL UNIQUE,
            room_charges DECIMAL(10, 2),
            tax DECIMAL(10, 2) DEFAULT 0,
            service_charges DECIMAL(10, 2) DEFAULT 0,
            total_amount DECIMAL(10, 2) NOT NULL,
            paid_amount DECIMAL(10, 2) DEFAULT 0,
            balance DECIMAL(10, 2),
            invoice_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            due_date DATE,
            payment_date DATE,
            status ENUM('Draft', 'Issued', 'Paid', 'Overdue', 'Cancelled') DEFAULT 'Draft',
            FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
            FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        )
    ",
    'services' => "
        CREATE TABLE IF NOT EXISTS services (
            service_id INT AUTO_INCREMENT PRIMARY KEY,
            service_name VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            status ENUM('Active', 'Inactive') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ",
    'booking_services' => "
        CREATE TABLE IF NOT EXISTS booking_services (
            booking_service_id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            service_id INT NOT NULL,
            quantity INT DEFAULT 1,
            price DECIMAL(10, 2),
            total_price DECIMAL(10, 2),
            added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
            FOREIGN KEY (service_id) REFERENCES services(service_id)
        )
    ",
    'payments' => "
        CREATE TABLE IF NOT EXISTS payments (
            payment_id INT AUTO_INCREMENT PRIMARY KEY,
            booking_id INT NOT NULL,
            invoice_id INT,
            amount DECIMAL(10, 2) NOT NULL,
            payment_method ENUM('Cash', 'Credit Card', 'Debit Card', 'Online Transfer', 'Cheque') DEFAULT 'Cash',
            transaction_id VARCHAR(100),
            payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('Success', 'Failed', 'Pending') DEFAULT 'Pending',
            notes TEXT,
            FOREIGN KEY (booking_id) REFERENCES bookings(booking_id),
            FOREIGN KEY (invoice_id) REFERENCES invoices(invoice_id)
        )
    ",
    'staff' => "
        CREATE TABLE IF NOT EXISTS staff (
            staff_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            designation VARCHAR(100),
            department VARCHAR(100),
            phone VARCHAR(20),
            email VARCHAR(150),
            salary DECIMAL(10, 2),
            joining_date DATE,
            status ENUM('Active', 'Inactive', 'On Leave') DEFAULT 'Active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id)
        )
    ",
    'maintenance_logs' => "
        CREATE TABLE IF NOT EXISTS maintenance_logs (
            maintenance_id INT AUTO_INCREMENT PRIMARY KEY,
            room_id INT NOT NULL,
            issue_description TEXT,
            assigned_to INT,
            status ENUM('Open', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Open',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            completed_at TIMESTAMP NULL,
            cost DECIMAL(10, 2),
            FOREIGN KEY (room_id) REFERENCES rooms(room_id),
            FOREIGN KEY (assigned_to) REFERENCES staff(staff_id)
        )
    "
);

// Create tables
echo "<div class='step'><h2>Step 1: Creating Database Tables</h2>";
$tablesCreated = 0;
foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<div class='success'>✓ Table <code>$tableName</code> created/verified</div>";
        $tablesCreated++;
    } else {
        echo "<div class='error'>✗ Error creating table <code>$tableName</code>: " . $conn->error . "</div>";
    }
}
echo "</div>";

// Insert admin user
echo "<div class='step'><h2>Step 2: Adding Admin User</h2>";
$checkAdmin = $conn->query("SELECT * FROM users WHERE username = 'admin'");
if ($checkAdmin->num_rows == 0) {
    $insertAdmin = "INSERT INTO users (username, password, email, role, first_name, last_name, phone) 
                    VALUES ('admin', 'admin123', 'admin@grandstay.com', 'admin', 'Admin', 'User', '9876543210')";
    if ($conn->query($insertAdmin) === TRUE) {
        echo "<div class='success'>✓ Admin user created</div>";
    } else {
        echo "<div class='error'>✗ Error creating admin user: " . $conn->error . "</div>";
    }
} else {
    echo "<div class='info'>ℹ Admin user already exists</div>";
}
echo "</div>";

// Insert sample rooms
echo "<div class='step'><h2>Step 3: Adding Sample Rooms</h2>";
$checkRooms = $conn->query("SELECT COUNT(*) as count FROM rooms");
$roomCount = $checkRooms->fetch_assoc()['count'];
if ($roomCount == 0) {
    $rooms = array(
        array('101', 'Deluxe Room', 2, 3200, 'Comfortable room with king bed, balcony, and modern amenities', 'TV, AC, WiFi, Mini Bar, Coffee Maker'),
        array('102', 'Deluxe Room', 2, 3200, 'Comfortable room with king bed, balcony, and modern amenities', 'TV, AC, WiFi, Mini Bar, Coffee Maker'),
        array('201', 'Executive Suite', 3, 5400, 'Spacious suite with lounge area, work desk, and premium decor', 'TV, AC, WiFi, Mini Bar, Work Desk, Jacuzzi'),
        array('202', 'Executive Suite', 3, 5400, 'Spacious suite with lounge area, work desk, and premium decor', 'TV, AC, WiFi, Mini Bar, Work Desk, Jacuzzi'),
        array('301', 'Family Villa', 4, 7800, 'Ideal for families with living room, kitchenette, and private patio', 'TV, AC, WiFi, Kitchen, Private Dining, 2 Beds'),
        array('302', 'Family Villa', 4, 7800, 'Ideal for families with living room, kitchenette, and private patio', 'TV, AC, WiFi, Kitchen, Private Dining, 2 Beds'),
        array('401', 'Standard Room', 2, 2500, 'Basic room with essential amenities and comfortable bed', 'TV, AC, WiFi, Bathroom'),
        array('402', 'Standard Room', 2, 2500, 'Basic room with essential amenities and comfortable bed', 'TV, AC, WiFi, Bathroom')
    );
    
    foreach ($rooms as $room) {
        $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type, capacity, price_per_night, description, amenities) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssiiiss', $room[0], $room[1], $room[2], $room[3], $room[4], $room[5]);
        if ($stmt->execute()) {
            echo "<div class='success'>✓ Room {$room[0]} ({$room[1]}) added</div>";
        } else {
            echo "<div class='error'>✗ Error adding room {$room[0]}: " . $stmt->error . "</div>";
        }
    }
} else {
    echo "<div class='info'>ℹ Rooms already exist (" . $roomCount . " rooms found)</div>";
}
echo "</div>";

// Insert sample services
echo "<div class='step'><h2>Step 4: Adding Hotel Services</h2>";
$checkServices = $conn->query("SELECT COUNT(*) as count FROM services");
$serviceCount = $checkServices->fetch_assoc()['count'];
if ($serviceCount == 0) {
    $services = array(
        array('Room Service', 'In-room dining and beverages', 500),
        array('Spa Treatment', 'Relaxing spa and massage services', 2000),
        array('Laundry Service', 'Room laundry and ironing', 200),
        array('Airport Transfer', 'Roundtrip airport transportation', 1500),
        array('Breakfast', 'Complimentary or paid breakfast', 300),
        array('Late Checkout', 'Extended checkout time', 1000),
        array('WiFi Premium', 'High-speed internet access', 100),
        array('Parking', 'Vehicle parking service', 500)
    );
    
    foreach ($services as $service) {
        $stmt = $conn->prepare("INSERT INTO services (service_name, description, price) VALUES (?, ?, ?)");
        $stmt->bind_param('ssd', $service[0], $service[1], $service[2]);
        if ($stmt->execute()) {
            echo "<div class='success'>✓ Service '{$service[0]}' added</div>";
        } else {
            echo "<div class='error'>✗ Error adding service: " . $stmt->error . "</div>";
        }
    }
} else {
    echo "<div class='info'>ℹ Services already exist (" . $serviceCount . " services found)</div>";
}
echo "</div>";

// Create indexes
echo "<div class='step'><h2>Step 5: Creating Database Indexes</h2>";
$indexes = array(
    "ALTER TABLE bookings ADD INDEX idx_booking_date (check_in, check_out)",
    "ALTER TABLE rooms ADD INDEX idx_room_status (status)",
    "ALTER TABLE customers ADD INDEX idx_customer_phone (phone)"
);
foreach ($indexes as $index) {
    $conn->query($index);
}
echo "<div class='success'>✓ Indexes created</div>";
echo "</div>";

// Show database summary
echo "<div class='step'><h2>Database Summary</h2>";
echo "<table>
    <tr>
        <th>Table</th>
        <th>Records</th>
    </tr>";

$tablesList = array('users', 'customers', 'rooms', 'bookings', 'services', 'invoices', 'payments', 'staff', 'maintenance_logs');
foreach ($tablesList as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $count = $result->fetch_assoc()['count'];
    echo "<tr><td>$table</td><td>$count</td></tr>";
}
echo "</table>";
echo "</div>";

// Show next steps
echo "<div class='step' style='background: #e3f2fd; padding: 15px; border-radius: 5px;'>
    <h2>✓ Setup Complete!</h2>
    <p>Your hotel management database is ready to use.</p>
    <h3>Next Steps:</h3>
    <ol>
        <li>Open your browser and go to: <code>http://localhost:8000</code> (or your PHP server)</li>
        <li>Make a booking from the homepage</li>
        <li>Admin login: <code>http://localhost:8000/admin/login.php</code></li>
        <li>Use credentials:
            <ul>
                <li>Username: <code>admin</code></li>
                <li>Password: <code>admin123</code></li>
            </ul>
        </li>
        <li>View bookings, manage rooms, and track payments from the dashboard</li>
    </ol>
    <p><strong>Delete this file (setup.php) after setup is complete.</strong></p>
</div>";

echo "</div>
</body>
</html>";

$conn->close();
?>
