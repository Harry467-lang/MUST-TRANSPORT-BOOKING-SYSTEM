<?php
require_once 'config.php';
requireLogin();

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    // Only allow users to cancel their own pending bookings
    $stmt = mysqli_prepare($conn, "UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $userId = $_SESSION['user_id'];
    mysqli_stmt_bind_param($stmt, "ii", $id, $userId);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        setFlash('success', 'Booking cancelled successfully.');
    } else {
        setFlash('error', 'Unable to cancel this booking.');
    }
}

header('Location: dashboard.php');
exit;
?>
