<?php

require_once '../config.php';
requireLogin();
requireAdmin();

$result = mysqli_query($conn, "
    SELECT b.*, u.full_name, c.car_name, c.plate_number,
           p.amount as pay_amount, p.status as pay_status, p.method as pay_method,
           d.full_name as driver_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    LEFT JOIN payments p ON p.booking_id = b.id
    LEFT JOIN users d ON c.driver_id = d.id
    ORDER BY b.booking_date DESC, b.start_time ASC
");
$bookings = [];
while ($row = mysqli_fetch_assoc($result)) $bookings[] = $row;

$total = count($bookings);
$confirmed = 0; $pending = 0; $cancelled = 0;
foreach ($bookings as $b) {
    if ($b['status'] === 'confirmed') $confirmed++;
    elseif ($b['status'] === 'pending') $pending++;
    elseif ($b['status'] === 'cancelled') $cancelled++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings Report — MUST Booking System</title>
    <style>
        /* Print-optimized styles */
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 20px; }
        .report-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .report-header h1 { font-size: 22px; color: #2563eb; margin: 0; }
        .report-header p { color: #666; margin: 5px 0 0; }
        .summary { display: flex; gap: 20px; margin-bottom: 20px; }
        .summary-box { flex: 1; padding: 10px 15px; background: #f1f5f9; border-radius: 6px; text-align: center; }
        .summary-box strong { display: block; font-size: 20px; color: #1e293b; }
        .summary-box small { color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #2563eb; color: white; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11px; }
        tr:nth-child(even) { background: #f8fafc; }
        .status { padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .s-confirmed { background: #d1fae5; color: #065f46; }
        .s-pending { background: #fef3c7; color: #92400e; }
        .s-cancelled { background: #fee2e2; color: #991b1b; }
        .s-completed { background: #dbeafe; color: #1e40af; }
        .s-paid { background: #d1fae5; color: #065f46; }
        .footer { text-align: center; color: #94a3b8; font-size: 10px; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { padding: 10px 30px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; margin: 0 5px; }
        .no-print button:hover { background: #1e40af; }
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
    <h1>🚐 MUST Booking System — Bookings Report</h1>
    <p>Generated on <?= date('d F Y \a\t H:i') ?></p>
</div>

<div class="summary">
    <div class="summary-box"><strong><?= $total ?></strong><small>Total Bookings</small></div>
    <div class="summary-box"><strong><?= $confirmed ?></strong><small>Confirmed</small></div>
    <div class="summary-box"><strong><?= $pending ?></strong><small>Pending</small></div>
    <div class="summary-box"><strong><?= $cancelled ?></strong><small>Cancelled</small></div>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>User</th>
            <th>Vehicle</th>
            <th>Driver</th>
            <th>Date</th>
            <th>Time</th>
            <th>Destination</th>
            <th>Pax</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
            <td>#<?= $b['id'] ?></td>
            <td><?= sanitize($b['full_name']) ?></td>
            <td><?= sanitize($b['car_name']) ?> (<?= sanitize($b['plate_number']) ?>)</td>
            <td><?= $b['driver_name'] ? sanitize($b['driver_name']) : '—' ?></td>
            <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
            <td><?= date('H:i', strtotime($b['start_time'])) ?>–<?= date('H:i', strtotime($b['end_time'])) ?></td>
            <td><?= sanitize($b['destination']) ?></td>
            <td><?= $b['passengers'] ?></td>
            <td><span class="status s-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
            <td><span class="status s-<?= ($b['pay_status'] ?? 'pending') ?>"><?= ucfirst($b['pay_status'] ?? 'pending') ?></span></td>
            <td>MWK <?= number_format($b['pay_amount'] ?? 0, 0) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="footer">
    MUST Booking System — MUST Transport Management<br>
    Report contains <?= $total ?> booking record<?= $total !== 1 ? 's' : '' ?>.
</div>

</body>
</html>
