<?php
require_once '../config.php'; requireLogin(); requireAdmin();
$isAdmin = true; $pageTitle = 'Manage Cars';

// DELETE
if (isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as cnt FROM bookings WHERE car_id=? AND status IN ('pending','confirmed')");
    mysqli_stmt_bind_param($stmt, "i", $id); mysqli_stmt_execute($stmt);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['cnt'] > 0) { setFlash('error', 'Cannot delete: has active bookings.'); }
    else { $st = mysqli_prepare($conn, "DELETE FROM cars WHERE id=?"); mysqli_stmt_bind_param($st, "i", $id); mysqli_stmt_execute($st); setFlash('success', 'Vehicle deleted.'); }
    header('Location: manage_cars.php'); exit;
}

// ADD / EDIT (now includes price_per_trip + driver_id)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $car_name = trim($_POST['car_name'] ?? ''); $plate = trim($_POST['plate_number'] ?? '');
    $car_type = $_POST['car_type'] ?? ''; $cap = intval($_POST['capacity'] ?? 0);
    $status = $_POST['status'] ?? 'available'; $driver_id = intval($_POST['driver_id'] ?? 0);
    $price = floatval($_POST['price_per_trip'] ?? 0); $edit_id = intval($_POST['edit_id'] ?? 0);
    $dv = $driver_id > 0 ? $driver_id : null;

    if (!$car_name || !$plate || !$car_type || $cap <= 0) { setFlash('error', 'All fields required.'); }
    elseif ($edit_id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE cars SET car_name=?, plate_number=?, car_type=?, capacity=?, status=?, driver_id=?, price_per_trip=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssisidi", $car_name, $plate, $car_type, $cap, $status, $dv, $price, $edit_id);
        mysqli_stmt_execute($stmt); setFlash('success', 'Vehicle updated.');
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO cars (car_name, plate_number, car_type, capacity, status, driver_id, price_per_trip) VALUES (?,?,?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "sssisid", $car_name, $plate, $car_type, $cap, $status, $dv, $price);
        mysqli_stmt_execute($stmt); setFlash('success', 'Vehicle added.');
    }
    header('Location: manage_cars.php'); exit;
}

// Edit car
$editCar = null;
if (isset($_GET['edit'])) { $id=intval($_GET['edit']); $st=mysqli_prepare($conn,"SELECT * FROM cars WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); $editCar=mysqli_fetch_assoc(mysqli_stmt_get_result($st)); }

// All cars with driver name
$cars=[]; $r=mysqli_query($conn, "SELECT c.*, u.full_name as driver_name FROM cars c LEFT JOIN users u ON c.driver_id=u.id ORDER BY c.car_name");
while ($row=mysqli_fetch_assoc($r)) $cars[]=$row;

// Drivers for dropdown
$drivers=[]; $r=mysqli_query($conn, "SELECT id, full_name FROM users WHERE role='driver' ORDER BY full_name");
while ($row=mysqli_fetch_assoc($r)) $drivers[]=$row;

include '../includes/header.php';
?>
<div style="display:grid;grid-template-columns:380px 1fr;gap:24px;">
    <div class="card" style="align-self:start;">
        <div class="card-header"><h3><i class="fas fa-<?= $editCar?'edit':'plus' ?>"></i> <?= $editCar?'Edit':'Add' ?> Vehicle</h3>
        <?php if ($editCar): ?><a href="manage_cars.php" class="btn btn-outline btn-xs"><i class="fas fa-times"></i> Cancel</a><?php endif; ?></div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="edit_id" value="<?= $editCar['id'] ?? 0 ?>">
                <div class="form-group"><label>Vehicle Name</label><input type="text" name="car_name" class="form-control" value="<?= sanitize($editCar['car_name'] ?? '') ?>" required></div>
                <div class="form-group"><label>Plate Number</label><input type="text" name="plate_number" class="form-control" value="<?= sanitize($editCar['plate_number'] ?? '') ?>" required></div>
                <div class="form-group"><label>Type</label><select name="car_type" class="form-control" required><option value="">Select</option>
                    <?php foreach (['Sedan','SUV','Van','Bus','Minibus'] as $t): ?><option value="<?= $t ?>" <?= ($editCar && $editCar['car_type']===$t)?'selected':'' ?>><?= $t ?></option><?php endforeach; ?></select></div>
                <div class="form-row">
                    <div class="form-group"><label>Capacity</label><input type="number" name="capacity" class="form-control" min="1" value="<?= $editCar['capacity'] ?? '' ?>" required></div>
                    <div class="form-group"><label><i class="fas fa-tag"></i> Price (MWK)</label><input type="number" name="price_per_trip" class="form-control" min="0" step="500" value="<?= $editCar['price_per_trip'] ?? '0' ?>"></div>
                </div>
                <div class="form-group"><label><i class="fas fa-id-card"></i> Driver</label><select name="driver_id" class="form-control"><option value="0">— No driver —</option>
                    <?php foreach ($drivers as $d): ?><option value="<?= $d['id'] ?>" <?= ($editCar && $editCar['driver_id']==$d['id'])?'selected':'' ?>><?= sanitize($d['full_name']) ?></option><?php endforeach; ?></select></div>
                <div class="form-group"><label>Status</label><select name="status" class="form-control">
                    <option value="available" <?= ($editCar && $editCar['status']==='available')?'selected':'' ?>>Available</option>
                    <option value="maintenance" <?= ($editCar && $editCar['status']==='maintenance')?'selected':'' ?>>Maintenance</option>
                    <option value="retired" <?= ($editCar && $editCar['status']==='retired')?'selected':'' ?>>Retired</option></select></div>
                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> <?= $editCar?'Update':'Add' ?> Vehicle</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-car"></i> All Vehicles (<?= count($cars) ?>)</h3></div>
        <div class="card-body" style="padding:0;"><div class="table-wrapper"><table><thead><tr><th>ID</th><th>Vehicle</th><th>Plate</th><th>Type</th><th>Seats</th><th>Price</th><th>Driver</th><th>Status</th><th>Actions</th></tr></thead><tbody>
            <?php foreach ($cars as $c): ?>
            <tr><td><?= $c['id'] ?></td><td><strong><?= sanitize($c['car_name']) ?></strong></td><td><?= sanitize($c['plate_number']) ?></td><td><?= sanitize($c['car_type']) ?></td><td><?= $c['capacity'] ?></td>
                <td><strong>MWK <?= number_format($c['price_per_trip'],0) ?></strong></td>
                <td><?= $c['driver_name'] ? '<span class="badge badge-confirmed">'.sanitize($c['driver_name']).'</span>' : '<span style="color:var(--gray-400)">—</span>' ?></td>
                <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span></td>
                <td><div class="btn-group"><a href="?edit=<?= $c['id'] ?>" class="btn btn-primary btn-xs"><i class="fas fa-edit"></i></a>
                    <a href="?action=delete&id=<?= $c['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirmAction('Delete <?= sanitize($c['car_name']) ?>?',this.href)"><i class="fas fa-trash"></i></a></div></td>
            </tr><?php endforeach; ?></tbody></table></div></div>
    </div>
</div>
<style>@media(max-width:900px){div[style*="grid-template-columns:380px"]{grid-template-columns:1fr!important;}}</style>
<?php include '../includes/footer.php'; ?>
