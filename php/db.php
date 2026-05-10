<?php
$host = 'localhost';
$user = 'root';
$pass = ''; 
$dbname = 'bookspark';

//connect without db
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//create db
$db_created = false;
$sql = "CREATE DATABASE IF NOT EXISTS bookspark";
if ($conn->query($sql) === TRUE) {
    $db_created = true;
}
$conn->select_db($dbname);

// Create tables
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        seller_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255),
        price INT NOT NULL DEFAULT 0,
        condition_desc VARCHAR(255),
        image_path VARCHAR(255),
        description TEXT,
        status ENUM('available', 'sold') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        buyer_id INT NOT NULL,
        book_id INT NOT NULL,
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'completed') DEFAULT 'completed',
        FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $table) {
    $conn->query($table);
}

// Migrate price column from VARCHAR to INT if needed
$col_check = $conn->query("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '$dbname' AND TABLE_NAME = 'books' AND COLUMN_NAME = 'price'");
if ($col_check && $col_row = $col_check->fetch_assoc()) {
    if (strtolower($col_row['DATA_TYPE']) === 'varchar') {
        // Strip non-numeric characters using PHP (compatible with all MySQL versions)
        $rows = $conn->query("SELECT id, price FROM books");
        if ($rows) {
            while ($r = $rows->fetch_assoc()) {
                $clean = intval(preg_replace('/[^0-9]/', '', $r['price']));
                $conn->query("UPDATE books SET price = '$clean' WHERE id = " . intval($r['id']));
            }
        }
        $conn->query("ALTER TABLE books MODIFY COLUMN price INT NOT NULL DEFAULT 0");
    }
}

// Add columns to existing tables
$conn->query("ALTER TABLE books ADD COLUMN IF NOT EXISTS genre VARCHAR(100)");
$conn->query("ALTER TABLE books ADD COLUMN IF NOT EXISTS status ENUM('available', 'sold') DEFAULT 'available'");

// Add payment columns to orders table
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS commission_amount INT DEFAULT 0");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS seller_payout INT DEFAULT 0");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_method VARCHAR(50) DEFAULT 'razorpay'");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_id VARCHAR(100)");
$conn->query("ALTER TABLE orders ADD COLUMN IF NOT EXISTS payment_status VARCHAR(50) DEFAULT 'pending'");
?>
