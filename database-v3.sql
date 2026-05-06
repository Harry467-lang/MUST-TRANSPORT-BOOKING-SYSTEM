-- ============================================
-- University Transport Booking System
-- UPDATED SCHEMA — with Drivers + Payments
-- ============================================

CREATE DATABASE IF NOT EXISTS uni_transport;
USE uni_transport;

-- ============================================
-- USERS TABLE
-- CHANGED: role ENUM now includes 'driver'
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'driver') DEFAULT 'user',   /* <-- ADDED 'driver' */
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- CARS TABLE
-- CHANGED: added driver_id column (FK → users.id)
-- ============================================
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_name VARCHAR(100) NOT NULL,
    plate_number VARCHAR(50) UNIQUE NOT NULL,
    car_type VARCHAR(50),
    capacity INT NOT NULL,
    status ENUM('available', 'maintenance', 'retired') DEFAULT 'available',
    driver_id INT DEFAULT NULL,                             /* <-- NEW COLUMN */
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id) REFERENCES users(id)            /* <-- NEW FK */
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- BOOKINGS TABLE (unchanged)
-- ============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    destination VARCHAR(255),
    purpose TEXT,
    passengers INT DEFAULT 1,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- PAYMENTS TABLE (NEW)
-- Simple: links to booking, tracks paid/pending
-- ============================================
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    method VARCHAR(50) DEFAULT NULL,                        /* e.g. Airtel Money, TNM Mpamba, Bank Card */
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

-- Indexes
CREATE INDEX idx_car_date ON bookings(car_id, booking_date);
CREATE INDEX idx_user ON bookings(user_id);
CREATE INDEX idx_payment_booking ON payments(booking_id);
