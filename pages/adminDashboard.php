<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: adminLogin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/adminDashboard.css">
    <link rel="stylesheet" href="../styles/myaccount.css"> <!-- For sidebar and layout structure -->
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="sidebar">
            <div class="top-bar" style="margin-bottom: 20px; text-align: center;">
                <h2 style="color: #1a1a2e; font-family: 'Arial Black', sans-serif; text-transform: uppercase;">Admin Dashboard</h2>
            </div>
            <div class="nav-links">
                <!-- Admins don't have user links, just dashboard -->
            </div>
            <!-- <div class="nav-item">Dashboard</div> -->
            <div class="nav-links">
                <a class="nav-item" href="../php/adminLogout.php">Logout</a>
            </div>
        </aside>

        <main class="admin-main">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Transactions</h3>
                    <p id="stat-transactions">0</p>
                </div>
                <div class="stat-card">
                    <h3>Total Sales Volume</h3>
                    <p id="stat-sales">₹0.00</p>
                </div>
                <div class="stat-card">
                    <h3>Total Commission Earned</h3>
                    <p id="stat-commission">₹0.00</p>
                </div>
                <div class="stat-card">
                    <h3>Current Commission Rate</h3>
                    <p id="stat-rate">0%</p>
                </div>
            </div>

            <div class="commission-update-bar admin-panel">
                <label for="rate-input">Update Commission Rate</label>
                <form id="rate-form" style="display:flex; flex:1; gap:10px; align-items:center; width: 100%;">
                    <input type="number" id="rate-input" name="rate" step="0.01" min="0" max="100" required placeholder="Rate %">
                    <button type="submit" class="admin-btn">Update</button>
                    <span id="rate-message" style="color: green; font-weight: bold; display: none;">Updated!</span>
                </form>
            </div>

            <div class="transactions-section admin-panel">
                <h2>Recent Transactions</h2>
                <div class="table-wrapper">
                    <table id="transactions-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Seller</th>
                                <th>Buyer</th>
                                <th>Book Title</th>
                                <th>Sale Price</th>
                                <th>Commission</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated by AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/adminDashboard.js"></script>
    <script src="../js/ajax/adminDashboard-ajax.js"></script>
</body>
</html>
