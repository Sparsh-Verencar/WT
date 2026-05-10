<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

include 'db.php';

$rate = isset($_POST['rate']) ? (float)$_POST['rate'] : null;

if ($rate !== null && $rate >= 0 && $rate <= 100) {
    $stmt = $conn->prepare("UPDATE commission_rate SET rate = ? WHERE id = 1");
    $stmt->bind_param("d", $rate);
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'DB update failed']);
    }
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid rate (must be 0-100)']);
}
?>
