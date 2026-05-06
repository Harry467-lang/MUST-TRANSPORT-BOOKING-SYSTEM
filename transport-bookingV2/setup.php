<?php
/**
 * ============================================
 * UniTransport — Database Setup Script (MySQLi)
 * ============================================
 * Run ONCE: http://localhost/transport-booking/setup.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'uni_transport';

// Connect without database first
$conn = mysqli_connect($host, $user, $pass);

if (!$conn) {
    die("<h2>Connection failed:</h2><p>" . mysqli_connect_error() . "</p>");
}

// Create database
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname`");
mysqli_select_db($conn, $dbname);
mysqli_set_charset($conn, "utf8mb4");

// Create tables
mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20),
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS cars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        car_name VARCHAR(100) NOT NULL,
        plate_number VARCHAR(50) NOT NULL UNIQUE,
        car_type VARCHAR(50),
        capacity INT NOT NULL,
        status ENUM('available', 'maintenance', 'retired') DEFAULT 'available',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");

mysqli_query($conn, "
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
    ) ENGINE=InnoDB
");

// Clear existing data
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "TRUNCATE TABLE bookings");
mysqli_query($conn, "TRUNCATE TABLE cars");
mysqli_query($conn, "TRUNCATE TABLE users");
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

// ============================================
// Insert users with PROPER bcrypt hashes
// ============================================
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$userHash  = password_hash('user123', PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");

$data = [
    ['System Admin',   'admin@university.ac.mw',          '+265991000000', $adminHash, 'admin'],
    ['Chikondi Banda', 'chikondi@students.uni.ac.mw',     '+265992111111', $userHash,  'user'],
    ['Tamara Phiri',   'tamara@students.uni.ac.mw',       '+265993222222', $userHash,  'user'],
    ['James Mwale',    'james@students.uni.ac.mw',        '+265994333333', $userHash,  'user'],
];

foreach ($data as $row) {
    mysqli_stmt_bind_param($stmt, "sssss", $row[0], $row[1], $row[2], $row[3], $row[4]);
    mysqli_stmt_execute($stmt);
}

// ============================================
// Insert 10 Cars
// ============================================
mysqli_query($conn, "
    INSERT INTO cars (car_name, plate_number, car_type, capacity, status) VALUES
    ('Toyota Hilux',        'MJ 1234', 'SUV',     5,  'available'),
    ('Toyota Coaster',      'MJ 5678', 'Bus',     30, 'available'),
    ('Nissan NP300',        'BT 9012', 'SUV',     5,  'available'),
    ('Toyota HiAce',        'MJ 3456', 'Van',     14, 'available'),
    ('Mitsubishi L200',     'BT 7890', 'SUV',     5,  'available'),
    ('Rosa Bus',            'MJ 2345', 'Minibus', 25, 'available'),
    ('Land Cruiser Prado',  'MJ 6789', 'SUV',     7,  'available'),
    ('Isuzu KB',            'BT 0123', 'SUV',     5,  'maintenance'),
    ('Toyota Fortuner',     'MJ 4567', 'SUV',     7,  'available'),
    ('Nissan Urvan',        'BT 8901', 'Van',     12, 'available')
");

// ============================================
// Sample Bookings
// ============================================
$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$dayAfter = date('Y-m-d', strtotime('+2 days'));

$bstmt = mysqli_prepare($conn, "
    INSERT INTO bookings (user_id, car_id, booking_date, start_time, end_time, destination, purpose, passengers, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$bookings = [
    [2, 1, $today,    '08:00:00', '12:00:00', 'Kamuzu Central Hospital',            'Medical fieldwork',       3,  'confirmed'],
    [3, 4, $today,    '09:00:00', '15:00:00', 'Lilongwe University of Agriculture', 'Inter-university seminar', 10, 'pending'],
    [4, 2, $tomorrow, '07:00:00', '17:00:00', 'Lake Malawi - Mangochi',             'Geography field trip',     25, 'confirmed'],
    [2, 7, $dayAfter, '10:00:00', '14:00:00', 'Capital Hill',                       'Policy research visit',    4,  'pending'],
];

foreach ($bookings as $b) {
    mysqli_stmt_bind_param($bstmt, "iisssssis",
        $b[0], $b[1], $b[2], $b[3], $b[4], $b[5], $b[6], $b[7], $b[8]
    );
    mysqli_stmt_execute($bstmt);
}

mysqli_close($conn);

// ============================================
// Success page
// ============================================
echo '<!DOCTYPE html>
<html>
<head>
    <title>Setup Complete</title>
    <style>
        body { font-family: "DM Sans", sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .box { background: #fff; padding: 48px; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.08); text-align: center; max-width: 500px; }
        .icon { width: 64px; height: 64px; background: #d1fae5; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; }
        h1 { color: #059669; margin-bottom: 12px; }
        p { color: #64748b; margin-bottom: 8px; }
        a.btn { display: inline-block; margin-top: 20px; padding: 12px 32px; background: #2563eb; color: #fff; border-radius: 8px; text-decoration: none; font-weight: 600; }
        a.btn:hover { background: #1e40af; }
        .cred { background: #f1f5f9; padding: 16px; border-radius: 8px; margin-top: 20px; text-align: left; font-size: 14px; line-height: 1.8; }
        .cred strong { color: #1e293b; }
    </style>
</head>
<body>
    <div class="box">
        <div class="icon">&#10003;</div>
        <h1>Setup Complete!</h1>
        <p>Database <strong>uni_transport</strong> created with all tables and sample data.</p>
        <div class="cred">
            <strong>Admin:</strong> admin@university.ac.mw / admin123<br>
            <strong>User:</strong> chikondi@students.uni.ac.mw / user123<br>
            <strong>User:</strong> tamara@students.uni.ac.mw / user123<br>
            <strong>User:</strong> james@students.uni.ac.mw / user123
        </div>
        <a href="login.php" class="btn">Go to Login &rarr;</a>
        <p style="margin-top:20px;font-size:12px;color:#94a3b8;">Delete setup.php after use for security.</p>
    </div>
</body>
</html>';
?>
