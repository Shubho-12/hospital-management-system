<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['add_bill'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $treatment = $conn->real_escape_string($_POST['treatment']);
    $amount = $conn->real_escape_string($_POST['amount']);
    $date = $conn->real_escape_string($_POST['date']);
    $conn->query("INSERT INTO bills (name, treatment, amount, date) VALUES ('$name', '$treatment', '$amount', '$date')");
    header("Location: bills.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM bills WHERE id = $id");
    header("Location: bills.php");
    exit();
}

if (isset($_POST['update_bill'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $treatment = $conn->real_escape_string($_POST['treatment']);
    $amount = $conn->real_escape_string($_POST['amount']);
    $date = $conn->real_escape_string($_POST['date']);
    $conn->query("UPDATE bills SET name='$name', treatment='$treatment', amount='$amount', date='$date' WHERE id=$id");
    header("Location: bills.php");
    exit();
}

$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$searchQuery = $search ? "WHERE name LIKE '%$search%'" : '';
$totalBills = $conn->query("SELECT COUNT(*) as total FROM bills $searchQuery")->fetch_assoc()['total'];
$totalPages = ceil($totalBills / $limit);

$bills = $conn->query("SELECT * FROM bills $searchQuery ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Bills</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 20px;
        }
        .container { max-width: 1100px; margin: auto; }
        h1 { text-align: center; margin-bottom: 20px; color: #333; }
        .card {
            background: #fff;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        form input, form select, form button, .search-box input {
            padding: 12px;
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
        }
        form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }
        form button:hover { background-color: #45a049; }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        table th, table td {
            padding: 15px;
            text-align: center;
        }
        table th { background-color: #f4f6f9; color: #333; }
        table tr:nth-child(even) { background-color: #fafafa; }
        table tr:hover { background-color: #f1f1f1; }
        .action-btn {
            background: #2196F3;
            border: none;
            padding: 8px 12px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            margin: 0 2px;
            display: inline-block;
        }
        .action-btn.delete { background: #f44336; }
        .action-btn.share { background: #9c27b0; }
        .action-btn:hover { opacity: 0.8; }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            text-decoration: none;
            background: #ddd;
            color: #333;
            border-radius: 6px;
            transition: 0.3s;
        }
        .pagination a.active { background: #4CAF50; color: white; }
        .pagination a:hover { background: #bbb; }
        .search-box {
            text-align: right;
            margin-bottom: 10px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .back-btn:hover { background: #45a049; }
    </style>
</head>
<body>

<div class="container">
    <h1>Bill Management</h1>
    <a href="user_page.php" class="back-btn">⬅️ Back to Home</a>

    <div class="card">
        <h2>Add New Bill</h2>
        <form action="bills.php" method="post">
            <input type="text" name="name" placeholder="Enter Name" required>
            <select name="treatment" required>
                <option value="">Select Treatment</option>
                <option value="General Checkup">General Checkup</option>
                <option value="Dental Cleaning">Dental Cleaning</option>
                <option value="X-Ray">X-Ray</option>
                <option value="Vaccination">Vaccination</option>
                <option value="Blood Test">Blood Test</option>
                <option value="Minor Surgery">Minor Surgery</option>
            </select>
            <input type="number" step="0.01" name="amount" placeholder="Amount (₹)" required>
            <input type="date" name="date" required>
            <button type="submit" name="add_bill">Add Bill</button>
        </form>
    </div>

    <div class="card">
        <div class="search-box">
            <form method="get" action="bills.php">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name...">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2>Bill List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Treatment</th>
                    <th>Amount (₹)</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($bill = $bills->fetch_assoc()): ?>
                    <tr>
                        <td><?= $bill['id'] ?></td>
                        <td><?= htmlspecialchars($bill['name']) ?></td>
                        <td><?= htmlspecialchars($bill['treatment']) ?></td>
                        <td>₹<?= number_format($bill['amount'], 2) ?></td>
                        <td><?= $bill['date'] ?></td>
                        <td>
                            <a class="action-btn" href="bills.php?edit=<?= $bill['id'] ?>">Edit</a>
                            <a class="action-btn delete" href="bills.php?delete=<?= $bill['id'] ?>" onclick="return confirm('Delete this bill?')">Delete</a>
                            <button class="action-btn share" onclick="copyToClipboard('<?= addslashes($bill['name']) ?> | <?= $bill['treatment'] ?> | ₹<?= number_format($bill['amount'],2) ?> | <?= $bill['date'] ?>')">Share</button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
    </div>

    <?php
    if (isset($_GET['edit'])):
        $id = (int)$_GET['edit'];
        $bill = $conn->query("SELECT * FROM bills WHERE id = $id")->fetch_assoc();
    ?>
    <div class="card">
        <h2>Edit Bill</h2>
        <form action="bills.php" method="post">
            <input type="hidden" name="id" value="<?= $bill['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($bill['name']) ?>" required>
            <select name="treatment" required>
                <option value="">Select Treatment</option>
                <option value="General Checkup" <?= $bill['treatment'] == 'General Checkup' ? 'selected' : '' ?>>General Checkup</option>
                <option value="Dental Cleaning" <?= $bill['treatment'] == 'Dental Cleaning' ? 'selected' : '' ?>>Dental Cleaning</option>
                <option value="X-Ray" <?= $bill['treatment'] == 'X-Ray' ? 'selected' : '' ?>>X-Ray</option>
                <option value="Vaccination" <?= $bill['treatment'] == 'Vaccination' ? 'selected' : '' ?>>Vaccination</option>
                <option value="Blood Test" <?= $bill['treatment'] == 'Blood Test' ? 'selected' : '' ?>>Blood Test</option>
                <option value="Minor Surgery" <?= $bill['treatment'] == 'Minor Surgery' ? 'selected' : '' ?>>Minor Surgery</option>
            </select>
            <input type="number" step="0.01" name="amount" value="<?= $bill['amount'] ?>" required>
            <input type="date" name="date" value="<?= $bill['date'] ?>" required>
            <button type="submit" name="update_bill" style="background: #FF9800;">Update Bill</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text)
    .then(() => {
        alert("Bill Info Copied: " + text);
    })
    .catch(err => {
        alert('Error copying text: ' + err);
    });
}
</script>

</body>
</html>
