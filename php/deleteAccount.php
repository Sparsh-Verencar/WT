<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    // if AJAX, return JSON
    $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }
    header("Location: ../pages/login.php");
    exit;
}

include 'db.php';
$user_id = $_SESSION['user_id'];
// detect ajax
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Delete all orders where buyer_id OR seller_id matches the user's ID
$stmt = $conn->prepare("DELETE FROM orders WHERE buyer_id = ? OR seller_id = ?");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$stmt->close();

// Delete all books where seller_id matches the user's ID
$stmt = $conn->prepare("DELETE FROM books WHERE seller_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Delete the user row from the users table
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

session_destroy();
if ($is_ajax) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'redirect' => '../index.php']);
    exit;
}
header("Location: ../index.php");
exit;
?>
