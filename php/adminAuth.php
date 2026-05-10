<?php
session_start();
include 'db.php';

$error = '';
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['admin_username'] = $row['username'];
                    if ($is_ajax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'redirect' => '../pages/adminDashboard.php']);
                        exit;
                    }
                    header("Location: ../pages/adminDashboard.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "Invalid username.";
            }
            $stmt->close();
        } else {
            $error = "DB error.";
        }
    } else {
        $error = "All fields are required.";
    }
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $error]);
        exit;
    }
} else {
    // If accessed directly and not POST, redirect to login page
    header("Location: ../pages/adminLogin.php");
    exit;
}
?>
