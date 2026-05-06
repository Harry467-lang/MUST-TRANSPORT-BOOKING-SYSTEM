<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (empty($full_name)) $errors[] = 'Full name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    // Check if email exists
    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    // Insert new user
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role   = 'user';

        $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $full_name, $email, $phone, $hashed, $role);
        mysqli_stmt_execute($stmt);

        setFlash('success', 'Account created successfully! Please sign in.');
        header('Location: login.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — UniTransport</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,500;9..40,700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo"><i class="fas fa-user-plus"></i></div>
            <h1>Create Account</h1>
            <p>Register to book university transport</p>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= sanitize($errors[0]) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name"><i class="fas fa-user"></i> Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. Chikondi Banda" value="<?= sanitize($_POST['full_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@students.uni.ac.mw" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="phone"><i class="fas fa-phone"></i> Phone Number</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="+265 99X XXX XXX" value="<?= sanitize($_POST['phone'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 characters" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password"><i class="fas fa-check-circle"></i> Confirm</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:13px;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Sign in</a>
        </div>
    </div>
</div>
<script src="script.js"></script>
</body>
</html>
