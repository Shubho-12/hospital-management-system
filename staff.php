<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['add_staff'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $role = $conn->real_escape_string($_POST['role']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $conn->query("INSERT INTO staff (name, role, contact) VALUES ('$name', '$role', '$contact')");
    header("Location: staff.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM staff WHERE id = $id");
    header("Location: staff.php");
    exit();
}

if (isset($_POST['update_staff'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $role = $conn->real_escape_string($_POST['role']);
    $contact = $conn->real_escape_string($_POST['contact']);
    $conn->query("UPDATE staff SET name='$name', role='$role', contact='$contact' WHERE id=$id");
    header("Location: staff.php");
    exit();
}

$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$searchQuery = $search ? "WHERE name LIKE '%$search%'" : '';
$totalStaff = $conn->query("SELECT COUNT(*) as total FROM staff $searchQuery")->fetch_assoc()['total'];
$totalPages = ceil($totalStaff / $limit);

$staffList = $conn->query("SELECT * FROM staff $searchQuery ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Staff</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #e8f0f8;
            padding: 20px;
        }
        .container {
            max-width: 1100px;
            margin: auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
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
        }
        form button {
            background-color: #2196F3;
            color: white;
            border: none;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 12px;
        }
        table th, table td {
            padding: 15px;
            text-align: center;
        }
        table th {
            background-color: #f4f6f9;
        }
        table tr:nth-child(even) {
            background-color: #fafafa;
        }
        .action-btn {
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            margin: 0 2px;
            color: white;
            border: none;
        }
        .edit-btn { background: #2196F3; }
        .delete-btn { background: #f44336; }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            background: #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
        }
        .pagination a.active {
            background: #2196F3;
            color: white;
        }
        .back-btn {
            background: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
        }
        .search-box {
            text-align: right;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Staff Management</h1>
    <a href="user_page.php" class="back-btn">⬅️ Back to Home</a>

    <div class="card">
        <h2>Add New Staff</h2>
        <form action="staff.php" method="post">
            <input type="text" name="name" placeholder="Name" required>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="Receptionist">Receptionist</option>
                <option value="Doctor">Doctor</option>
                <option value="Nurse">Nurse</option>
                <option value="Lab Technician">Lab Technician</option>
                <option value="Pharmacist">Pharmacist</option>
                <option value="Billing Staff">Billing Staff</option>
                <option value="Clinic Administrator">Clinic Administrator</option>
                <option value="IT Support">IT Support</option>
                <option value="Cleaner">Cleaner</option>
            </select>
            <input type="text" name="contact" placeholder="Contact" required>
            <button type="submit" name="add_staff">Add Staff</button>
        </form>
    </div>

    <div class="card">
        <div class="search-box">
            <form method="get" action="staff.php">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name...">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2>Staff List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($staff = $staffList->fetch_assoc()): ?>
                    <tr>
                        <td><?= $staff['id'] ?></td>
                        <td><?= htmlspecialchars($staff['name']) ?></td>
                        <td><?= htmlspecialchars($staff['role']) ?></td>
                        <td><?= htmlspecialchars($staff['contact']) ?></td>
                        <td>
                            <a class="action-btn edit-btn" href="staff.php?edit=<?= $staff['id'] ?>">Edit</a>
                            <a class="action-btn delete-btn" href="staff.php?delete=<?= $staff['id'] ?>" onclick="return confirm('Delete this staff member?')">Delete</a>
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

    <?php if (isset($_GET['edit'])):
        $id = (int)$_GET['edit'];
        $staff = $conn->query("SELECT * FROM staff WHERE id = $id")->fetch_assoc();
    ?>
    <div class="card">
        <h2>Edit Staff</h2>
        <form action="staff.php" method="post">
            <input type="hidden" name="id" value="<?= $staff['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($staff['name']) ?>" required>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="Receptionist" <?= $staff['role'] == 'Receptionist' ? 'selected' : '' ?>>Receptionist</option>
                <option value="Doctor" <?= $staff['role'] == 'Doctor' ? 'selected' : '' ?>>Doctor</option>
                <option value="Nurse" <?= $staff['role'] == 'Nurse' ? 'selected' : '' ?>>Nurse</option>
                <option value="Lab Technician" <?= $staff['role'] == 'Lab Technician' ? 'selected' : '' ?>>Lab Technician</option>
                <option value="Pharmacist" <?= $staff['role'] == 'Pharmacist' ? 'selected' : '' ?>>Pharmacist</option>
                <option value="Billing Staff" <?= $staff['role'] == 'Billing Staff' ? 'selected' : '' ?>>Billing Staff</option>
                <option value="Clinic Administrator" <?= $staff['role'] == 'Clinic Administrator' ? 'selected' : '' ?>>Clinic Administrator</option>
                <option value="IT Support" <?= $staff['role'] == 'IT Support' ? 'selected' : '' ?>>IT Support</option>
                <option value="Cleaner" <?= $staff['role'] == 'Cleaner' ? 'selected' : '' ?>>Cleaner</option>
            </select>
            <input type="text" name="contact" value="<?= htmlspecialchars($staff['contact']) ?>" required>
            <button type="submit" name="update_staff" style="background: #FF9800;">Update Staff</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
