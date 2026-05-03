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
        price VARCHAR(50) NOT NULL,
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

// Add columns to existing tables
$conn->query("ALTER TABLE books ADD COLUMN IF NOT EXISTS status ENUM('available', 'sold') DEFAULT 'available'");
?>
