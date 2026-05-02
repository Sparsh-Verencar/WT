<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../php/db.php';
$user_id = $_SESSION['user_id'];
$username_val = $_SESSION['username'];
$email_val = '';

// Fetch Email
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $email_val = $row['email'];
}
$stmt->close();

// Fetch listed books by this user
$stmt_b = $conn->prepare("SELECT id, title, price, status FROM books WHERE seller_id = ? ORDER BY created_at DESC");
$stmt_b->bind_param("i", $user_id);
$stmt_b->execute();
$books_res = $stmt_b->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="../styles/myaccount.css">
    <link rel="stylesheet" href="../styles/modals.css">
</head>
<body>
    <div id="container">
        <div id="sidebar">
            <div class="nav-links">
                <a class="nav-item" href="explorepage.php">Explore page</a>
                <a class="nav-item" href="myorder.php">My Orders</a>
                <a class="nav-item" href="bookListing.php">Book listing</a>
            </div>
            <div class="nav-item">My account</div>
        </div>

        <div id="content" style="overflow-y: auto;">
            <div id="account-card">
                <button id="edit-btn">edit</button>
                <div id="profile-pic">
                    <img src="../images/Profile.jpg" alt="Profile" width="150" height="150">
                </div>
                <div id="details">
                    <h1>Username</h1>
                    <h3 id="username"><?= htmlspecialchars($username_val) ?></h3>
                    <h2>Email</h2>
                    <h3><?= htmlspecialchars($email_val) ?></h3>
                    <div id="actions">
                        <button onclick="window.location.href='../php/logout.php'">Logout</button>
                        <form action="../php/deleteAccount.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete your account? This will also remove all your listed books and cannot be undone.');">
                             <button type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Display the listed books -->
            <div id="user-books" style="margin-top: 30px; background: #fff; padding: 20px; border-radius: 10px; border: 3px solid #1a1a2e; box-shadow: 0 4px 0 #FF006E; color: #1a1a2e; font-family: 'Arial Black', sans-serif;">
                <h2 style="margin-bottom: 15px; color: #FB5607; text-transform: uppercase;">My Listed Books</h2>
                <?php if ($books_res->num_rows === 0): ?>
                    <p style="font-size: 0.9rem;">You haven't listed any books yet.</p>
                <?php else: ?>
                    <ul style="list-style: none; padding-left: 0;">
                        <?php while ($b = $books_res->fetch_assoc()): ?>
                            <li style="border-bottom: 2px solid rgba(0,0,0,0.1); padding: 10px 0; display: flex; justify-content: space-between;">
                                <span><?= htmlspecialchars($b['title']) ?></span>
                                <div>
                                    <span style="color: #FF006E; margin-right: 10px;"><?= htmlspecialchars($b['price']) ?></span>
                                    <?php if (isset($b['status']) && $b['status'] === 'sold'): ?>
                                        <span style="background: red; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; font-family: 'Arial Black', sans-serif;">SOLD</span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- ── EDIT PROFILE MODAL ─────────────────── -->
    <div id="profile-backdrop" class="modal-backdrop"></div>
    <div id="profile-modal" class="modal-box">
        <h3>Edit Profile</h3>

        <div class="form-group">
            <label for="edit-username">Username</label>
            <input type="text" id="edit-username" placeholder="Your name">
        </div>
        <div class="form-group">
            <label for="edit-image">Profile Image</label>
            <input type="file" id="edit-image" accept="image/*">
            <img id="edit-img-preview" class="modal-img-preview" src="" alt="Preview">
        </div>

        <div class="modal-actions">
            <button id="profile-modal-close" class="modal-btn-cancel">Cancel</button>
            <button id="profile-save"        class="modal-btn-save">Save</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/myaccount.js"></script>
</body>
</html>
