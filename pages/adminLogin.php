<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: adminDashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <form action="../php/adminAuth.php" method="POST">
        <div class="form-badge">!</div>
        <header>Admin</header>
        <div id="ajax-message" role="status" aria-live="polite"></div>
        <section id="input-group">
            <label for="username">Username</label>
            <input id="Username-input" type="text" name="username" required>
            <br>
            <label for="password">Password</label>
            <input type="password" name="password" required>
            <br>
        </section>
        <section id="login-signup">
            <button type="submit">Login</button>
            <h4>
                Back to user login
                <a href="login.php">
                    User Login
                </a>
            </h4>
        </section>
    </form>
    <script src="../js/adminLogin.js"></script>
    <script src="../js/ajax/adminLogin-ajax.js"></script>
    <script>
        $(document).ready(function() {
            $('button').off('click').on('click', function(e) {
                // intentionally empty — form submit handled separately
            });
        });
    </script>
    <link rel="stylesheet" href="../styles/adminLogin.css">
</body>
</html>
