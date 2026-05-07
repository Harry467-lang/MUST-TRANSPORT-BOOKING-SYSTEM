<?php
require_once '../config.php'; requireLogin(); requireAdmin();
$isAdmin = true; $pageTitle = 'Manage Bookings';

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']); $a = $_GET['action'];
    if ($a === 'confirm')  { $st=mysqli_prepare($conn,"UPDATE bookings SET status='confirmed' WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); setFlash('success',"Booking #$id confirmed."); }
    elseif ($a === 'cancel') { $st=mysqli_prepare($conn,"UPDATE bookings SET status='cancelled' WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); setFlash('success',"Booking #$id cancelled."); }
    elseif ($a === 'complete') { $st=mysqli_prepare($conn,"UPDATE bookings SET status='completed' WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); setFlash('success',"Booking #$id completed."); }
    elseif ($a === 'delete') { $st=mysqli_prepare($conn,"DELETE FROM bookings WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); setFlash('success',"Booking #$id deleted."); }
    elseif ($a === 'mark_paid') { $st=mysqli_prepare($conn,"UPDATE payments SET status='paid' WHERE booking_id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); setFlash('success',"Payment for #$id marked paid."); }
    header('Location: manage_bookings.php'); exit;
}

// Handle payment method update (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_method'])) {
    $pid = intval($_POST['payment_id']); $method = trim($_POST['method'] ?? '');
    $st = mysqli_prepare($conn, "UPDATE payments SET method=? WHERE id=?");
    mysqli_stmt_bind_param($st, "si", $method, $pid); mysqli_stmt_execute($st);
    setFlash('success', 'Payment method updated.'); header('Location: manage_bookings.php'); exit;
}

$filter = $_GET['status'] ?? 'all';
if (in_array($filter, ['pending','confirmed','cancelled','completed'])) {
    $st = mysqli_prepare($conn, "SELECT b.*, u.full_name, u.email, c.car_name, c.plate_number, p.id as pid, p.status as pay_status, p.amount as pay_amount, p.method as pay_method
        FROM bookings b JOIN users u ON b.user_id=u.id JOIN cars c ON b.car_id=c.id LEFT JOIN payments p ON p.booking_id=b.id WHERE b.status=? ORDER BY b.created_at DESC");
    mysqli_stmt_bind_param($st, "s", $filter); mysqli_stmt_execute($st); $result = mysqli_stmt_get_result($st);
} else {
    $result = mysqli_query($conn, "SELECT b.*, u.full_name, u.email, c.car_name, c.plate_number, p.id as pid, p.status as pay_status, p.amount as pay_amount, p.method as pay_method
        FROM bookings b JOIN users u ON b.user_id=u.id JOIN cars c ON b.car_id=c.id LEFT JOIN payments p ON p.booking_id=b.id ORDER BY b.created_at DESC");
}
$bookings = []; while ($row = mysqli_fetch_assoc($result)) $bookings[] = $row;

include '../includes/header.php';
?>
<div class="mb-3 flex gap-1" style="flex-wrap:wrap;">
    <a href="manage_bookings.php" class="btn btn-sm <?= $filter==='all'?'btn-primary':'btn-outline' ?>">All</a>
    <a href="?status=pending" class="btn btn-sm <?= $filter==='pending'?'btn-warning':'btn-outline' ?>">Pending</a>
    <a href="?status=confirmed" class="btn btn-sm <?= $filter==='confirmed'?'btn-success':'btn-outline' ?>">Confirmed</a>
    <a href="?status=completed" class="btn btn-sm <?= $filter==='completed'?'btn-primary':'btn-outline' ?>">Completed</a>
    <a href="?status=cancelled" class="btn btn-sm <?= $filter==='cancelled'?'btn-danger':'btn-outline' ?>">Cancelled</a>
</div>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-calendar-check"></i> All Bookings (<?= count($bookings) ?>)</h3></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($bookings)): ?><div class="empty-state"><i class="fas fa-inbox"></i><h3>No bookings found</h3></div>
        <?php else: ?>
        <div class="table-wrapper"><table><thead><tr><th>ID</th><th>User</th><th>Vehicle</th><th>Date</th><th>Time</th><th>Destination</th><th>Status</th><th>Payment</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td><strong>#<?= $b['id'] ?></strong></td>
                <td><?= sanitize($b['full_name']) ?><br><small style="color:var(--gray-400)"><?= sanitize($b['email']) ?></small></td>
                <td><?= sanitize($b['car_name']) ?><br><small style="color:var(--gray-400)"><?= sanitize($b['plate_number']) ?></small></td>
                <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
                <td><?= date('H:i', strtotime($b['start_time'])) ?>–<?= date('H:i', strtotime($b['end_time'])) ?></td>
                <td><?= sanitize($b['destination']) ?><?php if ($b['purpose']): ?><br><small style="color:var(--gray-400)"><?= sanitize($b['purpose']) ?></small><?php endif; ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td>
                    <?php if ($b['pay_status']): ?>
                    <span class="badge badge-<?= $b['pay_status']==='paid'?'confirmed':'pending' ?>"><?= ucfirst($b['pay_status']) ?></span>
                    <br><small>MWK <?= number_format($b['pay_amount'],0) ?></small>
                    <?php if ($b['pay_method']): ?><br><small style="color:var(--gray-500)"><?= sanitize($b['pay_method']) ?></small><?php endif; ?>
                    <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                    <div class="btn-group">
                        <?php if ($b['status']==='pending'): ?>
                        <a href="?action=confirm&id=<?= $b['id'] ?>" class="btn btn-success btn-xs" title="Approve"><i class="fas fa-check"></i></a>
                        <a href="?action=cancel&id=<?= $b['id'] ?>" class="btn btn-danger btn-xs" title="Reject"><i class="fas fa-times"></i></a>
                        <?php elseif ($b['status']==='confirmed'): ?>
                        <a href="?action=complete&id=<?= $b['id'] ?>" class="btn btn-primary btn-xs" title="Complete"><i class="fas fa-flag-checkered"></i></a>
                        <a href="?action=cancel&id=<?= $b['id'] ?>" class="btn btn-danger btn-xs" title="Cancel"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                        <?php if ($b['pay_status']==='pending'): ?>
                        <a href="?action=mark_paid&id=<?= $b['id'] ?>" class="btn btn-warning btn-xs" title="Mark Paid"><i class="fas fa-money-bill"></i></a>
                        <?php endif; ?>
                        <a href="?action=delete&id=<?= $b['id'] ?>" class="btn btn-outline btn-xs" onclick="return confirmAction('Delete #<?= $b['id'] ?>?',this.href)"><i class="fas fa-trash"></i></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?></tbody></table></div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
