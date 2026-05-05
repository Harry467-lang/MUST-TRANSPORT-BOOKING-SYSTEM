<?php
require_once '../config.php';
requireLogin();
requireAdmin();
$isAdmin = true;
$pageTitle = 'Admin Dashboard';

// ============================================
// Stats (all using the global $conn from config.php)
// ============================================
$totalUsers      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='user'"))[0];
$totalCars       = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM cars"))[0];
$availableCars   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM cars WHERE status='available'"))[0];
$totalBookings   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"))[0];
$pendingBookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='pending'"))[0];

// Recent bookings
$recentBookings = [];
$result = mysqli_query($conn, "
    SELECT b.id, b.booking_date, b.start_time, b.end_time, b.destination, b.status,
           u.full_name, c.car_name, c.plate_number
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    ORDER BY b.created_at DESC
    LIMIT 8
");
while ($row = mysqli_fetch_assoc($result)) {
    $recentBookings[] = $row;
}

// Today's bookings
$todayCount = mysqli_fetch_row(mysqli_query($conn, "
    SELECT COUNT(*) FROM bookings
    WHERE booking_date = CURDATE() AND status IN ('pending','confirmed')
"))[0];

include '../includes/header.php';
?>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card blue">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-details">
            <h4>Registered Users</h4>
            <div class="stat-number"><?= $totalUsers ?></div>
        </div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-car"></i></div>
        <div class="stat-details">
            <h4>Available Cars</h4>
            <div class="stat-number"><?= $availableCars ?> / <?= $totalCars ?></div>
        </div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-details">
            <h4>Pending Approvals</h4>
            <div class="stat-number"><?= $pendingBookings ?></div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-details">
            <h4>Today's Rides</h4>
            <div class="stat-number"><?= $todayCount ?></div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="mb-3 flex gap-2" style="flex-wrap:wrap;">
    <a href="manage_bookings.php" class="btn btn-primary btn-sm">
        <i class="fas fa-calendar-check"></i> Manage Bookings
    </a>
    <a href="manage_cars.php" class="btn btn-outline btn-sm">
        <i class="fas fa-car"></i> Manage Cars
    </a>
    <a href="manage_users.php" class="btn btn-outline btn-sm">
        <i class="fas fa-users"></i> View Users
    </a>
    <a href="../booking.php" class="btn btn-outline btn-sm">
        <i class="fas fa-plus"></i> New Booking
    </a>
</div>

<!-- Recent Bookings Table -->
<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history"></i> Recent Bookings</h3>
        <a href="manage_bookings.php" class="btn btn-outline btn-xs">View All <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($recentBookings)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>No bookings yet</h3>
            <p>Bookings will appear here once users start booking.</p>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Vehicle</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Destination</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                    <tr>
                        <td>#<?= $b['id'] ?></td>
                        <td><?= sanitize($b['full_name']) ?></td>
                        <td>
                            <strong><?= sanitize($b['car_name']) ?></strong><br>
                            <small style="color:var(--gray-400)"><?= sanitize($b['plate_number']) ?></small>
                        </td>
                        <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                        <td><?= date('H:i', strtotime($b['start_time'])) ?>–<?= date('H:i', strtotime($b['end_time'])) ?></td>
                        <td><?= sanitize($b['destination']) ?></td>
                        <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
