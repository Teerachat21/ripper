<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle form submission (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $amount = (float)$_POST['amount'];
    $type = $_POST['type'];

    if (!empty($title) && $amount > 0) {
        $sql = "INSERT INTO transactions (user_id, title, amount, type) VALUES ('$user_id', '$title', '$amount', '$type')";
        $conn->query($sql);
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    // Ensure the transaction belongs to the logged-in user
    $sql = "DELETE FROM transactions WHERE id = $id AND user_id = $user_id";
    $conn->query($sql);
    header("Location: index.php");
    exit();
}

// Fetch transactions for the logged-in user
$result = $conn->query("SELECT * FROM transactions WHERE user_id = $user_id ORDER BY created_at DESC");
$transactions = [];
$total_income = 0;
$total_expense = 0;

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
        if ($row['type'] === 'income') {
            $total_income += $row['amount'];
        } else {
            $total_expense += $row['amount'];
        }
    }
}

$balance = $total_income - $total_expense;

// Fetch monthly summary
$monthly_summary_result = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month_year,
        SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
        SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
    FROM transactions 
    WHERE user_id = $user_id 
    GROUP BY month_year 
    ORDER BY month_year DESC
");
$monthly_summaries = [];
if ($monthly_summary_result) {
    while ($row = $monthly_summary_result->fetch_assoc()) {
        $monthly_summaries[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกรายรับ-รายจ่าย</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-2xl mx-auto py-10 px-4">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">บันทึกรายรับ-รายจ่าย</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">สวัสดี, <strong><?php echo htmlspecialchars($username); ?></strong></span>
                <a href="logout.php" class="text-sm bg-red-100 text-red-600 px-3 py-1 rounded-md hover:bg-red-200 transition">ออกจากระบบ</a>
            </div>
        </div>

        <!-- Balance Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 flex justify-between items-center">
            <div>
                <p class="text-gray-500 text-sm">ยอดเงินคงเหลือ</p>
                <p class="text-3xl font-bold <?php echo $balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                    ฿<?php echo number_format($balance, 2); ?>
                </p>
            </div>
            <div class="text-right">
                <div class="mb-2">
                    <p class="text-gray-500 text-xs">รายรับรวม</p>
                    <p class="text-lg font-semibold text-green-500">+฿<?php echo number_format($total_income, 2); ?></p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs">รายจ่ายรวม</p>
                    <p class="text-lg font-semibold text-red-500">-฿<?php echo number_format($total_expense, 2); ?></p>
                </div>
            </div>
        </div>

        <!-- Monthly Summary Section -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">สรุปรายเดือน</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-gray-500 text-sm border-b">
                            <th class="pb-2 font-medium">เดือน/ปี</th>
                            <th class="pb-2 font-medium text-right">รายรับ</th>
                            <th class="pb-2 font-medium text-right">รายจ่าย</th>
                            <th class="pb-2 font-medium text-right">คงเหลือ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($monthly_summaries)): ?>
                            <tr>
                                <td colspan="4" class="py-4 text-center text-gray-400 text-sm">ยังไม่มีข้อมูลสรุป</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthly_summaries as $summary): ?>
                                <?php 
                                    $dateObj = DateTime::createFromFormat('Y-m', $summary['month_year']);
                                    $formatted_date = $dateObj->format('m/Y');
                                    $monthly_balance = $summary['income'] - $summary['expense'];
                                ?>
                                <tr class="text-sm">
                                    <td class="py-3 font-medium"><?php echo $formatted_date; ?></td>
                                    <td class="py-3 text-right text-green-600">+฿<?php echo number_format($summary['income'], 2); ?></td>
                                    <td class="py-3 text-right text-red-600">-฿<?php echo number_format($summary['expense'], 2); ?></td>
                                    <td class="py-3 text-right font-semibold <?php echo $monthly_balance >= 0 ? 'text-blue-600' : 'text-red-600'; ?>">
                                        ฿<?php echo number_format($monthly_balance, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Transaction Form -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4 border-b pb-2">เพิ่มรายการใหม่</h2>
            <form action="index.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">รายการ</label>
                    <input type="text" name="title" required placeholder="เช่น เงินเดือน, ค่าอาหาร" 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">จำนวนเงิน</label>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" 
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ประเภท</label>
                        <select name="type" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="income">รายรับ (+)</option>
                            <option value="expense">รายจ่าย (-)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" name="add_transaction" 
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition duration-200 font-medium">
                    บันทึกรายการ
                </button>
            </form>
        </div>

        <!-- Transaction History -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <h2 class="text-xl font-semibold p-6 border-b">ประวัติรายการ</h2>
            <div class="divide-y divide-gray-200 max-h-[400px] overflow-y-auto">
                <?php if (empty($transactions)): ?>
                    <p class="p-6 text-center text-gray-500">ยังไม่มีข้อมูลรายการ</p>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <div class="p-4 flex justify-between items-center hover:bg-gray-50 transition">
                            <div class="flex items-center space-x-4">
                                <div class="w-2 h-10 rounded <?php echo $t['type'] === 'income' ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                                <div>
                                    <p class="font-medium text-gray-800"><?php echo htmlspecialchars($t['title']); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo date('d/m/Y H:i', strtotime($t['created_at'])); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <p class="font-semibold <?php echo $t['type'] === 'income' ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $t['type'] === 'income' ? '+' : '-'; ?>฿<?php echo number_format($t['amount'], 2); ?>
                                </p>
                                <a href="index.php?delete=<?php echo $t['id']; ?>" 
                                   onclick="return confirm('ต้องการลบรายการนี้ใช่ไหม?')"
                                   class="text-gray-300 hover:text-red-500 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
