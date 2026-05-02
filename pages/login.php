<?php
session_start();
include '../php/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['username'] = $row['username'];
                    header("Location: explorepage.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Invalid email.";
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
    <title>Login</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <form action="login.php" method="POST">
        <div class="form-badge">!</div>
        <header>Login</header>
        <?php if (!empty($error)): ?>
            <p style="color: #FF006E; font-family: 'Arial Black', sans-serif; text-align: center; text-shadow: 1px 1px 0px #fff;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <section id="input-group">
            <label for="email">Email</label>
            <input id="Email-input" type="email" name="email" required>
            <br>
            <label for="password">Password</label>
            <input type="password" name="password" required>
            <br>
        </section>
        <section id="login-signup">
            <button type="submit">Login</button>
            <h4>
                Don't have an account?
                <a href="createAccount.php">
                    create account
                </a>
            </h4>
        </section>
    </form>
    <script src="../js/login.js"></script>
    <script>
        $(document).ready(function() {
            // Overwrite the JS behavior for the form submission to allow PHP POST
            $('button').off('click').on('click', function(e) {
                // HTML5 validation lets native submit proceed
            });
        });
    </script>
    <link rel="stylesheet" href="../styles/login.css">
</body>
</html>
