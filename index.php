<?php
// Root redirect
require_once __DIR__ . '/application/config.php';
if (isLoggedIn()) {
    redirect(BASE_URL . '/presentation/' . $_SESSION['role'] . '/dashboard.php');
} else {
    redirect(BASE_URL . '/presentation/login.php');
}
?>
