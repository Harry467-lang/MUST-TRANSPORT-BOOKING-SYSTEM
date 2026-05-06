<?php
require_once '../config.php'; requireLogin(); requireAdmin();
$isAdmin = true; $pageTitle = 'Admin Dashboard';

$totalUsers      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='user'"))[0];
$totalDrivers    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='driver'"))[0];
$totalCars       = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM cars"))[0];
$availableCars   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM cars WHERE status='available'"))[0];
$pendingBookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='pending'"))[0];
$pendingPayments = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM payments WHERE status='pending'"))[0];
$todayCount      = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE booking_date=CURDATE() AND status IN ('pending','confirmed')"))[0];
$totalRevenue    = mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'"))[0];

$recentBookings = [];
$r = mysqli_query($conn, "SELECT b.id, b.booking_date, b.start_time, b.end_time, b.destination, b.status, u.full_name, c.car_name, c.plate_number, p.status as pay_status
    FROM bookings b JOIN users u ON b.user_id=u.id JOIN cars c ON b.car_id=c.id LEFT JOIN payments p ON p.booking_id=b.id ORDER BY b.created_at DESC LIMIT 8");
while ($row = mysqli_fetch_assoc($r)) { $recentBookings[] = $row; }

include '../includes/header.php';
?>
<div class="stats-grid">
    <div class="stat-card blue"><div class="stat-icon"><i class="fas fa-users"></i></div><div class="stat-details"><h4>Users</h4><div class="stat-number"><?= $totalUsers ?></div></div></div>
    <div class="stat-card green"><div class="stat-icon"><i class="fas fa-car"></i></div><div class="stat-details"><h4>Cars</h4><div class="stat-number"><?= $availableCars ?>/<?= $totalCars ?></div></div></div>
    <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-details"><h4>Pending</h4><div class="stat-number"><?= $pendingBookings ?></div></div></div>
    <div class="stat-card red"><div class="stat-icon"><i class="fas fa-calendar-day"></i></div><div class="stat-details"><h4>Today</h4><div class="stat-number"><?= $todayCount ?></div></div></div>
</div>
<div class="stats-grid">
    <div class="stat-card blue"><div class="stat-icon"><i class="fas fa-id-card"></i></div><div class="stat-details"><h4>Drivers</h4><div class="stat-number"><?= $totalDrivers ?></div></div></div>
    <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-credit-card"></i></div><div class="stat-details"><h4>Unpaid</h4><div class="stat-number"><?= $pendingPayments ?></div></div></div>
    <div class="stat-card green"><div class="stat-icon"><i class="fas fa-money-bill"></i></div><div class="stat-details"><h4>Revenue</h4><div class="stat-number">MWK <?= number_format($totalRevenue, 0) ?></div></div></div>
</div>

<!-- Quick Actions + PDF Reports -->
<div class="mb-3 flex gap-2" style="flex-wrap:wrap;">
    <a href="manage_bookings.php" class="btn btn-primary btn-sm"><i class="fas fa-calendar-check"></i> Bookings</a>
    <a href="manage_cars.php" class="btn btn-outline btn-sm"><i class="fas fa-car"></i> Cars</a>
    <a href="manage_drivers.php" class="btn btn-outline btn-sm"><i class="fas fa-id-card"></i> Drivers</a>
    <a href="manage_payments.php" class="btn btn-outline btn-sm"><i class="fas fa-credit-card"></i> Payments</a>
    <a href="manage_users.php" class="btn btn-outline btn-sm"><i class="fas fa-users"></i> Users</a>
    <a href="../booking.php" class="btn btn-outline btn-sm"><i class="fas fa-plus"></i> New Booking</a>
</div>
<div class="mb-3 flex gap-2" style="flex-wrap:wrap;">
    <a href="report_bookings.php" class="btn btn-success btn-sm" target="_blank"><i class="fas fa-file-pdf"></i> Bookings Report (PDF)</a>
    <a href="report_payments.php" class="btn btn-success btn-sm" target="_blank"><i class="fas fa-file-pdf"></i> Payments Report (PDF)</a>
</div>

<div class="card">
    <div class="card-header"><h3><i class="fas fa-history"></i> Recent Bookings</h3><a href="manage_bookings.php" class="btn btn-outline btn-xs">View All <i class="fas fa-arrow-right"></i></a></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($recentBookings)): ?><div class="empty-state"><i class="fas fa-inbox"></i><h3>No bookings yet</h3></div>
        <?php else: ?>
        <div class="table-wrapper"><table><thead><tr><th>ID</th><th>User</th><th>Vehicle</th><th>Date</th><th>Time</th><th>Status</th><th>Payment</th></tr></thead><tbody>
            <?php foreach ($recentBookings as $b): ?>
            <tr>
                <td>#<?= $b['id'] ?></td><td><?= sanitize($b['full_name']) ?></td>
                <td><strong><?= sanitize($b['car_name']) ?></strong><br><small style="color:var(--gray-400)"><?= sanitize($b['plate_number']) ?></small></td>
                <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                <td><?= date('H:i', strtotime($b['start_time'])) ?>–<?= date('H:i', strtotime($b['end_time'])) ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td><?php if ($b['pay_status']): ?><span class="badge badge-<?= $b['pay_status']==='paid'?'confirmed':'pending' ?>"><?= ucfirst($b['pay_status']) ?></span><?php else: ?>—<?php endif; ?></td>
            </tr>
            <?php endforeach; ?></tbody></table></div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
