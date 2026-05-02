<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

include 'db.php';
$user_id = $_SESSION['user_id'];

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
header("Location: ../index.php");
exit;
?>
