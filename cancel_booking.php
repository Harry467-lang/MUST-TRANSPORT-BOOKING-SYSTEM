<?php
require_once 'config.php';
requireLogin();
$id = intval($_GET['id'] ?? 0);
if ($id > 0) {
    $userId = $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, "ii", $id, $userId); mysqli_stmt_execute($stmt);
    setFlash(mysqli_stmt_affected_rows($stmt) > 0 ? 'success' : 'error',
             mysqli_stmt_affected_rows($stmt) > 0 ? 'Booking cancelled.' : 'Unable to cancel.');
}
header('Location: dashboard.php'); exit;
?>
