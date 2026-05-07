<?php
$host='localhost'; $user='root'; $pass=''; $dbname='uni_transport';
$conn = mysqli_connect($host, $user, $pass);
if (!$conn) die("Connection failed: " . mysqli_connect_error());
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$dbname`");
mysqli_select_db($conn, $dbname); mysqli_set_charset($conn, "utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(100) NOT NULL, email VARCHAR(100) NOT NULL UNIQUE, phone VARCHAR(20), password VARCHAR(255) NOT NULL, role ENUM('admin','user','driver') DEFAULT 'user', created_at DATETIME DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS cars (id INT AUTO_INCREMENT PRIMARY KEY, car_name VARCHAR(100) NOT NULL, plate_number VARCHAR(50) NOT NULL UNIQUE, car_type VARCHAR(50), capacity INT NOT NULL, status ENUM('available','maintenance','retired') DEFAULT 'available', driver_id INT DEFAULT NULL, price_per_trip DECIMAL(10,2) DEFAULT 0.00, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL) ENGINE=InnoDB");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS bookings (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, car_id INT NOT NULL, booking_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, destination VARCHAR(255), purpose TEXT, passengers INT DEFAULT 1, status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending', admin_notes TEXT, created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE) ENGINE=InnoDB");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS payments (id INT AUTO_INCREMENT PRIMARY KEY, booking_id INT NOT NULL, amount DECIMAL(10,2) DEFAULT 0.00, method VARCHAR(50), status ENUM('pending','paid') DEFAULT 'pending', created_at DATETIME DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE) ENGINE=InnoDB");

mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");
mysqli_query($conn, "TRUNCATE TABLE payments"); mysqli_query($conn, "TRUNCATE TABLE bookings");
mysqli_query($conn, "TRUNCATE TABLE cars"); mysqli_query($conn, "TRUNCATE TABLE users");
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");

$ah=password_hash('admin123',PASSWORD_DEFAULT); $uh=password_hash('user123',PASSWORD_DEFAULT); $dh=password_hash('driver123',PASSWORD_DEFAULT);
$st=mysqli_prepare($conn,"INSERT INTO users (full_name,email,phone,password,role) VALUES (?,?,?,?,?)");
$users=[
    ['MUST Admin','admin@must.ac.mw','+265991000000',$ah,'admin'],
    ['Chikondi Banda','chikondi@students.must.ac.mw','+265992111111',$uh,'user'],
    ['Tamara Phiri','tamara@students.must.ac.mw','+265993222222',$uh,'user'],
    ['James Mwale','james@students.must.ac.mw','+265994333333',$uh,'user'],
    ['Samuel Phiri','samuel@drivers.must.ac.mw','+265999123456',$dh,'driver'],
    ['Isaac Mwale','isaac@drivers.must.ac.mw','+265888654321',$dh,'driver'],
    ['Jacob Chikhula','jacob@drivers.must.ac.mw','+265991776655',$dh,'driver']
];
foreach($users as $u){mysqli_stmt_bind_param($st,"sssss",$u[0],$u[1],$u[2],$u[3],$u[4]);mysqli_stmt_execute($st);}

mysqli_query($conn, "INSERT INTO cars (car_name,plate_number,car_type,capacity,status,driver_id,price_per_trip) VALUES
('Toyota Hilux','MJ 1234','SUV',5,'available',5,15000),('Toyota Coaster','MJ 5678','Bus',30,'available',6,50000),
('Nissan NP300','BT 9012','SUV',5,'available',NULL,15000),('Toyota HiAce','MJ 3456','Van',14,'available',7,25000),
('Mitsubishi L200','BT 7890','SUV',5,'available',NULL,15000),('Rosa Bus','MJ 2345','Minibus',25,'available',5,35000),
('Land Cruiser Prado','MJ 6789','SUV',7,'available',6,20000),('Isuzu KB','BT 0123','SUV',5,'maintenance',NULL,15000),
('Toyota Fortuner','MJ 4567','SUV',7,'available',7,20000),('Nissan Urvan','BT 8901','Van',12,'available',NULL,25000)");

$td=date('Y-m-d'); $tm=date('Y-m-d',strtotime('+1 day')); $da=date('Y-m-d',strtotime('+2 days'));
$bs=mysqli_prepare($conn,"INSERT INTO bookings (user_id,car_id,booking_date,start_time,end_time,destination,purpose,passengers,status) VALUES (?,?,?,?,?,?,?,?,?)");
$bk=[[2,1,$td,'08:00','12:00','Kamuzu Central Hospital','Medical fieldwork',3,'confirmed'],
     [3,4,$td,'09:00','15:00','LUANAR','Inter-university seminar',10,'pending'],
     [4,2,$tm,'07:00','17:00','Lake Malawi - Mangochi','Geography field trip',25,'confirmed'],
     [2,7,$da,'10:00','14:00','Capital Hill','Policy research',4,'pending']];
foreach($bk as $b){mysqli_stmt_bind_param($bs,"iisssssis",$b[0],$b[1],$b[2],$b[3],$b[4],$b[5],$b[6],$b[7],$b[8]);mysqli_stmt_execute($bs);}

// Sample payments — all with methods now (users always pick during booking)
mysqli_query($conn, "INSERT INTO payments (booking_id,amount,method,status) VALUES
(1,15000,'Airtel Money','paid'),
(2,25000,'TNM Mpamba','pending'),
(3,50000,'Bank Transfer','paid'),
(4,20000,'Cash','pending')");
mysqli_close($conn);

echo '<!DOCTYPE html><html><head><title>Setup Complete — MUST Booking System</title><style>
body{font-family:Arial,sans-serif;background:#faf6ef url("assets/MUST_campus.jpg") center/cover no-repeat fixed;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;position:relative;}
body::before{content:"";position:fixed;inset:0;background:rgba(14,31,51,.8);z-index:0;}
.box{background:#fff;padding:48px;border-radius:8px;box-shadow:0 10px 30px rgba(14,31,51,.18);text-align:center;max-width:520px;border-top:5px solid #c16c4f;position:relative;z-index:1;}
.box img{width:80px;margin:0 auto 12px;display:block;}
h1{color:#2e7d4f;margin-bottom:12px;font-family:Georgia,serif;}
p{color:#6e6557;margin:5px 0;}
a{display:inline-block;margin-top:20px;padding:12px 32px;background:#c16c4f;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;}
.cred{background:#faf6ef;padding:16px;border-radius:8px;margin-top:20px;text-align:left;font-size:14px;line-height:1.9;border-left:4px solid #c16c4f;}
.cred strong{color:#0e1f33;}
.sys-name{color:#c16c4f;font-size:12px;letter-spacing:3px;text-transform:uppercase;font-weight:bold;}
</style></head><body>
<div class="box">
<img src="assets/logoMUST.png" alt="MUST">
<div class="sys-name">MUST Booking System</div>
<h1>✓ Setup Complete!</h1>
<p>Database created with drivers, prices &amp; payments.</p>
<div class="cred">
<strong>Admin:</strong> admin@must.ac.mw / admin123<br>
<strong>User:</strong> chikondi@students.must.ac.mw / user123<br>
<strong>Driver:</strong> samuel@drivers.must.ac.mw / driver123
</div>
<a href="login.php">Go to Login →</a>
<p style="margin-top:20px;font-size:11px;color:#a39a8b;">Delete setup.php after use.</p>
</div></body></html>';
?>
