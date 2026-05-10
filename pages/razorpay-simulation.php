<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../php/db.php';

$user_id = $_SESSION['user_id'];
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;
$amount = isset($_GET['amount']) ? intval($_GET['amount']) : 0;
$commission = isset($_GET['commission']) ? intval($_GET['commission']) : 0;
$seller_payout = isset($_GET['seller_payout']) ? intval($_GET['seller_payout']) : 0;

// Validate parameters
if ($book_id <= 0 || $amount <= 0) {
    header("Location: explorepage.php");
    exit;
}

// Fetch book details
$stmt = $conn->prepare("SELECT b.*, u.username AS seller_name FROM books b JOIN users u ON b.seller_id = u.id WHERE b.id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
$book = $result->fetch_assoc();
$stmt->close();

if (!$book) {
    header("Location: explorepage.php");
    exit;
}

$payment_error = '';
$payment_success = false;
$payment_id = '';

// Process payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    $card_number = preg_replace('/\s/', '', $_POST['card_number'] ?? '');
    $card_expiry = trim($_POST['card_expiry'] ?? '');
    $card_cvv = trim($_POST['card_cvv'] ?? '');
    $card_name = trim($_POST['card_name'] ?? '');

    // Validation
    $errors = [];
    if (strlen($card_number) < 13 || strlen($card_number) > 19 || !preg_match('/^\d+$/', $card_number)) {
        $errors[] = 'Invalid card number';
    }
    if (!preg_match('/^\d{2}\/\d{2}$/', $card_expiry)) {
        $errors[] = 'Invalid expiry date (use MM/YY)';
    }
    if (strlen($card_cvv) < 3 || strlen($card_cvv) > 4 || !preg_match('/^\d+$/', $card_cvv)) {
        $errors[] = 'Invalid CVV';
    }
    if (empty($card_name)) {
        $errors[] = 'Please enter cardholder name';
    }

    if (empty($errors)) {
        // Re-check book is available
        $check = $conn->prepare("SELECT status FROM books WHERE id = ?");
        $check->bind_param("i", $book_id);
        $check->execute();
        $check_res = $check->get_result();
        $check_row = $check_res->fetch_assoc();
        $check->close();

        if (!$check_row || $check_row['status'] === 'sold') {
            $payment_error = 'This book has already been sold.';
        } else {
            // Generate payment ID
            $payment_id = 'pay_' . time() . '_' . substr(md5(mt_rand()), 0, 8);

            // Mark book as sold
            $upd = $conn->prepare("UPDATE books SET status = 'sold' WHERE id = ?");
            $upd->bind_param("i", $book_id);
            $upd->execute();
            $upd->close();

            // Check for duplicate order
            $dup = $conn->prepare("SELECT id FROM orders WHERE buyer_id = ? AND book_id = ?");
            $dup->bind_param("ii", $user_id, $book_id);
            $dup->execute();
            $dup_res = $dup->get_result();

            if ($dup_res->num_rows === 0) {
                // Create order with commission data
                $ins = $conn->prepare("INSERT INTO orders (buyer_id, book_id, status, commission_amount, seller_payout, payment_method, payment_id, payment_status) VALUES (?, ?, 'completed', ?, ?, 'razorpay', ?, 'completed')");
                $ins->bind_param("iiiiis", $user_id, $book_id, $commission, $seller_payout, $payment_id);
                $ins->execute();
                $ins->close();
            }
            $dup->close();

            $payment_success = true;
        }
    } else {
        $payment_error = implode(', ', $errors);
    }
}

$img = !empty($book['image_path']) ? htmlspecialchars($book['image_path']) : '../images/1984.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay - Payment Gateway</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #3395FF 0%, #2563eb 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            font-weight: 900;
            letter-spacing: 1px;
        }

        .header p {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .content {
            padding: 30px;
        }

        .book-summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .book-summary img {
            width: 70px;
            height: 90px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
        }

        .book-info h3 {
            font-size: 1rem;
            color: #1a1a2e;
            margin-bottom: 5px;
        }

        .book-info p {
            font-size: 0.85rem;
            color: #666;
            margin: 3px 0;
        }

        .price-section {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #3395FF;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .price-row.total {
            font-weight: 900;
            font-size: 1.1rem;
            color: #3395FF;
            border-top: 2px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }

        .error-message {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .error-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔵 Razorpay</h1>
            <p>Secure Payment Gateway</p>
        </div>

        <div class="content">
            <!-- Success Screen -->
            <div <?= $payment_success ? 'style="display:block;"' : 'style="display:none;"' ?>>
                <div style="text-align: center; padding: 40px 30px;">
                    <div style="font-size: 3rem; margin-bottom: 20px;">✅</div>
                    <div style="font-size: 1.5rem; color: #28a745; font-weight: 900; margin-bottom: 10px;">Payment Successful!</div>
                    <div style="color: #666; margin-bottom: 20px; line-height: 1.6;">
                        Your payment has been processed successfully.<br>
                        The book has been added to your orders.
                    </div>
                    <div style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0; font-family: monospace; font-size: 0.9rem; word-break: break-all; border-left: 4px solid #28a745;">
                        Payment ID: <?= htmlspecialchars($payment_id) ?>
                    </div>
                    <div style="color: #666; font-size: 0.9rem; margin-top: 15px;">
                        Redirecting to your orders in 3 seconds...<br>
                        <a href="myorder.php" style="color: #3395FF; text-decoration: none; font-weight: 600;">Click here if not redirected</a>
                    </div>
                </div>
            </div>

            <!-- Payment Form -->
            <div <?= $payment_success ? 'style="display:none;"' : '' ?>>
                <!-- Book Summary -->
                <div class="book-summary">
                    <img src="<?= $img ?>" alt="Book">
                    <div class="book-info">
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <p><strong>Author:</strong> <?= htmlspecialchars($book['author'] ?? 'Unknown') ?></p>
                        <p><strong>Seller:</strong> <?= htmlspecialchars($book['seller_name']) ?></p>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="price-section">
                    <div class="price-row">
                        <span>Book Price:</span>
                        <span>₹<?= number_format($amount) ?></span>
                    </div>
                    <div class="price-row">
                        <span>Platform Fee (5%):</span>
                        <span>₹<?= number_format($commission) ?></span>
                    </div>
                    <div class="price-row total">
                        <span>Total Amount:</span>
                        <span>₹<?= number_format($amount) ?></span>
                    </div>
                </div>

                <!-- Error Message -->
                <?php if (!empty($payment_error)): ?>
                    <div class="error-message show"><?= htmlspecialchars($payment_error) ?></div>
                <?php endif; ?>

                <!-- Payment Form -->
                <form method="POST" id="paymentForm">
                    <input type="hidden" name="action" value="pay">

                    <div class="form-group">
                        <label for="card_name">Cardholder Name</label>
                        <input type="text" id="card_name" name="card_name" placeholder="John Doe" required>
                    </div>

                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="4111 1111 1111 1111" maxlength="19" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label for="card_expiry">Expiry (MM/YY)</label>
                            <input type="text" id="card_expiry" name="card_expiry" placeholder="12/28" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 25px;">
                        <button type="button" style="flex: 1; padding: 12px; border: none; border-radius: 6px; font-size: 0.95rem; font-weight: 700; cursor: pointer; background: #f0f0f0; color: #333;" onclick="window.history.back()">← Back</button>
                        <button type="submit" style="flex: 1; padding: 12px; border: none; border-radius: 6px; font-size: 0.95rem; font-weight: 700; cursor: pointer; background: linear-gradient(135deg, #3395FF 0%, #2563eb 100%); color: white; transition: all 0.3s;">Pay ₹<?= number_format($amount) ?></button>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px; color: #666; font-size: 0.85rem; margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        🔒 SSL Secured • PCI Compliant
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Format card number
        document.getElementById('card_number').addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            e.target.value = v.replace(/(\d{4})/g, '$1 ').trim();
        });

        // Auto-redirect after success
        <?php if ($payment_success): ?>
            setTimeout(function() {
                window.location.href = 'myorder.php';
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
