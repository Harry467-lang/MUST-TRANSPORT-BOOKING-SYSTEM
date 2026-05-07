<?php
require_once 'config.php';
if (isLoggedIn()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? ''); $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) { $error = 'Please fill in all fields.'; }
    else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email); mysqli_stmt_execute($stmt);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id']; $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email']; $_SESSION['role'] = $user['role'];
            setFlash('success', 'Welcome back, ' . explode(' ', $user['full_name'])[0] . '!');
            header('Location: ' . ($user['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php')); exit;
        } else { $error = 'Invalid email or password.'; }
    }
}
$pageTitle = 'Login';
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - MUST Booking System</title><link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,500;9..40,700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head><body>
<div class="auth-page"><div class="auth-card">
    <div class="auth-header"><div class="auth-logo"><img src="assets/logoMUST.png alt= "MUST logo"></div><h1>MUST Booking System</h1><p>Sign in to your transport account</p></div>
    <?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?></div><?php endif; ?>
    <form method="POST">
        <div class="form-group"><label><i class="fas fa-envelope"></i> Email</label><input type="email" name="email" class="form-control" placeholder="you@must.ac.mw" value="<?= sanitize($_POST['email'] ?? '') ?>" required></div>
        <div class="form-group"><label><i class="fas fa-lock"></i> Password</label><input type="password" name="password" class="form-control" placeholder="Enter your password" required></div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:13px;"><i class="fas fa-sign-in-alt"></i> Sign In</button>
    </form>
    <div class="auth-footer">Don't have an account? <a href="register.php">Create one</a></div>
</div></div>
<script src="script.js"></script></body></html>
