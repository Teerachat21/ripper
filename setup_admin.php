<?php
require_once 'db_config.php';

$admin_user = 'admin';
$admin_pass = 'admin2234';
$hashed_pass = password_hash($admin_pass, PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->query("SELECT id FROM users WHERE username = '$admin_user'");

if ($check->num_rows == 0) {
    $sql = "INSERT INTO users (username, password, role) VALUES ('$admin_user', '$hashed_pass', 'admin')";
    if ($conn->query($sql)) {
        echo "<h3>สร้างบัญชี Admin เรียบร้อยแล้ว!</h3>";
        echo "<p>Username: <strong>$admin_user</strong></p>";
        echo "<p>Password: <strong>$admin_pass</strong></p>";
        echo "<br><a href='login.php'>ไปที่หน้าเข้าสู่ระบบ</a>";
    } else {
        echo "เกิดข้อผิดพลาด: " . $conn->error;
    }
} else {
    // If exists, update password and role just in case
    $sql = "UPDATE users SET password = '$hashed_pass', role = 'admin' WHERE username = '$admin_user'";
    if ($conn->query($sql)) {
        echo "<h3>อัปเดตบัญชี Admin เดิมเรียบร้อยแล้ว!</h3>";
        echo "<p>Username: <strong>$admin_user</strong></p>";
        echo "<p>Password: <strong>$admin_pass</strong></p>";
        echo "<br><a href='login.php'>ไปที่หน้าเข้าสู่ระบบ</a>";
    }
}
?>
