-- Hotel Management Database Schema
CREATE DATABASE IF NOT EXISTS hotel_db;
USE hotel_db;

-- Users Table (for admin and staff)
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
);

-- Rooms Table
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
);

-- Customers Table
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
);

-- Bookings Table
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
);

-- Invoices Table
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
);

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Booking Services (Junction Table)
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
);

-- Payments Table
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
);

-- Staff Table
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
);

-- Room Maintenance Log
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
);

-- Insert default admin user
INSERT INTO users (username, password, email, role, first_name, last_name, phone) 
VALUES ('admin', 'admin123', 'admin@grandstay.com', 'admin', 'Admin', 'User', '9876543210');

-- Insert sample rooms
INSERT INTO rooms (room_number, room_type, capacity, price_per_night, description, amenities, status) VALUES
('101', 'Deluxe Room', 2, 3200, 'Comfortable room with king bed, balcony, and modern amenities', 'TV, AC, WiFi, Mini Bar, Coffee Maker', 'Available'),
('102', 'Deluxe Room', 2, 3200, 'Comfortable room with king bed, balcony, and modern amenities', 'TV, AC, WiFi, Mini Bar, Coffee Maker', 'Available'),
('201', 'Executive Suite', 3, 5400, 'Spacious suite with lounge area, work desk, and premium decor', 'TV, AC, WiFi, Mini Bar, Work Desk, Jacuzzi', 'Available'),
('202', 'Executive Suite', 3, 5400, 'Spacious suite with lounge area, work desk, and premium decor', 'TV, AC, WiFi, Mini Bar, Work Desk, Jacuzzi', 'Available'),
('301', 'Family Villa', 4, 7800, 'Ideal for families with living room, kitchenette, and private patio', 'TV, AC, WiFi, Kitchen, Private Dining, 2 Beds', 'Available'),
('302', 'Family Villa', 4, 7800, 'Ideal for families with living room, kitchenette, and private patio', 'TV, AC, WiFi, Kitchen, Private Dining, 2 Beds', 'Available'),
('401', 'Standard Room', 2, 2500, 'Basic room with essential amenities and comfortable bed', 'TV, AC, WiFi, Bathroom', 'Available'),
('402', 'Standard Room', 2, 2500, 'Basic room with essential amenities and comfortable bed', 'TV, AC, WiFi, Bathroom', 'Available');

-- Insert sample services
INSERT INTO services (service_name, description, price, status) VALUES
('Room Service', 'In-room dining and beverages', 500, 'Active'),
('Spa Treatment', 'Relaxing spa and massage services', 2000, 'Active'),
('Laundry Service', 'Room laundry and ironing', 200, 'Active'),
('Airport Transfer', 'Roundtrip airport transportation', 1500, 'Active'),
('Breakfast', 'Complimentary or paid breakfast', 300, 'Active'),
('Late Checkout', 'Extended checkout time', 1000, 'Active'),
('WiFi Premium', 'High-speed internet access', 100, 'Active'),
('Parking', 'Vehicle parking service', 500, 'Active');

-- Create index for better query performance
CREATE INDEX idx_booking_date ON bookings(check_in, check_out);
CREATE INDEX idx_room_status ON rooms(status);
CREATE INDEX idx_customer_phone ON customers(phone);
