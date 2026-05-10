<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../php/db.php';

$user_id = $_SESSION['user_id'];

// Handle new orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_book'])) {
    $book_id = intval($_POST['buy_book']);
    
    // Check book status first
    $stmt_status = $conn->prepare("SELECT status FROM books WHERE id = ?");
    $stmt_status->bind_param("i", $book_id);
    $stmt_status->execute();
    $res_status = $stmt_status->get_result();
    if ($row_status = $res_status->fetch_assoc()) {
        if ($row_status['status'] === 'sold') {
            $stmt_status->close();
            header("Location: explorepage.php?error=already_sold");
            exit;
        }
    } else {
        $stmt_status->close();
        header("Location: explorepage.php");
        exit;
    }
    $stmt_status->close();
    
    // Mark the book as sold
    $stmt_upd = $conn->prepare("UPDATE books SET status = 'sold' WHERE id = ?");
    $stmt_upd->bind_param("i", $book_id);
    $stmt_upd->execute();
    $stmt_upd->close();

    // Check if not already ordered to prevent duplicates maybe
    $stmt_check = $conn->prepare("SELECT id FROM orders WHERE buyer_id = ? AND book_id = ?");
    $stmt_check->bind_param("ii", $user_id, $book_id);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows === 0) {
        $stmt_check->close();
        
        $stmt_ins = $conn->prepare("INSERT INTO orders (buyer_id, book_id, status) VALUES (?, ?, 'completed')");
        $stmt_ins->bind_param("ii", $user_id, $book_id);
        $stmt_ins->execute();
        $stmt_ins->close();

        // Transaction logging for Admin
        $stmt_b = $conn->prepare("SELECT seller_id, price FROM books WHERE id = ?");
        $stmt_b->bind_param("i", $book_id);
        $stmt_b->execute();
        $res_b = $stmt_b->get_result();
        if ($row_b = $res_b->fetch_assoc()) {
            $seller_id = $row_b['seller_id'];
            $raw_price = str_replace('₹', '', $row_b['price']);
            $sale_price = (float)$raw_price;
            
            $stmt_r = $conn->prepare("SELECT rate FROM commission_rate WHERE id = 1");
            $stmt_r->execute();
            $res_r = $stmt_r->get_result();
            $rate = 10.00;
            if ($row_r = $res_r->fetch_assoc()) {
                $rate = (float)$row_r['rate'];
            }
            $stmt_r->close();
            
            $commission_amount = $sale_price * ($rate / 100);
            
            $stmt_tx = $conn->prepare("INSERT INTO transactions (seller_id, buyer_id, book_id, sale_price, commission_amount) VALUES (?, ?, ?, ?, ?)");
            $stmt_tx->bind_param("iiidd", $seller_id, $user_id, $book_id, $sale_price, $commission_amount);
            $stmt_tx->execute();
            $stmt_tx->close();
        }
        $stmt_b->close();
    } else {
        $stmt_check->close();
    }
}

// Fetch all orders
$orders = [];
$stmt = $conn->prepare("
    SELECT o.status, o.order_date, b.id as book_id, b.title, b.author, b.description, b.price, b.image_path 
    FROM orders o 
    JOIN books b ON o.book_id = b.id 
    WHERE o.buyer_id = ? 
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="../styles/myorder.css">
</head>
<body>

    <div class="sidebar">
        <div class="sidebar-links">
            <a href="explorepage.php">Explore page</a>
            <a href="myorder.php">My Orders</a>
            <a href="bookListing.php">Book listing</a>
        </div>
        <a href="myaccountpage.php">My account</a>
    </div>

    <div class="main">
        <div class="top-bar">
            <h2>My orders</h2>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="🔍  Search">
            </div>
        </div>

        <div class="book-grid">
            <?php if ($result->num_rows === 0): ?>
                <p style="color:#FFA500;font-weight:900;font-family:'Arial Black',sans-serif;padding:1rem;">No orders yet. Buy books from the Explore page!</p>
            <?php 
                endif;
                while ($row = $result->fetch_assoc()): 
                    $img = !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : '../images/1984.png';
            ?>
            <div class="book-card" data-id="<?= htmlspecialchars($row['book_id']) ?>">
                <div class="img-placeholder"><img src="<?= $img ?>" alt="Book-img"></div>
                <p class="book-title" style="position: relative;">
                    <strong><?= htmlspecialchars($row['title']) ?></strong>
                    <?php if (!empty($row['author'])): ?>
                        <br>by <?= htmlspecialchars($row['author']) ?>
                    <?php endif; ?>
                    <br><?= htmlspecialchars($row['price']) ?>
                    <br><span style="font-size: 0.8rem; color: #888;">Ordered: <?= date('d M Y', strtotime($row['order_date'])) ?></span>
                    <br><span style="background: green; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; margin-top: 5px; display: inline-block;">PURCHASED</span>
                </p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        var ogEmpty = $.fn.empty;
        $.fn.empty = function() {
            if(this[0] && this[0].classList && this[0].classList.contains('book-grid')){
                return this; // do nothing
            }
            return ogEmpty.apply(this, arguments);
        };
        var ogAppend = $.fn.append;
        $.fn.append = function() {
            if(this[0] && this[0].classList && this[0].classList.contains('book-grid')){
                return this; // do nothing
            }
            return ogAppend.apply(this, arguments);
        };
    </script>
    <script src="../js/myorder.js"></script>
</body>
</html>
