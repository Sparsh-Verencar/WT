<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include '../php/db.php';

$error = '';
$success = '';
$edit_mode = false;
$edit_book = null;
// Detect AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt_edit = $conn->prepare("SELECT * FROM books WHERE id = ? AND seller_id = ?");
    $stmt_edit->bind_param("ii", $edit_id, $_SESSION['user_id']);
    $stmt_edit->execute();
    $res_edit = $stmt_edit->get_result();
    if ($res_edit->num_rows > 0) {
        $edit_mode = true;
        $edit_book = $res_edit->fetch_assoc();
    }
    $stmt_edit->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_book') {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $genre = trim($_POST['genre']);
        $desc = trim($_POST['description']);
        $price = intval($_POST['price']);
        $seller_id = $_SESSION['user_id'];
        
        $image_path = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../images/books/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = '../images/books/' . $filename;
            }
        }
        
        $stmt = $conn->prepare("INSERT INTO books (seller_id, title, author, genre, description, price, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssis", $seller_id, $title, $author, $genre, $desc, $price, $image_path);
        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $success = "Book added successfully.";
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'book' => [
                    'id' => $new_id,
                    'title' => $title,
                    'author' => $author,
                    'genre' => $genre,
                    'description' => $desc,
                    'price' => $price,
                    'image_path' => $image_path
                ]]);
                exit;
            }
        } else {
            $error = "Failed to add book.";
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
        $stmt->close();
    } elseif ($_POST['action'] === 'delete_book') {
        $book_id = $_POST['book_id'] ?? 0;
        $seller_id = $_SESSION['user_id'];
        // Do not allow deleting a book that has been sold
        $check = $conn->prepare("SELECT status FROM books WHERE id = ? AND seller_id = ?");
        $check->bind_param("ii", $book_id, $seller_id);
        $check->execute();
        $res_check = $check->get_result();
        if ($res_check->num_rows === 0) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Book not found']);
                exit;
            }
        }
        $row_check = $res_check->fetch_assoc();
        if (isset($row_check['status']) && $row_check['status'] === 'sold') {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Cannot delete a sold book']);
                exit;
            } else {
                $error = 'Cannot delete a sold book';
            }
        } else {
            $stmt = $conn->prepare("DELETE FROM books WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ii", $book_id, $seller_id);
            if ($stmt->execute()) {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'book_id' => $book_id]);
                    exit;
                }
            } else {
                if ($is_ajax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to delete']);
                    exit;
                }
            }
            $stmt->close();
        }
        $check->close();
    } elseif ($_POST['action'] === 'edit_book') {
        $book_id = intval($_POST['book_id'] ?? 0);
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $genre = trim($_POST['genre']);
        $desc = trim($_POST['description']);
        $price = intval($_POST['price']);
        $seller_id = $_SESSION['user_id'];
        
        $image_path = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../images/books/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = time() . '_' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = '../images/books/' . $filename;
            }
        }
        
        if ($image_path !== null) {
            $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, genre = ?, description = ?, price = ?, image_path = ? WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ssssisii", $title, $author, $genre, $desc, $price, $image_path, $book_id, $seller_id);
        } else {
            $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, genre = ?, description = ?, price = ? WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ssssiii", $title, $author, $genre, $desc, $price, $book_id, $seller_id);
        }
        
        if ($stmt->execute()) {
            $success = "Book updated successfully.";
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'book' => [
                    'id' => $book_id,
                    'title' => $title,
                    'author' => $author,
                    'genre' => $genre,
                    'description' => $desc,
                    'price' => $price,
                    'image_path' => $image_path !== null ? $image_path : ''
                ]]);
                exit;
            }
        } else {
            $error = "Failed to update book.";
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $error]);
                exit;
            }
        }
        $stmt->close();
    }
}

// AJAX GET: return JSON list for search/filter
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $seller_id = $_SESSION['user_id'];
    if ($q !== '') {
        $like = '%' . $q . '%';
        $stmt_list = $conn->prepare("SELECT * FROM books WHERE seller_id = ? AND (title LIKE ? OR description LIKE ?) ORDER BY created_at DESC");
        $stmt_list->bind_param('iss', $seller_id, $like, $like);
    } else {
        $stmt_list = $conn->prepare("SELECT * FROM books WHERE seller_id = ? ORDER BY created_at DESC");
        $stmt_list->bind_param('i', $seller_id);
    }
    $stmt_list->execute();
    $res = $stmt_list->get_result();
    $books = [];
    while ($r = $res->fetch_assoc()) {
        $books[] = [
            'id' => $r['id'],
            'title' => $r['title'],
            'author' => isset($r['author']) ? $r['author'] : '',
            'genre' => isset($r['genre']) ? $r['genre'] : '',
            'description' => $r['description'],
            'price' => $r['price'],
            'image_path' => $r['image_path'] ?: '../images/1984.png',
            'status' => isset($r['status']) ? $r['status'] : ''
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'books' => $books]);
    exit;
}

$seller_id = $_SESSION['user_id'];
// Available (not sold) books for the main grid
$avail_stmt = $conn->prepare("SELECT * FROM books WHERE seller_id = ? AND (status IS NULL OR status != 'sold') ORDER BY created_at DESC");
$avail_stmt->bind_param("i", $seller_id);
$avail_stmt->execute();
$avail_result = $avail_stmt->get_result();

// Sold books (purchases) with buyer info
$sold_stmt = $conn->prepare("SELECT b.*, o.buyer_id, o.order_date, u.username AS buyer_name FROM books b LEFT JOIN orders o ON b.id = o.book_id LEFT JOIN users u ON o.buyer_id = u.id WHERE b.seller_id = ? AND b.status = 'sold' ORDER BY o.order_date DESC");
$sold_stmt->bind_param("i", $seller_id);
$sold_stmt->execute();
$sold_result = $sold_stmt->get_result();

$js_books_array = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Listing</title>
    <link rel="stylesheet" href="../styles/bookListing.css">
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
            <h2>Book listings</h2>
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="🔍  Search">
            </div>
        </div>
        <?php if (!empty($error)): ?>
            <p style="color: #FF006E; font-weight: 900; margin: 10px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <p style="color: green; font-weight: 900; margin: 10px;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <?php if ($sold_result && $sold_result->num_rows > 0): ?>
            <section style="margin: 20px 0;">
                <h3 style="margin:0 0 10px 0;">Purchases</h3>
                <div class="purchases-list" style="display:flex;gap:10px;flex-wrap:wrap;">
                    <?php while ($srow = $sold_result->fetch_assoc()): ?>
                        <?php $simg = !empty($srow['image_path']) ? htmlspecialchars($srow['image_path']) : '../images/1984.png'; ?>
                        <div class="purchase-card" style="border:1px solid #eee;padding:10px;border-radius:6px;width:220px;">
                            <div style="display:flex;gap:10px;align-items:center;">
                                <img src="<?= $simg ?>" alt="Book" style="width:60px;height:80px;object-fit:cover;border-radius:4px;">
                                <div>
                                    <strong><?= htmlspecialchars($srow['title']) ?></strong>
                                    <div style="font-size:13px;color:#666;">Buyer: <?= htmlspecialchars($srow['buyer_name'] ?? '—') ?></div>
                                    <div style="font-size:13px;color:#666;">&#8377;<?= htmlspecialchars($srow['price']) ?> • <?= isset($srow['order_date']) ? htmlspecialchars($srow['order_date']) : '' ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>

        <div class="book-grid">
            <div class="add-card">
                <p>+</p>
            </div>
            
            <?php 
                while ($row = $avail_result->fetch_assoc()): 
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
            <div class="book-card" data-id="<?= htmlspecialchars($row['id']) ?>">
                <div class="buttons" style="display: flex; gap: 5px;">
                    <?php if (isset($row['status']) && $row['status'] === 'sold'): ?>
                        <span style="background: red; color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 12px; display:inline-block; font-family: 'Arial Black', sans-serif;">SOLD</span>
                    <?php else: ?>
                        <a href="bookListing.php?edit=<?= htmlspecialchars($row['id']) ?>" style="background:#FF006E; color:#fff; padding:5px 10px; border-radius:5px; font-size: 13.3333px; text-decoration:none; display:inline-block;" onclick="event.stopPropagation();">Edit</a>
                    <?php endif; ?>
                    <form action="bookListing.php" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this book?');">
                        <input type="hidden" name="action" value="delete_book">
                        <input type="hidden" name="book_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <button type="submit" class="del-btn-php" style="background:#FF006E; color:#fff; border:none; padding:5px 10px; border-radius:5px; cursor:pointer; font-size: 13.3333px;" onclick="event.stopPropagation();">Delete</button>
                    </form>
                </div>
                <div class="img-placeholder"><img src="<?= $img ?>" alt="Book-img"></div>
                <p class="book-title">
                    <?= htmlspecialchars($row['title']) ?><br>
                    <?= htmlspecialchars($row['description']) ?><br>
                    &#8377;<?= htmlspecialchars($row['price']) ?>
                </p>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- ── ADD / EDIT MODAL ──────────────────── -->
    <form action="bookListing.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?= $edit_mode ? 'edit_book' : 'add_book' ?>">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="book_id" value="<?= htmlspecialchars($edit_book['id']) ?>">
        <?php endif; ?>
        <div id="modal-backdrop" class="modal-backdrop" <?= $edit_mode ? 'style="display:block;"' : '' ?>></div>
        <div id="book-modal" class="modal-box" <?= $edit_mode ? 'style="display:block;"' : '' ?>>
            <h3 id="modal-title"><?= $edit_mode ? 'Update Book' : 'Add Book' ?></h3>

            <div class="form-group">
                <label for="input-name">Book Title</label>
                <input type="text" id="input-name" name="title" placeholder="e.g. 1984" required value="<?= $edit_mode ? htmlspecialchars($edit_book['title']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="input-author">Author</label>
                <input type="text" id="input-author" name="author" placeholder="e.g. George Orwell" value="<?= $edit_mode ? htmlspecialchars($edit_book['author'] ?? '') : '' ?>">
            </div>
            <div class="form-group">
                <label for="input-genre">Genre</label>
                <input type="text" id="input-genre" name="genre" placeholder="e.g. Fiction, Mystery, Science" value="<?= $edit_mode ? htmlspecialchars($edit_book['genre'] ?? '') : '' ?>">
            </div>
            <div class="form-group">
                <label for="input-desc">Description</label>
                <textarea id="input-desc" name="description" placeholder="Short description of the book…"><?= $edit_mode ? htmlspecialchars($edit_book['description']) : '' ?></textarea>
            </div>
            <div class="form-group">
                <label for="input-price">Price (&#8377;)</label>
                <input type="number" id="input-price" name="price" placeholder="&#8377; e.g. 299" min="0" step="1" required value="<?= $edit_mode ? htmlspecialchars($edit_book['price']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="input-image">Book Image</label>
                <input type="file" id="input-image" name="image" accept="image/*">
                <?php if ($edit_mode && !empty($edit_book['image_path'])): ?>
                    <div style="margin-top: 10px;">
                        <img src="<?= htmlspecialchars($edit_book['image_path']) ?>" alt="Current Image" style="max-width: 100px; border-radius: 5px;">
                        <p style="font-size: 12px; color: #666;">Current image</p>
                    </div>
                <?php endif; ?>
                <img id="modal-img-preview" class="modal-img-preview" src="" alt="Preview">
            </div>

            <div class="modal-actions">
                <!-- Change ID of close to preserve behavior, but change ID of save so JS doesn't intercept it, or better yet, just leave it and let JS fail or bypass it -->
                <button type="button" id="modal-close-php" onclick="<?= $edit_mode ? "window.location.href='bookListing.php';" : "document.getElementById('book-modal').style.display='none'; document.getElementById('modal-backdrop').style.display='none';" ?>" class="modal-btn-cancel">Cancel</button>
                <button type="submit" id="modal-save-php" class="modal-btn-save"><?= $edit_mode ? 'Update Book' : 'Save' ?></button>
            </div>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Seed localStorage so JS functionalities that rely on it stay consistent
        var phpBooks = <?= json_encode($js_books_array) ?>;
        localStorage.setItem('books', JSON.stringify(phpBooks));

        // Suppress `remove` for .book-card, and `.after` for .add-card so JS doesn't break PHP rendering
        var ogRemove = $.fn.remove;
        $.fn.remove = function() {
            if(this.hasClass('book-card')){
                return this; // do nothing
            }
            return ogRemove.apply(this, arguments);
        };

        var ogAfter = $.fn.after;
        $.fn.after = function() {
            if(this.hasClass('add-card')){
                return this; // do nothing
            }
            return ogAfter.apply(this, arguments);
        };
    </script>
    <script src="../js/bookListing.js"></script>
</body>
</html>
