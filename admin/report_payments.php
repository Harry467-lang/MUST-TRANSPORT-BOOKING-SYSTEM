<?php
/**
 * Payments Report — Printable / Save as PDF
 */
require_once '../config.php';
requireLogin();
requireAdmin();

$result = mysqli_query($conn, "
    SELECT p.*, b.booking_date, b.destination, b.status as bstatus,
           u.full_name, c.car_name
    FROM payments p
    JOIN bookings b ON p.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    ORDER BY p.created_at DESC
");
$payments = [];
while ($row = mysqli_fetch_assoc($result)) $payments[] = $row;

$totalPaid    = 0; $totalPending = 0; $paidCount = 0; $pendingCount = 0;
foreach ($payments as $p) {
    if ($p['status'] === 'paid') { $totalPaid += $p['amount']; $paidCount++; }
    else { $totalPending += $p['amount']; $pendingCount++; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments Report — MUST Booking System</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #059669; padding-bottom: 15px; }
        .report-header h1 { font-size: 22px; color: #059669; margin: 0; }
        .report-header p { color: #666; margin: 5px 0 0; }
        .summary { display: flex; gap: 20px; margin-bottom: 20px; }
        .summary-box { flex: 1; padding: 10px 15px; background: #f1f5f9; border-radius: 6px; text-align: center; }
        .summary-box strong { display: block; font-size: 20px; color: #1e293b; }
        .summary-box small { color: #64748b; }
        .summary-box.green { background: #d1fae5; }
        .summary-box.yellow { background: #fef3c7; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #059669; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        tr:nth-child(even) { background: #f8fafc; }
        .status { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .s-paid { background: #d1fae5; color: #065f46; }
        .s-pending { background: #fef3c7; color: #92400e; }
        .footer { text-align: center; color: #94a3b8; font-size: 10px; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { padding: 10px 30px; background: #059669; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin: 0 5px; }
        .no-print button:hover { background: #047857; }
        .no-print .back { background: #64748b; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">🖨️ Print / Save as PDF</button>
    <button class="back" onclick="window.location.href='dashboard.php'">← Back to Dashboard</button>
</div>

<div class="report-header">
    <h1>💳 MUST Booking System — Payments Report</h1>
    <p>Generated on <?= date('d F Y \a\t H:i') ?></p>
</div>

<div class="summary">
    <div class="summary-box"><strong><?= count($payments) ?></strong><small>Total Payments</small></div>
    <div class="summary-box green"><strong>MWK <?= number_format($totalPaid, 0) ?></strong><small><?= $paidCount ?> Paid</small></div>
    <div class="summary-box yellow"><strong>MWK <?= number_format($totalPending, 0) ?></strong><small><?= $pendingCount ?> Pending</small></div>
    <div class="summary-box"><strong>MWK <?= number_format($totalPaid + $totalPending, 0) ?></strong><small>Grand Total</small></div>
</div>

<table>
    <thead>
        <tr>
            <th>Payment ID</th>
            <th>Booking</th>
            <th>User</th>
            <th>Vehicle</th>
            <th>Date</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($payments as $p): ?>
        <tr>
            <td>#<?= $p['id'] ?></td>
            <td>#<?= $p['booking_id'] ?></td>
            <td><?= sanitize($p['full_name']) ?></td>
            <td><?= sanitize($p['car_name']) ?></td>
            <td><?= date('d M Y', strtotime($p['booking_date'])) ?></td>
            <td><strong>MWK <?= number_format($p['amount'], 0) ?></strong></td>
            <td><?= $p['method'] ? sanitize($p['method']) : '—' ?></td>
            <td><span class="status s-<?= $p['status'] ?>"><?= ucfirst($p['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="footer">
    MUST Booking System — Payment Records<br>
    Report contains <?= count($payments) ?> payment record<?= count($payments) !== 1 ? 's' : '' ?>.
</div>

</body>
</html>
