<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../php/db.php';

$books = [];
$stmt = $conn->prepare("SELECT * FROM books WHERE status = 'available' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();

// Fill array for localStorage seeding
$js_books_array = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Page</title>
    <link rel="stylesheet" href="../styles/explorepage.css">
    <link rel="stylesheet" href="../styles/modals.css">
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
            <h2>Explore</h2>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="🔍  Search">
            </div>
        </div>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'already_sold'): ?>
            <p style="color: #FF006E; font-weight: bold; padding: 10px; background: #fff; border-radius: 5px; margin-bottom: 15px; border: 2px solid #FF006E; font-family: 'Arial Black', sans-serif;">Sorry, this book was just sold. Please choose another.</p>
        <?php endif; ?>

        <div class="book-grid">
            <?php 
                if ($result->num_rows === 0): 
            ?>
                <p style="color:#FFA500;font-weight:900;font-family:'Arial Black',sans-serif;padding:1rem;">No books listed yet. Add some from the Book Listing page!</p>
            <?php 
                endif;
                while ($row = $result->fetch_assoc()): 
                    $img = !empty($row['image_path']) ? htmlspecialchars($row['image_path']) : '../images/1984.png';
                    $js_books_array[] = [
                        'id' => $row['id'],
                        'name' => $row['title'],
                        'author' => isset($row['author']) ? $row['author'] : '',
                        'genre' => isset($row['genre']) ? $row['genre'] : '',
                        'description' => $row['description'],
                        'price' => $row['price'],
                        'image' => $row['image_path']
                    ];
            ?>
            <div class="book-card" data-id="<?= htmlspecialchars($row['id']) ?>" <?php if ($row['seller_id'] == $_SESSION['user_id']) echo 'onclick="event.stopPropagation();" style="cursor:default;"'; ?>>
                <?php if ($row['seller_id'] == $_SESSION['user_id']): ?>
                    <div style="text-align: center; margin-bottom: 10px;">
                        <span style="display: inline-block; background: #ccc; color: #666; padding: 5px 10px; border-radius: 5px; font-size: 12px; cursor: not-allowed; font-family: 'Arial Black', sans-serif;">Your Listing</span>
                    </div>
                <?php endif; ?>
                <div class="img-placeholder"><img src="<?= $img ?>" alt="Book-img"></div>
                <p class="book-title">
                    <?= htmlspecialchars($row['title']) ?><br>
                    <?php if (isset($row['author']) && $row['author']): ?>
                        <small style="color:#888;">By <?= htmlspecialchars($row['author']) ?></small><br>
                    <?php endif; ?>
                    <?php if (isset($row['genre']) && $row['genre']): ?>
                        <small style="color:#888;"><?= htmlspecialchars($row['genre']) ?></small><br>
                    <?php endif; ?>
                    <?= htmlspecialchars($row['description']) ?><br>
                    &#8377;<?= htmlspecialchars($row['price']) ?>
                </p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- ── BOOK DETAIL MODAL ─────────────────── -->
    <div id="detail-backdrop" class="modal-backdrop"></div>
    <div id="detail-modal" class="modal-box">
        <h3>Book Details</h3>
        <img id="detail-img" class="detail-cover" src="" alt="Book cover">
        <div class="detail-info">
            <strong id="detail-name"></strong>
            <span id="detail-desc"></span>
            <span id="detail-price" class="detail-price"></span>
        </div>
        <div class="modal-actions">
            <button id="detail-close" class="modal-btn-cancel">Close</button>
            <form action="myorder.php" method="POST" style="display:inline;">
                <input type="hidden" name="buy_book" id="buy_book_id" value="">
                <button type="submit" id="detail-buy-php" class="modal-btn-buy">Buy</button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Seed local storage so the JS click handlers for the modal find the correct data
        var phpBooks = <?= json_encode($js_books_array) ?>;
        localStorage.setItem('books', JSON.stringify(phpBooks));

        // Let the modal form populate before submit
        $(document).on('click', '.book-card', function() {
            var id = $(this).data('id');
            $('#buy_book_id').val(id);
        });

        // Suppress default empty to preserve the PHP rendered grid
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
    <script src="../js/explorepage.js"></script>
</body>
</html>
