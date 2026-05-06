<?php
require_once '../config.php';
requireLogin();
requireAdmin();
$isAdmin = true;
$pageTitle = 'Manage Cars';

// DELETE VEHICLE
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {

    $id = intval($_GET['id']);

    // Check active bookings
    $stmt = mysqli_prepare($conn, "
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE car_id = ? 
        AND status IN ('pending','confirmed')
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row['count'] > 0) {
        setFlash('error', 'Cannot delete: this vehicle has active bookings.');
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM cars WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);

        setFlash('success', 'Vehicle deleted successfully.');
    }

    header('Location: manage_cars.php');
    exit;
}


// ADD / EDIT VEHICLE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $car_name = trim($_POST['car_name'] ?? '');
    $plate_number = trim($_POST['plate_number'] ?? '');
    $car_type = $_POST['car_type'] ?? '';
    $capacity = intval($_POST['capacity'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $edit_id = intval($_POST['edit_id'] ?? 0);

    if (!$car_name || !$plate_number || !$car_type || $capacity <= 0) {

        setFlash('error', 'All fields are required.');

    } else {

        if ($edit_id > 0) {

            // UPDATE
            $stmt = mysqli_prepare($conn, "
                UPDATE cars 
                SET car_name=?, plate_number=?, car_type=?, capacity=?, status=? 
                WHERE id=?
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "sssisi",
                $car_name,
                $plate_number,
                $car_type,
                $capacity,
                $status,
                $edit_id
            );

            mysqli_stmt_execute($stmt);

            setFlash('success', 'Vehicle updated successfully.');

        } else {

            // INSERT
            $stmt = mysqli_prepare($conn, "
                INSERT INTO cars (car_name, plate_number, car_type, capacity, status)
                VALUES (?, ?, ?, ?, ?)
            ");

            mysqli_stmt_bind_param(
                $stmt,
                "sssis",
                $car_name,
                $plate_number,
                $car_type,
                $capacity,
                $status
            );

            mysqli_stmt_execute($stmt);

            setFlash('success', 'Vehicle added successfully.');
        }
    }

    header('Location: manage_cars.php');
    exit;
}


// GET VEHICLE FOR EDIT
$editCar = null;

if (isset($_GET['edit'])) {

    $id = intval($_GET['edit']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM cars WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $editCar = mysqli_fetch_assoc($result);
}


// GET ALL VEHICLES
$cars = [];

$result = mysqli_query($conn, "SELECT * FROM cars ORDER BY car_name");

while ($row = mysqli_fetch_assoc($result)) {
    $cars[] = $row;
}


include '../includes/header.php';
?>

<div style="display:grid; grid-template-columns: 380px 1fr; gap:24px;">

    <!-- Add/Edit Form -->
    <div class="card" style="align-self:start;">
        <div class="card-header">
            <h3>
                <i class="fas fa-<?= $editCar ? 'edit' : 'plus' ?>"></i>
                <?= $editCar ? 'Edit Vehicle' : 'Add Vehicle' ?>
            </h3>

            <?php if ($editCar): ?>
                <a href="manage_cars.php" class="btn btn-outline btn-xs">
                    <i class="fas fa-times"></i> Cancel
                </a>
            <?php endif; ?>
        </div>

        <div class="card-body">
            <form method="POST">

                <input type="hidden" name="edit_id" value="<?= $editCar['id'] ?? 0 ?>">

                <div class="form-group">
                    <label>Vehicle Name</label>
                    <input 
                        type="text"
                        name="car_name"
                        class="form-control"
                        value="<?= sanitize($editCar['car_name'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Plate Number</label>
                    <input 
                        type="text"
                        name="plate_number"
                        class="form-control"
                        value="<?= sanitize($editCar['plate_number'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select name="car_type" class="form-control" required>

                        <option value="">Select type</option>

                        <?php foreach (['Sedan','SUV','Van','Bus','Minibus'] as $type): ?>
                            <option 
                                value="<?= $type ?>"
                                <?= ($editCar && $editCar['car_type'] === $type) ? 'selected' : '' ?>
                            >
                                <?= $type ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Capacity</label>
                    <input 
                        type="number"
                        name="capacity"
                        class="form-control"
                        value="<?= $editCar['capacity'] ?? '' ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="available" <?= ($editCar && $editCar['status'] === 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="maintenance" <?= ($editCar && $editCar['status'] === 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                        <option value="retired" <?= ($editCar && $editCar['status'] === 'retired') ? 'selected' : '' ?>>Retired</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <?= $editCar ? 'Update Vehicle' : 'Add Vehicle' ?>
                </button>

            </form>
        </div>
    </div>


    <!-- Cars Table -->
    <div class="card">
        <div class="card-header">
            <h3>All Vehicles (<?= count($cars) ?>)</h3>
        </div>

        <div class="card-body" style="padding:0;">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vehicle</th>
                        <th>Plate</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($cars as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><strong><?= sanitize($c['car_name']) ?></strong></td>
                            <td><?= sanitize($c['plate_number']) ?></td>
                            <td><?= sanitize($c['car_type']) ?></td>
                            <td><?= $c['capacity'] ?> seats</td>

                            <td>
                                <span class="badge badge-<?= $c['status'] ?>">
                                    <?= ucfirst($c['status']) ?>
                                </span>
                            </td>

                            <td>
                                <a href="?edit=<?= $c['id'] ?>">Edit</a>
                                <a href="?action=delete&id=<?= $c['id'] ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>