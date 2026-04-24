-- 1. สร้างฐานข้อมูล
CREATE DATABASE IF NOT EXISTS income_expense CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE income_expense;

-- 2. สร้างตารางผู้ใช้งาน (users)
CREATE TABLE IF NOT EXISTS users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. สร้างตารางรายการรายรับ-รายจ่าย (transactions)
CREATE TABLE IF NOT EXISTS transactions (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) NOT NULL,
    title VARCHAR(255) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    type ENUM('income', 'expense') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- สำหรับกรณีที่เคยสร้างตาราง transactions ไว้แล้วแต่ไม่มี user_id
-- ให้ใช้คำสั่งนี้ (ถ้าจำเป็น):
-- ALTER TABLE transactions ADD COLUMN user_id INT(11) NOT NULL AFTER id;
-- ALTER TABLE transactions ADD FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
