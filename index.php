<?php
session_start();
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        Book Spark- Your one stop solution to buy and sell books
    </title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav id="nav">
        <img id="logo" src="images/logo.png" alt="logo" width="50" height="50">
        <div id="auth-but-group">
            <?php if ($is_logged_in): ?>
                <a href="pages/myaccountpage.php">
                    <button>My Account</button>
                </a>
                <a href="php/logout.php">
                    <button>Logout</button>
                </a>
            <?php else: ?>
                <a href="pages/login.php">
                    <button>Login</button>
                </a>
                <a href="pages/createAccount.php">
                    <button>Create Account</button>
                </a>
                <a href="pages/adminlogin.php">
                    <button>Admin Login</button>
                </a>
            <?php endif; ?>
        </div>
    </nav>
    <section id="header">
        <main id="shade"></main>
        <h1>Book Spark</h1>
        <br>
        <h3>
            Your one stop solution for buying and selling books
        </h3>
        <a href="pages/<?= $is_logged_in ? 'explorepage.php' : 'login.php' ?>">
            <button id="cta">Explore</button>
        </a>
    </section>
    <link rel="stylesheet" href="styles/style.css">
    <script src="js/index.js"></script>
    <script src="js/ajax/account-ajax.js"></script>
</body>
</html>
