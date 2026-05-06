<?php
// ============================================
// Database Configuration — MySQLi
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'uni_transport');
define('DB_USER', 'root');
define('DB_PASS', '');

// Create mysqli connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("<div style='padding:40px;text-align:center;font-family:sans-serif;'>
        <h2>Database Connection Failed</h2>
        <p>Please make sure MySQL is running and the database has been imported.</p>
        <p style='color:#888;'>Error: " . mysqli_connect_error() . "</p>
    </div>");
}

mysqli_set_charset($conn, "utf8mb4");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// Helper Functions
// ============================================

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
