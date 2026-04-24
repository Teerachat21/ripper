<?php
$host = 'sqlXXX.infinityfree.com'; // แก้ไขเป็น MySQL Hostname จากหน้า MySQL Databases ใน InfinityFree
$user = 'if0_41740823';           // ชื่อ Account จากรูปภาพ
$pass = 'รหัสผ่านของคุณ';           // ใส่รหัสผ่าน Hosting ของคุณที่นี่
$dbname = 'if0_41740823_XXXX';    // แก้ไขเป็นชื่อฐานข้อมูลที่คุณสร้างไว้

// Create connection
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($dbname);
} else {
    die("Error creating database: " . $conn->error);
}

// Create users table if not exists
$users_table_sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($users_table_sql) === FALSE) {
    die("Error creating users table: " . $conn->error);
}

// Check if role column exists (Migration)
$check_role = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check_role->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user' AFTER password");
}

// Create transactions table if not exists (with user_id)
$table_sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($table_sql) === FALSE) {
    die("Error creating transactions table: " . $conn->error);
}

// Check if user_id column exists (Migration for existing tables)
$check_column = $conn->query("SHOW COLUMNS FROM transactions LIKE 'user_id'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE transactions ADD COLUMN user_id INT(11) NOT NULL AFTER id");
    $conn->query("ALTER TABLE transactions ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");
}
?>
