<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../php/db.php';

$user_id = $_SESSION['user_id'];
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : (isset($_POST['book_id']) ? intval($_POST['book_id']) : 0);

if ($book_id <= 0) {
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

if (!$book || $book['status'] === 'sold') {
    header("Location: explorepage.php?error=already_sold");
    exit;
}

// Don't allow buying your own book
if ($book['seller_id'] == $user_id) {
    header("Location: explorepage.php");
    exit;
}

// Calculate pricing
$book_price = intval($book['price']);
$commission_rate = 0.05;
$commission_amount = intval(round($book_price * $commission_rate));
$seller_payout = $book_price - $commission_amount;

$payment_error = '';
$payment_success = false;
$payment_id = '';

// Calculate pricing
$book_price = intval($book['price']);
$commission_rate = 0.05;
$commission_amount = intval(round($book_price * $commission_rate));
$seller_payout = $book_price - $commission_amount;

$img = !empty($book['image_path']) ? htmlspecialchars($book['image_path']) : '../images/1984.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Book Spark</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        /* Scanline overlay to match app aesthetic */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255,255,255,0.025) 2px, rgba(255,255,255,0.025) 4px);
            pointer-events: none;
            z-index: 999;
        }

        .checkout-container {
            background: #16213e;
            border: 4px solid #FF006E;
            border-radius: 15px;
            box-shadow: 6px 6px 0 #FB5607, 12px 12px 0 #1a1a2e, 0 24px 60px rgba(0,0,0,0.5);
            max-width: 520px;
            width: 100%;
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        .checkout-header {
            background: linear-gradient(135deg, #FF006E 0%, #FB5607 100%);
            color: #FFFF00;
            padding: 24px 30px;
            text-align: center;
            border-bottom: 4px solid #1a1a2e;
        }

        .checkout-header h1 {
            font-size: 1.6rem;
            font-family: 'Impact', 'Arial Black', sans-serif;
            text-transform: uppercase;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-shadow: 2px 2px 0 rgba(0,0,0,0.3);
        }

        .rzp-badge {
            background: #3395FF;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.3rem;
            color: white;
            border: 3px solid #1a1a2e;
            box-shadow: 3px 3px 0 #1a1a2e;
        }

        .checkout-header p {
            opacity: 0.9;
            margin-top: 4px;
            font-size: 0.85rem;
            font-family: 'Arial Black', sans-serif;
        }

        .checkout-body {
            padding: 28px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #FFD700;
            text-decoration: none;
            font-weight: 900;
            font-family: 'Arial Black', sans-serif;
            font-size: 0.85rem;
            text-transform: uppercase;
            transition: color 0.2s;
        }
        .back-link:hover { color: #FF006E; }

        /* Book summary card */
        .book-summary {
            background: #0f172a;
            border: 3px solid #FFD700;
            border-radius: 10px;
            padding: 16px;
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 4px 4px 0 #FF006E;
        }

        .book-summary img {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #FFD700;
        }

        .book-summary-info h3 {
            color: #FFD700;
            font-family: 'Arial Black', sans-serif;
            font-size: 0.95rem;
            text-transform: uppercase;
        }

        .book-summary-info p {
            color: #FFA500;
            font-size: 0.8rem;
            margin-top: 2px;
        }

        /* Price breakdown */
        .price-breakdown {
            background: #0f172a;
            border: 3px solid #1a1a2e;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .price-breakdown h4 {
            color: #FFD700;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #1a1a2e;
            color: #ccc;
            font-size: 0.9rem;
        }

        .price-row:last-child { border-bottom: none; }

        .price-row.total {
            color: #FFD700;
            font-weight: 900;
            font-size: 1.1rem;
            font-family: 'Arial Black', sans-serif;
            border-top: 2px solid #FF006E;
            margin-top: 6px;
            padding-top: 12px;
        }

        .price-row .amount { font-weight: bold; }
        .price-row.commission .amount { color: #FFA500; }
        .price-row.total .amount { color: #FFD700; }

        /* Form styles */
        .form-section-title {
            color: #FFD700;
            font-family: 'Impact', 'Arial Black', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1rem;
            margin-bottom: 14px;
            text-shadow: 1px 1px 0 #FF006E;
        }

        .form-group {
            margin-bottom: 14px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #FFA500;
            font-weight: 900;
            font-size: 0.75rem;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border-radius: 7px;
            border: 3px solid #1a1a2e;
            background: #0f172a;
            color: #FFFF00;
            font-size: 0.95rem;
            font-family: 'Comic Sans MS', sans-serif;
            font-weight: 700;
            outline: none;
            box-shadow: 0 3px 0 #FF006E;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-group input:focus {
            border-color: #FFD700;
            box-shadow: 0 3px 0 #FFD700, 0 6px 16px rgba(255,215,0,0.15);
        }

        .form-group input::placeholder {
            color: #555;
            font-weight: 400;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 22px;
        }

        .btn {
            flex: 1;
            padding: 13px 18px;
            border: 3px solid #1a1a2e;
            border-radius: 7px;
            font-size: 0.85rem;
            font-weight: 900;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-family: 'Arial Black', sans-serif;
            transition: all 0.15s ease;
            box-shadow: 0 4px 0 #1a1a2e;
        }

        .btn-cancel {
            background: transparent;
            color: #FF006E;
            border-color: #FF006E;
        }
        .btn-cancel:hover {
            background: linear-gradient(135deg, #FF006E, #c9003e);
            color: #FFFF00;
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #1a1a2e, 0 10px 18px rgba(255,0,110,0.3);
        }

        .btn-pay {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #1a1a2e;
        }
        .btn-pay:hover {
            background: linear-gradient(135deg, #FFA500, #FFD700);
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #1a1a2e, 0 10px 18px rgba(255,215,0,0.3);
        }

        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #666;
            font-size: 0.8rem;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid #1a1a2e;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Success state */
        .success-box {
            text-align: center;
            padding: 30px 20px;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4CAF50, #66BB6A);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
            border: 4px solid #1a1a2e;
            box-shadow: 4px 4px 0 #FF006E;
            animation: popIn 0.5s ease;
        }

        @keyframes popIn {
            0% { transform: scale(0); opacity: 0; }
            60% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        .success-box h2 {
            color: #4CAF50;
            font-family: 'Impact', 'Arial Black', sans-serif;
            text-transform: uppercase;
            font-size: 1.5rem;
            margin-bottom: 12px;
            text-shadow: 2px 2px 0 rgba(0,0,0,0.3);
        }

        .success-box p {
            color: #ccc;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .success-box .payment-id {
            background: #0f172a;
            border: 2px solid #4CAF50;
            border-radius: 6px;
            padding: 10px 16px;
            display: inline-block;
            color: #4CAF50;
            font-family: monospace;
            font-size: 0.85rem;
            font-weight: bold;
            margin: 12px 0;
        }

        .success-box .btn-orders {
            display: inline-block;
            margin-top: 16px;
            padding: 12px 28px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #1a1a2e;
            text-decoration: none;
            border-radius: 7px;
            border: 3px solid #1a1a2e;
            font-weight: 900;
            font-family: 'Arial Black', sans-serif;
            text-transform: uppercase;
            font-size: 0.85rem;
            box-shadow: 0 4px 0 #1a1a2e;
            transition: all 0.15s ease;
        }
        .success-box .btn-orders:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0 #1a1a2e, 0 10px 18px rgba(255,215,0,0.3);
        }

        /* Error message */
        .error-msg {
            background: rgba(255, 0, 110, 0.15);
            border: 2px solid #FF006E;
            color: #FF006E;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 900;
            font-family: 'Arial Black', sans-serif;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>
                <div class="rzp-badge">₹</div>
                Razorpay Checkout
            </h1>
            <p>Secure Mock Payment</p>
        </div>

        <div class="checkout-body">
            <?php if ($payment_success): ?>
                <!-- SUCCESS STATE -->
                <div class="success-box">
                    <div class="success-icon">✓</div>
                    <h2>Payment Successful!</h2>
                    <p>Your order for <strong style="color: #FFD700;"><?= htmlspecialchars($book['title']) ?></strong> has been placed.</p>
                    <p>Amount paid: <strong style="color: #FFD700;">₹<?= number_format($book_price) ?></strong></p>
                    <div class="payment-id"><?= htmlspecialchars($payment_id) ?></div>
                    <p style="font-size: 0.8rem; color: #888;">Commission (5%): ₹<?= number_format($commission_amount) ?> • Seller receives: ₹<?= number_format($seller_payout) ?></p>
                    <a href="myorder.php" class="btn-orders">📦 View My Orders</a>
                </div>
            <?php else: ?>
                <!-- PAYMENT FORM -->
                <a href="explorepage.php" class="back-link">← Back to Explore</a>

                <?php if ($payment_error): ?>
                    <div class="error-msg">✗ <?= htmlspecialchars($payment_error) ?></div>
                <?php endif; ?>

                <!-- Book summary -->
                <div class="book-summary">
                    <img src="<?= $img ?>" alt="Book cover">
                    <div class="book-summary-info">
                        <h3><?= htmlspecialchars($book['title']) ?></h3>
                        <?php if (!empty($book['author'])): ?>
                            <p>By <?= htmlspecialchars($book['author']) ?></p>
                        <?php endif; ?>
                        <p>Seller: <?= htmlspecialchars($book['seller_name']) ?></p>
                    </div>
                </div>

                <!-- Price breakdown -->
                <div class="price-breakdown">
                    <h4>Price Breakdown</h4>
                    <div class="price-row">
                        <span>Book Price</span>
                        <span class="amount">₹<?= number_format($book_price) ?></span>
                    </div>
                    <div class="price-row commission">
                        <span>Platform Fee (5%)</span>
                        <span class="amount">₹<?= number_format($commission_amount) ?></span>
                    </div>
                    <div class="price-row">
                        <span>Seller Receives (95%)</span>
                        <span class="amount" style="color: #4CAF50;">₹<?= number_format($seller_payout) ?></span>
                    </div>
                    <div class="price-row total">
                        <span>Total Payable</span>
                        <span class="amount">₹<?= number_format($book_price) ?></span>
                    </div>
                </div>

                <form method="GET" action="razorpay-simulation.php" id="paymentForm">
                    <input type="hidden" name="book_id" value="<?= $book_id ?>">
                    <input type="hidden" name="amount" value="<?= $book_price ?>">
                    <input type="hidden" name="commission" value="<?= $commission_amount ?>">
                    <input type="hidden" name="seller_payout" value="<?= $seller_payout ?>">

                    <h3 class="form-section-title">💳 Card Details</h3>

                    <div class="form-group">
                        <label for="card_name">Cardholder Name</label>
                        <input type="text" id="card_name" name="card_name" placeholder="e.g. John Doe" required>
                    </div>

                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" placeholder="4111 1111 1111 1111" maxlength="19" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="card_expiry">Expiry (MM/YY)</label>
                            <input type="text" id="card_expiry" name="card_expiry" placeholder="12/28" maxlength="5" required>
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="text" id="card_cvv" name="card_cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>

                    <div class="btn-group">
                        <a href="explorepage.php" class="btn btn-cancel" style="text-decoration:none;text-align:center;">Cancel</a>
                        <button type="submit" class="btn btn-pay">💳 Proceed to Payment</button>
                    </div>

                    <div class="security-badge">🔒 Redirecting to Razorpay • SSL Encrypted</div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Format card number with spaces
        var cardEl = document.getElementById('card_number');
        if (cardEl) {
            cardEl.addEventListener('input', function(e) {
                var v = e.target.value.replace(/\D/g, '');
                e.target.value = v.replace(/(\d{4})/g, '$1 ').trim();
            });
        }

        // Format expiry
        var expEl = document.getElementById('card_expiry');
        if (expEl) {
            expEl.addEventListener('input', function(e) {
                var v = e.target.value.replace(/\D/g, '');
                if (v.length >= 2) v = v.slice(0,2) + '/' + v.slice(2,4);
                e.target.value = v;
            });
        }

        // CVV numbers only
        var cvvEl = document.getElementById('card_cvv');
        if (cvvEl) {
            cvvEl.addEventListener('input', function(e) {
                e.target.value = e.target.value.replace(/\D/g, '');
            });
        }
    </script>
</body>
</html>
