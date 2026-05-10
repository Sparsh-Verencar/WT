<?php
session_start();

// Hard-coded admin credentials
$ADMIN_USERNAME = 'admin';
$ADMIN_PASSWORD = 'password123';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Validate credentials
    if ($username === $ADMIN_USERNAME && $password === $ADMIN_PASSWORD) {
        // Set admin session
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: ../pages/admin.php');
        exit;
    } else {
        $_SESSION['admin_error'] = 'Invalid username or password';
        header('Location: ../pages/adminlogin.php');
        exit;
    }
} else {
    header('Location: ../pages/adminlogin.php');
    exit;
}
?>
