<?php
require_once '../config.php'; requireLogin(); requireAdmin();
$isAdmin = true; $pageTitle = 'Manage Payments';

// Mark paid / pending
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']); $a = $_GET['action'];
    if ($a === 'mark_paid') { $st=mysqli_prepare($conn,"UPDATE payments SET status='paid' WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); setFlash('success',"Payment #$id paid."); }
    if ($a === 'mark_pending') { $st=mysqli_prepare($conn,"UPDATE payments SET status='pending' WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); setFlash('info',"Payment #$id set pending."); }
    header('Location: manage_payments.php'); exit;
}

// Set method (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_method'])) {
    $pid = intval($_POST['payment_id']); $method = trim($_POST['method']);
    $st = mysqli_prepare($conn, "UPDATE payments SET method=? WHERE id=?");
    mysqli_stmt_bind_param($st, "si", $method, $pid); mysqli_stmt_execute($st);
    setFlash('success', 'Payment method set.'); header('Location: manage_payments.php'); exit;
}

$filter = $_GET['status'] ?? 'all';
if (in_array($filter, ['pending','paid'])) {
    $st = mysqli_prepare($conn, "SELECT p.*, b.booking_date, b.destination, b.status as bstatus, u.full_name, c.car_name FROM payments p JOIN bookings b ON p.booking_id=b.id JOIN users u ON b.user_id=u.id JOIN cars c ON b.car_id=c.id WHERE p.status=? ORDER BY p.created_at DESC");
    mysqli_stmt_bind_param($st, "s", $filter); mysqli_stmt_execute($st); $result = mysqli_stmt_get_result($st);
} else {
    $result = mysqli_query($conn, "SELECT p.*, b.booking_date, b.destination, b.status as bstatus, u.full_name, c.car_name FROM payments p JOIN bookings b ON p.booking_id=b.id JOIN users u ON b.user_id=u.id JOIN cars c ON b.car_id=c.id ORDER BY p.created_at DESC");
}
$payments = []; while ($row = mysqli_fetch_assoc($result)) $payments[] = $row;

$totalPaid    = mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='paid'"))[0];
$totalPending = mysqli_fetch_row(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='pending'"))[0];

include '../includes/header.php';
?>
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card green"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-details"><h4>Total Paid</h4><div class="stat-number">MWK <?= number_format($totalPaid,0) ?></div></div></div>
    <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-details"><h4>Pending</h4><div class="stat-number">MWK <?= number_format($totalPending,0) ?></div></div></div>
</div>
<div class="mb-3 flex gap-1" style="flex-wrap:wrap;">
    <a href="manage_payments.php" class="btn btn-sm <?= $filter==='all'?'btn-primary':'btn-outline' ?>">All</a>
    <a href="?status=pending" class="btn btn-sm <?= $filter==='pending'?'btn-warning':'btn-outline' ?>">Pending</a>
    <a href="?status=paid" class="btn btn-sm <?= $filter==='paid'?'btn-success':'btn-outline' ?>">Paid</a>
</div>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-credit-card"></i> All Payments (<?= count($payments) ?>)</h3></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($payments)): ?><div class="empty-state"><i class="fas fa-credit-card"></i><h3>No payments</h3></div>
        <?php else: ?>
        <div class="table-wrapper"><table><thead><tr><th>ID</th><th>Booking</th><th>User</th><th>Vehicle</th><th>Amount</th><th>Method</th><th>Payment</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><strong>#<?= $p['id'] ?></strong></td>
                <td>#<?= $p['booking_id'] ?> — <?= date('d M', strtotime($p['booking_date'])) ?></td>
                <td><?= sanitize($p['full_name']) ?></td>
                <td><?= sanitize($p['car_name']) ?></td>
                <td><strong>MWK <?= number_format($p['amount'],0) ?></strong></td>
                <td>
                    <?php if ($p['method']): ?><?= sanitize($p['method']) ?>
                    <?php else: ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                        <select name="method" class="form-control" style="width:auto;padding:4px 8px;font-size:.78rem;display:inline-block;">
                            <option value="Cash">Cash</option><option value="Airtel Money">Airtel Money</option>
                            <option value="TNM Mpamba">TNM Mpamba</option><option value="Bank Card">Bank Card</option>
                        </select>
                        <button type="submit" name="set_method" class="btn btn-outline btn-xs">Set</button>
                    </form>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-<?= $p['status']==='paid'?'confirmed':'pending' ?>"><?= ucfirst($p['status']) ?></span></td>
                <td>
                    <?php if ($p['status']==='pending'): ?>
                    <a href="?action=mark_paid&id=<?= $p['id'] ?>" class="btn btn-success btn-xs"><i class="fas fa-check"></i> Paid</a>
                    <?php else: ?>
                    <a href="?action=mark_pending&id=<?= $p['id'] ?>" class="btn btn-outline btn-xs"><i class="fas fa-undo"></i> Undo</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?></tbody></table></div>
        <?php endif; ?>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
