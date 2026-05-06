<?php
require_once '../config.php'; requireLogin(); requireAdmin();
$isAdmin = true; $pageTitle = 'Manage Drivers';

if (isset($_GET['action']) && $_GET['action']==='delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $st = mysqli_prepare($conn, "UPDATE cars SET driver_id=NULL WHERE driver_id=?"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st);
    $st = mysqli_prepare($conn, "DELETE FROM users WHERE id=? AND role='driver'"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st);
    setFlash('success', 'Driver removed.'); header('Location: manage_drivers.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? ''); $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? ''); $pw = $_POST['password'] ?? ''; $eid = intval($_POST['edit_id'] ?? 0);
    if (empty($name) || empty($phone)) { setFlash('error', 'Name and phone required.'); }
    elseif ($eid > 0) {
        $st = mysqli_prepare($conn, "UPDATE users SET full_name=?, phone=? WHERE id=? AND role='driver'");
        mysqli_stmt_bind_param($st, "ssi", $name, $phone, $eid); mysqli_stmt_execute($st); setFlash('success', 'Driver updated.');
    } else {
        if (empty($email)) { setFlash('error', 'Email required.'); }
        elseif (strlen($pw) < 6) { setFlash('error', 'Password min 6 chars.'); }
        else {
            $ch = mysqli_prepare($conn, "SELECT id FROM users WHERE email=?"); mysqli_stmt_bind_param($ch,"s",$email); mysqli_stmt_execute($ch); mysqli_stmt_store_result($ch);
            if (mysqli_stmt_num_rows($ch) > 0) { setFlash('error', 'Email exists.'); }
            else { $h = password_hash($pw, PASSWORD_DEFAULT); $role = 'driver';
                $st = mysqli_prepare($conn, "INSERT INTO users (full_name,email,phone,password,role) VALUES (?,?,?,?,?)");
                mysqli_stmt_bind_param($st,"sssss",$name,$email,$phone,$h,$role); mysqli_stmt_execute($st); setFlash('success','Driver added.');
            }
        }
    }
    header('Location: manage_drivers.php'); exit;
}

$editDriver = null;
if (isset($_GET['edit'])) { $id=intval($_GET['edit']); $st=mysqli_prepare($conn,"SELECT * FROM users WHERE id=? AND role='driver'"); mysqli_stmt_bind_param($st,"i",$id); mysqli_stmt_execute($st); $editDriver=mysqli_fetch_assoc(mysqli_stmt_get_result($st)); }

$drivers = []; $r = mysqli_query($conn, "SELECT u.*, (SELECT COUNT(*) FROM cars WHERE driver_id=u.id) as car_count FROM users u WHERE u.role='driver' ORDER BY u.full_name");
while ($row = mysqli_fetch_assoc($r)) $drivers[] = $row;

include '../includes/header.php';
?>
<div style="display:grid;grid-template-columns:380px 1fr;gap:24px;">
    <div class="card" style="align-self:start;">
        <div class="card-header"><h3><i class="fas fa-<?= $editDriver?'edit':'plus' ?>"></i> <?= $editDriver?'Edit':'Add' ?> Driver</h3>
        <?php if ($editDriver): ?><a href="manage_drivers.php" class="btn btn-outline btn-xs"><i class="fas fa-times"></i></a><?php endif; ?></div>
        <div class="card-body"><form method="POST"><input type="hidden" name="edit_id" value="<?= $editDriver['id'] ?? 0 ?>">
            <div class="form-group"><label><i class="fas fa-user"></i> Full Name</label><input type="text" name="full_name" class="form-control" value="<?= sanitize($editDriver['full_name'] ?? '') ?>" required></div>
            <?php if (!$editDriver): ?>
            <div class="form-group"><label><i class="fas fa-envelope"></i> Email</label><input type="email" name="email" class="form-control" placeholder="driver@uni.ac.mw" required></div>
            <div class="form-group"><label><i class="fas fa-lock"></i> Password</label><input type="password" name="password" class="form-control" placeholder="Min 6 chars" required></div>
            <?php endif; ?>
            <div class="form-group"><label><i class="fas fa-phone"></i> Phone</label><input type="text" name="phone" class="form-control" value="<?= sanitize($editDriver['phone'] ?? '') ?>" required></div>
            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> <?= $editDriver?'Update':'Add' ?></button>
        </form></div>
    </div>
    <div class="card">
        <div class="card-header"><h3><i class="fas fa-id-card"></i> Drivers (<?= count($drivers) ?>)</h3></div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($drivers)): ?><div class="empty-state"><i class="fas fa-id-card"></i><h3>No drivers</h3></div>
            <?php else: ?>
            <div class="table-wrapper"><table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Cars</th><th>Actions</th></tr></thead><tbody>
                <?php foreach ($drivers as $d): ?>
                <tr><td><?= $d['id'] ?></td>
                    <td><strong><?= sanitize($d['full_name']) ?></strong></td>
                    <td><?= sanitize($d['email']) ?></td><td><?= sanitize($d['phone'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $d['car_count']>0?'confirmed':'pending' ?>"><?= $d['car_count'] ?></span></td>
                    <td><div class="btn-group"><a href="?edit=<?= $d['id'] ?>" class="btn btn-primary btn-xs"><i class="fas fa-edit"></i></a>
                        <a href="?action=delete&id=<?= $d['id'] ?>" class="btn btn-danger btn-xs" onclick="return confirmAction('Remove <?= sanitize($d['full_name']) ?>?',this.href)"><i class="fas fa-trash"></i></a></div></td>
                </tr><?php endforeach; ?></tbody></table></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>@media(max-width:900px){div[style*="grid-template-columns:380px"]{grid-template-columns:1fr!important;}}</style>
<?php include '../includes/footer.php'; ?>
