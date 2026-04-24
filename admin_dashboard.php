<?php
session_start();
require_once 'db_config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch stats
$user_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$transaction_count = $conn->query("SELECT COUNT(*) as count FROM transactions")->fetch_assoc()['count'];
$total_income = $conn->query("SELECT SUM(amount) as sum FROM transactions WHERE type = 'income'")->fetch_assoc()['sum'] ?? 0;
$total_expense = $conn->query("SELECT SUM(amount) as sum FROM transactions WHERE type = 'expense'")->fetch_assoc()['sum'] ?? 0;

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    $conn->query("DELETE FROM users WHERE id = $id AND role != 'admin'");
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch all users
$users_result = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - บันทึกรายรับรายจ่าย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>body { font-family: 'Kanit', sans-serif; }</style>
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-slate-800 text-white p-4 shadow-lg">
        <div class="max-w-6xl mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Admin Panel</h1>
            <div class="flex items-center space-x-6">
                <span>ผู้ดูแลระบบ: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                <a href="index.php" class="hover:text-blue-300">หน้าผู้ใช้ทั่วไป</a>
                <a href="logout.php" class="bg-red-500 px-3 py-1 rounded hover:bg-red-600 transition">ออกจากระบบ</a>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto py-10 px-4">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">ผู้ใช้งานทั้งหมด</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo $user_count; ?> คน</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm">รายการทั้งหมด</p>
                <p class="text-2xl font-bold text-gray-800"><?php echo $transaction_count; ?> รายการ</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">รายรับรวมทั้งระบบ</p>
                <p class="text-2xl font-bold text-green-600">฿<?php echo number_format($total_income, 2); ?></p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-md border-l-4 border-red-500">
                <p class="text-gray-500 text-sm">รายจ่ายรวมทั้งระบบ</p>
                <p class="text-2xl font-bold text-red-600">฿<?php echo number_format($total_expense, 2); ?></p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="text-xl font-semibold">รายชื่อผู้ใช้งาน</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-50">
                        <tr class="text-gray-500 text-sm border-b">
                            <th class="p-4 font-medium">ID</th>
                            <th class="p-4 font-medium">ชื่อผู้ใช้</th>
                            <th class="p-4 font-medium">บทบาท</th>
                            <th class="p-4 font-medium">วันที่สมัคร</th>
                            <th class="p-4 font-medium text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($u = $users_result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 text-sm text-gray-600"><?php echo $u['id']; ?></td>
                                <td class="p-4 font-medium"><?php echo htmlspecialchars($u['username']); ?></td>
                                <td class="p-4">
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'; ?>">
                                        <?php echo $u['role']; ?>
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                                <td class="p-4 text-center">
                                    <?php if ($u['role'] !== 'admin'): ?>
                                        <a href="admin_dashboard.php?delete_user=<?php echo $u['id']; ?>" 
                                           onclick="return confirm('ยืนยันการลบผู้ใช้และข้อมูลทั้งหมด?')"
                                           class="text-red-500 hover:text-red-700 text-sm font-medium">ลบผู้ใช้</a>
                                    <?php else: ?>
                                        <span class="text-gray-300 text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
