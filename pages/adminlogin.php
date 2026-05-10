<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Book Spark</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav id="nav">
        <img id="logo" src="../images/logo.png" alt="logo" width="50" height="50">
        <div id="auth-but-group">
            <a href="../index.php">
                <button>Back to Home</button>
            </a>
        </div>
    </nav>

    <section id="login-container">
        <div id="login-form-wrapper">
            <h2>Admin Login</h2>
            <?php if (isset($_SESSION['admin_error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_SESSION['admin_error']); ?>
                </div>
                <?php unset($_SESSION['admin_error']); ?>
            <?php endif; ?>

            <form id="admin-login-form" method="POST" action="../php/adminlogin.php">
                <div class="form-group">
                    <label for="admin-username">Username:</label>
                    <input type="text" id="admin-username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="admin-password">Password:</label>
                    <input type="password" id="admin-password" name="password" required>
                </div>

                <button type="submit" class="login-button">Login</button>
            </form>
        </div>
    </section>

    <link rel="stylesheet" href="../styles/login.css">
    <style>
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
    </style>
</body>
</html>
