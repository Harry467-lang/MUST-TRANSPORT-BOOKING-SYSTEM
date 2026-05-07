-- ============================================
-- UniTransport — ALTER Statements
-- Run these ONCE on your existing database
-- ============================================

USE uni_transport;

-- 1. Add 'driver' to users role ENUM (if not already done)
ALTER TABLE users
    MODIFY COLUMN role ENUM('admin', 'user', 'driver') DEFAULT 'user';

-- 2. Add driver_id to cars (if not already done)
ALTER TABLE cars
    ADD COLUMN IF NOT EXISTS driver_id INT DEFAULT NULL,
    ADD FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE;

-- 3. NEW: Add price_per_trip to cars
ALTER TABLE cars
    ADD COLUMN IF NOT EXISTS price_per_trip DECIMAL(10,2) DEFAULT 0.00;

-- 4. Create payments table (if not exists)
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    method VARCHAR(50) DEFAULT NULL,
    status ENUM('pending', 'paid') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Set sample prices on existing cars
UPDATE cars SET price_per_trip = 15000.00 WHERE car_type = 'SUV';
UPDATE cars SET price_per_trip = 25000.00 WHERE car_type = 'Van';
UPDATE cars SET price_per_trip = 35000.00 WHERE car_type = 'Minibus';
UPDATE cars SET price_per_trip = 50000.00 WHERE car_type = 'Bus';
UPDATE cars SET price_per_trip = 10000.00 WHERE car_type = 'Sedan';
