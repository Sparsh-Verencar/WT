<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../pages/adminlogin.php");
    exit;
}

include '../php/db.php';

// Fetch all books with seller info and order data
$books_query = $conn->prepare("
    SELECT 
        b.id,
        b.title,
        b.author,
        b.genre,
        b.price,
        b.status,
        b.created_at,
        u.username AS seller_name,
        u.email AS seller_email,
        COALESCE(o.buyer_id, 'Not Sold') AS buyer_id,
        (SELECT username FROM users WHERE id = o.buyer_id) AS buyer_name,
        o.order_date
    FROM books b
    JOIN users u ON b.seller_id = u.id
    LEFT JOIN orders o ON b.id = o.book_id
    ORDER BY b.created_at DESC
");
$books_query->execute();
$books_result = $books_query->get_result();
$books_data = [];
while ($row = $books_result->fetch_assoc()) {
    $books_data[] = $row;
}

// Calculate total earnings (sum of all sold books' prices)
$earnings_query = $conn->prepare("
    SELECT SUM(b.price) AS total_earnings, COUNT(o.id) AS total_sales
    FROM books b
    JOIN orders o ON b.id = o.book_id
");
$earnings_query->execute();
$earnings_result = $earnings_query->get_result();
$earnings_row = $earnings_result->fetch_assoc();
$total_earnings = $earnings_row['total_earnings'] ?? 0;
$total_sales = $earnings_row['total_sales'] ?? 0;

// Fetch seller statistics
$seller_stats_query = $conn->prepare("
    SELECT 
        u.id,
        u.username,
        COUNT(b.id) AS books_listed,
        SUM(CASE WHEN b.status = 'sold' THEN 1 ELSE 0 END) AS books_sold,
        SUM(CASE WHEN b.status = 'sold' THEN b.price ELSE 0 END) AS seller_earnings
    FROM users u
    LEFT JOIN books b ON u.id = b.seller_id
    GROUP BY u.id
    ORDER BY seller_earnings DESC
");
$seller_stats_query->execute();
$seller_stats_result = $seller_stats_query->get_result();
$seller_stats = [];
while ($row = $seller_stats_result->fetch_assoc()) {
    if ($row['books_listed'] > 0) {
        $seller_stats[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Book Spark</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>

    <div class="admin-container">
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <h2 style="color: #FFD700; font-family: 'Impact', sans-serif; margin-bottom: 20px;">ADMIN</h2>
            </div>
            <div class="sidebar-menu">
                <a href="#overview" class="menu-item active" onclick="showSection('overview')">Overview</a>
                <a href="#books" class="menu-item" onclick="showSection('books')">Books & Sales</a>
                <a href="#sellers" class="menu-item" onclick="showSection('sellers')">Seller Stats</a>
            </div>
            <div style="margin-top: 30px; border-top: 2px solid #FF006E; padding-top: 20px;">
                <a href="../php/logout.php" style="color: #FF006E; text-decoration: none; font-weight: bold; display: block; padding: 10px; text-align: center; border-radius: 5px; border: 2px solid #FF006E; transition: all 0.2s;">Logout</a>
            </div>
        </div>

        <div class="admin-main">
            <!-- OVERVIEW SECTION -->
            <section id="overview" class="admin-section active">
                <div class="section-header">
                    <h1>Admin Dashboard</h1>
                    <p style="color: #999; margin-top: 5px;">Platform Analytics & Earnings</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #FFD700, #FFA500);">₹</div>
                        <div class="stat-content">
                            <h3>Total Platform Earnings</h3>
                            <p class="stat-value">₹<?= number_format($total_earnings) ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #FF006E, #FB5607);">📚</div>
                        <div class="stat-content">
                            <h3>Books Sold</h3>
                            <p class="stat-value"><?= $total_sales ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #1a1a2e, #16213e);">👥</div>
                        <div class="stat-content">
                            <h3>Active Sellers</h3>
                            <p class="stat-value"><?= count($seller_stats) ?></p>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon" style="background: linear-gradient(135deg, #0f3460, #0d2340);">📊</div>
                        <div class="stat-content">
                            <h3>Total Listings</h3>
                            <p class="stat-value"><?= count($books_data) ?></p>
                        </div>
                    </div>
                </div>

                <div class="summary-box">
                    <h2 style="color: #FFD700; margin-bottom: 15px;">Quick Summary</h2>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 10px 0; border-bottom: 1px solid #333; display: flex; justify-content: space-between;">
                            <span>Platform Commission (if applicable)</span>
                            <strong>₹<?= number_format(intval($total_earnings * 0.1)) ?></strong>
                        </li>
                        <li style="padding: 10px 0; border-bottom: 1px solid #333; display: flex; justify-content: space-between;">
                            <span>Average Book Price</span>
                            <strong>₹<?= $total_sales > 0 ? number_format(intval($total_earnings / $total_sales)) : '0' ?></strong>
                        </li>
                        <li style="padding: 10px 0; display: flex; justify-content: space-between;">
                            <span>Books Still Available</span>
                            <strong><?= count(array_filter($books_data, function($b) { return $b['status'] !== 'sold'; })) ?></strong>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- BOOKS & SALES SECTION -->
            <section id="books" class="admin-section">
                <div class="section-header">
                    <h1>Books & Sales Details</h1>
                    <p style="color: #999; margin-top: 5px;">Complete transaction history</p>
                </div>

                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Genre</th>
                                <th>Price</th>
                                <th>Seller</th>
                                <th>Status</th>
                                <th>Buyer</th>
                                <th>Sale Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books_data as $book): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($book['author'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($book['genre'] ?? '—') ?></td>
                                    <td style="color: #FFD700; font-weight: bold;">₹<?= number_format($book['price']) ?></td>
                                    <td>
                                        <div style="font-size: 0.9rem;">
                                            <strong><?= htmlspecialchars($book['seller_name']) ?></strong><br>
                                            <small style="color: #999;"><?= htmlspecialchars($book['seller_email']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($book['status'] === 'sold'): ?>
                                            <span class="badge badge-sold">SOLD</span>
                                        <?php else: ?>
                                            <span class="badge badge-available">AVAILABLE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($book['buyer_id'] !== 'Not Sold' && !is_null($book['buyer_name'])): ?>
                                            <strong><?= htmlspecialchars($book['buyer_name']) ?></strong>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($book['order_date']): ?>
                                            <?= date('d M Y', strtotime($book['order_date'])) ?>
                                        <?php else: ?>
                                            <span style="color: #999;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- SELLER STATS SECTION -->
            <section id="sellers" class="admin-section">
                <div class="section-header">
                    <h1>Seller Performance</h1>
                    <p style="color: #999; margin-top: 5px;">Revenue and activity by seller</p>
                </div>

                <div class="seller-cards">
                    <?php foreach ($seller_stats as $seller): ?>
                        <div class="seller-card">
                            <div class="seller-header">
                                <h3><?= htmlspecialchars($seller['username']) ?></h3>
                                <span class="seller-rank">
                                    <?php
                                    $rank = array_search($seller['id'], array_column($seller_stats, 'id')) + 1;
                                    if ($rank === 1) {
                                        echo '🥇 Top Seller';
                                    } elseif ($rank === 2) {
                                        echo '🥈 Second';
                                    } elseif ($rank === 3) {
                                        echo '🥉 Third';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="seller-stats">
                                <div class="stat-row">
                                    <span>Books Listed</span>
                                    <strong><?= $seller['books_listed'] ?></strong>
                                </div>
                                <div class="stat-row">
                                    <span>Books Sold</span>
                                    <strong style="color: #FFD700;"><?= $seller['books_sold'] ?></strong>
                                </div>
                                <div class="stat-row" style="border-top: 1px solid #333; padding-top: 10px; margin-top: 10px;">
                                    <span>Earnings</span>
                                    <strong style="color: #FF006E; font-size: 1.2rem;">₹<?= number_format($seller['seller_earnings'] ?? 0) ?></strong>
                                </div>
                                <div class="stat-row">
                                    <span>Conversion Rate</span>
                                    <strong>
                                        <?php 
                                        $rate = $seller['books_listed'] > 0 ? intval(($seller['books_sold'] / $seller['books_listed']) * 100) : 0;
                                        echo $rate . '%';
                                        ?>
                                    </strong>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($seller_stats)): ?>
                    <p style="text-align: center; color: #999; padding: 40px;">No seller data available yet.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.admin-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Update menu active state
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
        }
    </script>

</body>
</html>
