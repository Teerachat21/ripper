<?php
require_once 'db_config.php';

$result = $conn->query("SHOW COLUMNS FROM transactions LIKE 'user_id'");
if ($result->num_rows == 0) {
    echo "Column user_id does not exist. Adding it now...\n";
    
    // Check if users table exists first
    $conn->query("CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Add user_id column
    $sql = "ALTER TABLE transactions ADD COLUMN user_id INT(11) NOT NULL AFTER id";
    if ($conn->query($sql)) {
        echo "Column user_id added successfully.\n";
        
        // Add foreign key
        $fk_sql = "ALTER TABLE transactions ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE";
        if ($conn->query($fk_sql)) {
            echo "Foreign key added successfully.\n";
        } else {
            echo "Error adding foreign key: " . $conn->error . "\n";
        }
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column user_id already exists.\n";
}
?>
