<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

include 'db.php';

$data = [
    'totalTransactions' => 0,
    'totalSalesVolume' => 0,
    'totalCommissionEarned' => 0,
    'currentCommissionRate' => 0,
    'recentTransactions' => []
];

// Get current commission rate
$res = $conn->query("SELECT rate FROM commission_rate WHERE id = 1");
if ($row = $res->fetch_assoc()) {
    $data['currentCommissionRate'] = (float)$row['rate'];
}

// Get aggregate stats
$res = $conn->query("SELECT COUNT(*) as cnt, SUM(sale_price) as sales, SUM(commission_amount) as comm FROM transactions");
if ($row = $res->fetch_assoc()) {
    $data['totalTransactions'] = (int)$row['cnt'];
    $data['totalSalesVolume'] = (float)$row['sales'];
    $data['totalCommissionEarned'] = (float)$row['comm'];
}

// Get recent transactions
$query = "
    SELECT t.id, t.sale_price, t.commission_amount, t.transaction_date, 
           b.title as book_title, s.username as seller_name, byr.username as buyer_name
    FROM transactions t
    JOIN books b ON t.book_id = b.id
    JOIN users s ON t.seller_id = s.id
    JOIN users byr ON t.buyer_id = byr.id
    ORDER BY t.transaction_date DESC
    LIMIT 20
";
$res = $conn->query($query);
while ($row = $res->fetch_assoc()) {
    $data['recentTransactions'][] = $row;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $data]);
?>
