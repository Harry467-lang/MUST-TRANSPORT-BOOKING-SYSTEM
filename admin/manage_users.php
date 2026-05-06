<?php
require_once '../config.php'; requireLogin(); requireAdmin();
$isAdmin = true; $pageTitle = 'Manage Users';

if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']); $a = $_GET['action'];
    if ($a === 'delete' && $id !== $_SESSION['user_id']) {
        $st=mysqli_prepare($conn,"UPDATE cars SET driver_id=NULL WHERE driver_id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st);
        $st=mysqli_prepare($conn,"DELETE FROM users WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st);
        setFlash('success','User deleted.');
    } elseif ($a === 'toggle_role' && $id !== $_SESSION['user_id']) {
        $st=mysqli_prepare($conn,"SELECT role FROM users WHERE id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
        if ($row) { $roles=['user'=>'driver','driver'=>'admin','admin'=>'user']; $nr=$roles[$row['role']]??'user';
            $st2=mysqli_prepare($conn,"UPDATE users SET role=? WHERE id=?"); mysqli_stmt_bind_param($st2,"si",$nr,$id); mysqli_stmt_execute($st2);
            setFlash('success','Role → '.ucfirst($nr));
        }
    } elseif ($id === $_SESSION['user_id']) { setFlash('error','Cannot modify your own account.'); }
    header('Location: manage_users.php'); exit;
}

$users=[]; $r=mysqli_query($conn, "SELECT u.*, (SELECT COUNT(*) FROM bookings WHERE user_id=u.id) as bc FROM users u ORDER BY u.created_at DESC");
while ($row=mysqli_fetch_assoc($r)) $users[]=$row;

include '../includes/header.php';
?>
<div class="card">
    <div class="card-header"><h3><i class="fas fa-users"></i> All Users (<?= count($users) ?>)</h3></div>
    <div class="card-body" style="padding:0;"><div class="table-wrapper"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Bookings</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($users as $u): $rb=match($u['role']){'admin'=>'admin','driver'=>'driver',default=>'user'}; ?>
        <tr><td><?= $u['id'] ?></td>
            <td><div style="display:flex;align-items:center;gap:10px;"><div style="width:34px;height:34px;border-radius:50%;background:var(--brand-100);color:var(--brand-600);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;"><?= strtoupper(substr($u['full_name'],0,1)) ?></div><strong><?= sanitize($u['full_name']) ?></strong></div></td>
            <td><?= sanitize($u['email']) ?></td><td><?= sanitize($u['phone']??'—') ?></td>
            <td><span class="badge badge-<?= $rb ?>"><?= ucfirst($u['role']) ?></span></td>
            <td><?= $u['bc'] ?></td><td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td><div class="btn-group">
                <a href="?action=toggle_role&id=<?= $u['id'] ?>" class="btn btn-outline btn-xs" title="Cycle: user→driver→admin"><i class="fas fa-user-shield"></i></a>
                <?php if ($u['id'] !== $_SESSION['user_id']): ?><a href="?action=delete&id=<?= $u['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirmAction('Delete <?= sanitize($u['full_name']) ?>?',this.href)"><i class="fas fa-trash"></i></a><?php endif; ?>
            </div></td>
        </tr><?php endforeach; ?></tbody></table></div></div>
</div>
<?php include '../includes/footer.php'; ?>
