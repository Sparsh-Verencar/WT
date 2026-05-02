<?php
session_start();
include '../php/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "DB error.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <form action="createAccount.php" method="POST">
        <div class="form-badge">✦</div>
        <header>Create Account</header>
        <?php if (!empty($error)): ?>
            <p style="color: #FF006E; font-family: 'Arial Black', sans-serif; text-align: center; text-shadow: 1px 1px 0px #fff;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <section id="input-group">
            <label for="username">Username</label>
            <input type="text" name="username" required>
            <br>
            <label for="email">Email</label>
            <input type="email" name="email" required>
            <br>
            <label for="password">Password</label>
            <input type="password" name="password" required>
            <br>
        </section>
        <section id="signup-button">
            <!-- Native submit, unbind JS preventDefault from interfering with the submission -->
            <button type="submit">Create Account</button>
        </section>
    </form>
    <link rel="stylesheet" href="../styles/createAccount.css">
    <script src="../js/createAccount.js"></script>
    <script>
        $(document).ready(function() {
            // Overwrite the JS behavior for the form submission to allow PHP POST
            $('button').off('click').on('click', function(e) {
                // HTML5 required handles validation empty, this lets the native submit proceed
            });
        });
    </script>
</body>
</html>
