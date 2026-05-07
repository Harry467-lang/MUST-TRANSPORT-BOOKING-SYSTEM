<?php
require_once 'config.php'; requireLogin();
$pageTitle = 'My Dashboard'; $userId = $_SESSION['user_id'];

$totalBookings     = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE user_id = $userId"))[0];
$pendingBookings   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE user_id = $userId AND status='pending'"))[0];
$confirmedBookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE user_id = $userId AND status='confirmed'"))[0];
$cancelledBookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE user_id = $userId AND status='cancelled'"))[0];

$stmt = mysqli_prepare($conn, "
    SELECT b.*, c.car_name, c.plate_number, p.status as pay_status, p.amount as pay_amount
    FROM bookings b JOIN cars c ON b.car_id = c.id LEFT JOIN payments p ON p.booking_id = b.id
    WHERE b.user_id = ? ORDER BY b.created_at DESC LIMIT 10
");
mysqli_stmt_bind_param($stmt, "i", $userId); mysqli_stmt_execute($stmt);
$recentBookings = []; $r = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($r)) { $recentBookings[] = $row; }
include 'includes/header.php';
?>
<div class="stats-grid">
    <div class="stat-card blue"><div class="stat-icon"><i class="fas fa-calendar-alt"></i></div><div class="stat-details"><h4>Total</h4><div class="stat-number"><?= $totalBookings ?></div></div></div>
    <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-details"><h4>Pending</h4><div class="stat-number"><?= $pendingBookings ?></div></div></div>
    <div class="stat-card green"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-details"><h4>Confirmed</h4><div class="stat-number"><?= $confirmedBookings ?></div></div></div>
    <div class="stat-card red"><div class="stat-icon"><i class="fas fa-times-circle"></i></div><div class="stat-details"><h4>Cancelled</h4><div class="stat-number"><?= $cancelledBookings ?></div></div></div>
</div>
<div class="mb-3"><a href="booking.php" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Book a Ride</a></div>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-history"></i> My Recent Bookings</h3></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($recentBookings)): ?>
        <div class="empty-state"><i class="fas fa-calendar-plus"></i><h3>No bookings yet</h3><p>Book your first ride!</p><a href="booking.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Book Now</a></div>
        <?php else: ?>
        <div class="table-wrapper"><table><thead><tr><th>#</th><th>Vehicle</th><th>Date</th><th>Time</th><th>Destination</th><th>Status</th><th>Payment</th><th>Action</th></tr></thead><tbody>
            <?php foreach ($recentBookings as $i => $b): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><strong><?= sanitize($b['car_name']) ?></strong><br><small style="color:var(--gray-400)"><?= sanitize($b['plate_number']) ?></small></td>
                <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                <td><?= date('H:i', strtotime($b['start_time'])) ?>–<?= date('H:i', strtotime($b['end_time'])) ?></td>
                <td><?= sanitize($b['destination']) ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td><?php if ($b['pay_status']): ?><span class="badge badge-<?= $b['pay_status']==='paid'?'confirmed':'pending' ?>"><?= ucfirst($b['pay_status']) ?></span><br><small style="color:var(--gray-500)">MWK <?= number_format($b['pay_amount'],0) ?></small><?php else: ?><span style="color:var(--gray-400)">—</span><?php endif; ?></td>
                <td><?php if ($b['status']==='pending'): ?><a href="cancel_booking.php?id=<?= $b['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirmAction('Cancel?',this.href)"><i class="fas fa-times"></i> Cancel</a><?php else: ?>—<?php endif; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody></table></div>
        <?php endif; ?>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
