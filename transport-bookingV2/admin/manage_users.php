<?php
require_once '../config.php';
requireLogin();
requireAdmin();
$isAdmin = true;
$pageTitle = 'Manage Users';

// ============================================
// Handle delete
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($id === $_SESSION['user_id']) {
        setFlash('error', 'You cannot delete your own account.');
    } else {
        $stmt = mysqli_prepare($conn, "DELETE FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        setFlash('success', 'User deleted successfully.');
    }

    header('Location: manage_users.php');
    exit;
}

// ============================================
// Handle role toggle
// ============================================
if (isset($_GET['action']) && $_GET['action'] === 'toggle_role' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($id === $_SESSION['user_id']) {
        setFlash('error', 'You cannot change your own role.');
    } else {
        // Get current role
        $stmt = mysqli_prepare($conn, "SELECT role FROM users WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            $newRole = ($row['role'] === 'admin') ? 'user' : 'admin';
            $stmt2 = mysqli_prepare($conn, "UPDATE users SET role = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt2, "si", $newRole, $id);
            mysqli_stmt_execute($stmt2);
            setFlash('success', 'User role updated to ' . ucfirst($newRole) . '.');
        }
    }

    header('Location: manage_users.php');
    exit;
}

// ============================================
// Get all users with booking count
// ============================================
$result = mysqli_query($conn, "
    SELECT u.*,
        (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as booking_count
    FROM users u
    ORDER BY u.created_at DESC
");

$users = [];
while ($row = mysqli_fetch_assoc($result)) {
    $users[] = $row;
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users"></i> All Users (<?= count($users) ?>)</h3>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Bookings</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:50%;background:var(--brand-100);color:var(--brand-600);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
                                    <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                                </div>
                                <strong><?= sanitize($u['full_name']) ?></strong>
                            </div>
                        </td>
                        <td><?= sanitize($u['email']) ?></td>
                        <td><?= sanitize($u['phone'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= $u['booking_count'] ?></td>
                        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="?action=toggle_role&id=<?= $u['id'] ?>" class="btn btn-outline btn-xs"
                                   title="Toggle Role">
                                    <i class="fas fa-user-shield"></i>
                                </a>
                                <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                <a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-xs"
                                   onclick="return confirmAction('Delete user <?= sanitize($u['full_name']) ?>? This will also delete all their bookings.', this.href)"
                                   title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
