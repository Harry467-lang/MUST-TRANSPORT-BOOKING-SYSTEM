# UniTransport — University Transport Booking System

A modern, full-featured web application for managing university vehicle bookings.
Built with PHP, MySQL, HTML/CSS, and JavaScript.

---

## Features

- **User Registration & Login** with password hashing (bcrypt)
- **Role-based access**: Admin and Normal User
- **Vehicle Booking** with double-booking prevention
- **Admin Dashboard** with stats, charts, and quick actions
- **Car Management** (Add / Edit / Delete)
- **Booking Management** (Approve / Reject / Complete / Delete)
- **User Management** (View / Delete / Toggle Role)
- **Modern, responsive UI** with sidebar navigation
- **Status badges**, hover effects, and clean tables
- **10 pre-loaded vehicles** with sample data

---

## Quick Setup (XAMPP)

### Step 1: Install XAMPP
Download and install XAMPP from https://www.apachefriends.org/
Start **Apache** and **MySQL** from the XAMPP Control Panel.

### Step 2: Copy Project Files
Copy this entire `transport-booking` folder to:
```
C:\xampp\htdocs\transport-booking
```

### Step 3: Run Setup
Open your browser and navigate to:
```
http://localhost/transport-booking/setup.php
```
This will automatically:
- Create the `uni_transport` database
- Create all required tables
- Insert sample data with properly hashed passwords
- Insert 10 vehicles and sample bookings

### Step 4: Login
Go to `http://localhost/transport-booking/` and log in:

| Role  | Email                          | Password |
|-------|--------------------------------|----------|
| Admin | admin@university.ac.mw        | admin123 |
| User  | chikondi@students.uni.ac.mw   | user123  |
| User  | tamara@students.uni.ac.mw     | user123  |
| User  | james@students.uni.ac.mw      | user123  |

### Step 5: Delete setup.php
For security, delete `setup.php` after successful setup.

---

## Alternative Setup (Manual)

1. Open phpMyAdmin: http://localhost/phpmyadmin
2. Create a new database: `uni_transport`
3. Import `database.sql` into the database
4. Then run `setup.php` to fix password hashes

---

## Project Structure

```
transport-booking/
├── index.php              # Entry point (redirects)
├── login.php              # Login page
├── register.php           # Registration page
├── dashboard.php          # User dashboard
├── booking.php            # Book a ride
├── cancel_booking.php     # Cancel own booking
├── logout.php             # Logout handler
├── config.php             # Database config & helpers
├── setup.php              # One-time database setup
├── style.css              # Main stylesheet
├── script.js              # JavaScript
├── database.sql           # Database schema & data
├── credentials.txt        # Login credentials
├── README.md              # This file
├── includes/
│   ├── header.php         # Shared header & sidebar
│   └── footer.php         # Shared footer
└── admin/
    ├── dashboard.php      # Admin overview
    ├── manage_cars.php    # CRUD for vehicles
    ├── manage_bookings.php# Approve/reject/manage
    └── manage_users.php   # View/manage users
```

---

## Database Tables

- **users** — id, full_name, email, phone, password, role, created_at
- **cars** — id, car_name, plate_number, car_type, capacity, status, created_at
- **bookings** — id, user_id, car_id, booking_date, start_time, end_time, destination, purpose, passengers, status, admin_notes, created_at

---

## Security Features

- Passwords hashed with `password_hash()` (bcrypt)
- Prepared statements (MySQLi) to prevent SQL injection
- Session-based authentication
- Role-based access control
- HTML output escaped with `htmlspecialchars()`
- Admin pages protected by `requireAdmin()`

---

## Technologies

- **Backend:** PHP 7.4+ with MySQLi
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Icons:** Font Awesome 6
- **Fonts:** DM Sans + Playfair Display (Google Fonts)
