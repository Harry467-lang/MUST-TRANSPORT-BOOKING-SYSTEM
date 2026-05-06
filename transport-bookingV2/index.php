<?php
require_once 'config.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
} else {
    header('Location: login.php');
}
exit;
?>
