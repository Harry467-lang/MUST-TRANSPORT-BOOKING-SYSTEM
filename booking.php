<?php
require_once 'config.php';
requireLogin();
$pageTitle = 'Book a Ride';
$errors = [];
$cars = [];

$filter_date  = $_GET['booking_date'] ?? $_POST['booking_date'] ?? '';
$filter_start = $_GET['start_time']   ?? $_POST['start_time']   ?? '';
$filter_end   = $_GET['end_time']     ?? $_POST['end_time']     ?? '';

// Fetch available cars (with driver name + price)
if ($filter_date && $filter_start && $filter_end) {
    $stmt = mysqli_prepare($conn, "
        SELECT c.*, u.full_name as driver_name FROM cars c
        LEFT JOIN users u ON c.driver_id = u.id
        WHERE c.status = 'available'
        AND c.id NOT IN (
            SELECT car_id FROM bookings WHERE booking_date = ?
            AND status IN ('pending','confirmed') AND (start_time < ? AND end_time > ?)
        ) ORDER BY c.car_name
    ");
    mysqli_stmt_bind_param($stmt, "sss", $filter_date, $filter_end, $filter_start);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "
        SELECT c.*, u.full_name as driver_name FROM cars c
        LEFT JOIN users u ON c.driver_id = u.id
        WHERE c.status = 'available' ORDER BY c.car_name
    ");
}
while ($row = mysqli_fetch_assoc($result)) { $cars[] = $row; }

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && intval($_POST['car_id'] ?? 0) > 0) {
    $car_id = intval($_POST['car_id']); $booking_date = $_POST['booking_date'] ?? '';
    $start_time = $_POST['start_time'] ?? ''; $end_time = $_POST['end_time'] ?? '';
    $destination = trim($_POST['destination'] ?? ''); $purpose = trim($_POST['purpose'] ?? '');
    $passengers = intval($_POST['passengers'] ?? 1);

    if ($car_id <= 0) $errors[] = 'Select a vehicle.';
    if (!$booking_date) $errors[] = 'Date required.';
    if (!$start_time || !$end_time) $errors[] = 'Times required.';
    if ($start_time >= $end_time) $errors[] = 'End time must be after start.';
    if (!$destination) $errors[] = 'Destination required.';
    if ($booking_date < date('Y-m-d')) $errors[] = 'Cannot book in the past.';

    // Capacity + price check
    $carPrice = 0;
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT capacity, price_per_trip FROM cars WHERE id = ? AND status = 'available'");
        mysqli_stmt_bind_param($stmt, "i", $car_id); mysqli_stmt_execute($stmt);
        $car = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if (!$car) $errors[] = 'Vehicle unavailable.';
        elseif ($passengers > $car['capacity']) $errors[] = 'Exceeds capacity (' . $car['capacity'] . ').';
        else $carPrice = $car['price_per_trip'];
    }

    // Overlap check
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM bookings WHERE car_id=? AND booking_date=? AND status IN ('pending','confirmed') AND (start_time < ? AND end_time > ?)");
        mysqli_stmt_bind_param($stmt, "isss", $car_id, $booking_date, $end_time, $start_time); mysqli_stmt_execute($stmt);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['cnt'] > 0) $errors[] = 'Vehicle already booked for that time.';
    }

    // Insert booking + payment
    if (empty($errors)) {
        $userId = $_SESSION['user_id'];
        $stmt = mysqli_prepare($conn, "INSERT INTO bookings (user_id, car_id, booking_date, start_time, end_time, destination, purpose, passengers) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iisssssi", $userId, $car_id, $booking_date, $start_time, $end_time, $destination, $purpose, $passengers);
        mysqli_stmt_execute($stmt);
        $bookingId = mysqli_insert_id($conn);

        // Create pending payment with car price
        $stmt2 = mysqli_prepare($conn, "INSERT INTO payments (booking_id, amount, status) VALUES (?, ?, 'pending')");
        mysqli_stmt_bind_param($stmt2, "id", $bookingId, $carPrice); mysqli_stmt_execute($stmt2);

        setFlash('success', 'Booking submitted! Awaiting approval.');
        header('Location: dashboard.php'); exit;
    }
}
include 'includes/header.php';
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= sanitize($errors[0]) ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap: 24px;">
    <!-- Booking Form -->
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-edit"></i> Booking Details</h3></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" id="car_id" name="car_id" value="">
                <div class="form-group">
                    <label><i class="fas fa-car"></i> Selected Vehicle</label>
                    <div id="selectedVehicle" class="form-control" style="background:var(--gray-50);color:var(--gray-500);cursor:default;">Click a vehicle to select</div>
                </div>
                <!-- Price display -->
                <div class="form-group" id="priceDisplay" style="display:none;">
                    <label><i class="fas fa-tag"></i> Trip Price</label>
                    <div class="form-control" style="background:var(--success-bg);color:#065f46;font-weight:700;cursor:default;" id="priceText">MWK 0</div>
                </div>
                <div class="form-group"><label><i class="fas fa-calendar"></i> Date</label>
                    <input type="date" id="booking_date" name="booking_date" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= sanitize($filter_date) ?>" required></div>
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-clock"></i> Start</label><input type="time" id="start_time" name="start_time" class="form-control" value="<?= sanitize($filter_start ?: '08:00') ?>" required></div>
                    <div class="form-group"><label><i class="fas fa-clock"></i> End</label><input type="time" id="end_time" name="end_time" class="form-control" value="<?= sanitize($filter_end ?: '17:00') ?>" required></div>
                </div>
                <div class="form-group"><label><i class="fas fa-map-marker-alt"></i> Destination</label><input type="text" name="destination" class="form-control" placeholder="e.g. Kamuzu Central Hospital" value="<?= sanitize($_POST['destination'] ?? '') ?>" required></div>
                <div class="form-row">
                    <div class="form-group"><label><i class="fas fa-users"></i> Passengers</label><input type="number" name="passengers" class="form-control" value="<?= sanitize($_POST['passengers'] ?? '1') ?>" min="1" required></div>
                    <div class="form-group"><label><i class="fas fa-info-circle"></i> Purpose</label><input type="text" name="purpose" class="form-control" placeholder="e.g. Field trip" value="<?= sanitize($_POST['purpose'] ?? '') ?>"></div>
                </div>
                <div class="btn-group" style="width:100%;gap:10px;">
                    <button type="button" onclick="checkAvailability()" class="btn btn-outline" style="flex:1;"><i class="fas fa-search"></i> Check</button>
                    <button type="submit" class="btn btn-primary" style="flex:2;"><i class="fas fa-paper-plane"></i> Submit Booking</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Available Vehicles -->
    <div>
        <h3 class="section-title"><i class="fas fa-car-side"></i> Available Vehicles
            <?php if ($filter_date): ?><small style="font-weight:400;color:var(--gray-400);"> — <?= date('d M Y', strtotime($filter_date)) ?></small><?php endif; ?>
        </h3>
        <?php if (empty($cars)): ?>
        <div class="card"><div class="empty-state" style="padding:40px;"><i class="fas fa-car-crash"></i><h3>No vehicles available</h3><p>Try a different date or time.</p></div></div>
        <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <?php foreach ($cars as $c): ?>
            <div class="car-card car-selectable" data-id="<?= $c['id'] ?>" data-name="<?= sanitize($c['car_name']) ?>" data-price="<?= $c['price_per_trip'] ?>" onclick="selectCar(this)">
                <div style="display:flex;">
                    <div style="width:80px;background:var(--brand-100);display:flex;align-items:center;justify-content:center;font-size:1.8rem;color:var(--brand-300);flex-shrink:0;">
                        <i class="fas fa-<?= ($c['car_type']==='Bus'||$c['car_type']==='Minibus') ? 'bus' : ($c['car_type']==='Van' ? 'shuttle-van' : 'car') ?>"></i>
                    </div>
                    <div class="car-card-body" style="padding:14px 16px;">
                        <h4 style="font-size:.95rem;margin-bottom:6px;"><?= sanitize($c['car_name']) ?>
                            <span style="float:right;color:var(--success);font-size:.85rem;">MWK <?= number_format($c['price_per_trip'], 0) ?></span>
                        </h4>
                        <div class="car-meta" style="margin-bottom:0;">
                            <span><i class="fas fa-hashtag"></i> <?= sanitize($c['plate_number']) ?></span>
                            <span><i class="fas fa-tag"></i> <?= sanitize($c['car_type']) ?></span>
                            <span><i class="fas fa-users"></i> <?= $c['capacity'] ?> seats</span>
                            <?php if ($c['driver_name']): ?><span><i class="fas fa-id-card"></i> <?= sanitize($c['driver_name']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>@media(max-width:900px){div[style*="grid-template-columns: 1fr 1fr"]{grid-template-columns:1fr!important;}}</style>
<script>
function selectCar(el) {
    document.querySelectorAll('.car-selectable').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('car_id').value = el.dataset.id;
    document.getElementById('selectedVehicle').innerText = el.dataset.name;
    document.getElementById('selectedVehicle').style.color = 'var(--gray-900)';
    document.getElementById('selectedVehicle').style.fontWeight = '600';
    // Show price
    var price = parseFloat(el.dataset.price);
    document.getElementById('priceDisplay').style.display = 'block';
    document.getElementById('priceText').innerText = 'MWK ' + price.toLocaleString();
}
function checkAvailability() {
    var d=document.getElementById('booking_date').value, s=document.getElementById('start_time').value, e=document.getElementById('end_time').value;
    if(!d||!s||!e){alert('Fill date & times first.');return;}
    window.location.href='booking.php?booking_date='+d+'&start_time='+s+'&end_time='+e;
}
</script>
<?php include 'includes/footer.php'; ?>
