<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit_attendance'])) {
    $staff_id = (int)$_POST['staff_id'];
    $status = $_POST['status'];
    $clock_in = $_POST['clock_in'] ?? null;
    $clock_out = $_POST['clock_out'] ?? null;
    $date = date('Y-m-d');

    $conn->query("INSERT INTO staff_attendance (staff_id, date, status, clock_in, clock_out) VALUES ($staff_id, '$date', '$status', '$clock_in', '$clock_out')");
    header("Location: staff_attendance.php");
    exit();
}


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM staff_attendance WHERE id = $id");
    header("Location: staff_attendance.php");
    exit();
}


if (isset($_POST['update_attendance'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $clock_in = $_POST['clock_in'] ?? null;
    $clock_out = $_POST['clock_out'] ?? null;
    $conn->query("UPDATE staff_attendance SET status='$status', clock_in='$clock_in', clock_out='$clock_out' WHERE id=$id");
    header("Location: staff_attendance.php");
    exit();
}


$search = $_GET['search'] ?? '';
$history_date = $_GET['history_date'] ?? date('Y-m-d');
$searchClause = $search ? "AND s.name LIKE '%$search%'" : '';

$staffList = $conn->query("SELECT * FROM staff ORDER BY name ASC");

$attendanceList = $conn->query("SELECT a.*, s.name FROM staff_attendance a JOIN staff s ON a.staff_id = s.id WHERE a.date = '$history_date' $searchClause ORDER BY a.clock_in ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Attendance</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f7fb; padding: 20px; }
        .container { max-width: 1100px; margin: auto; }
        h1, h2 { text-align: center; }
        .card { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        form input, form select, form button { padding: 10px; margin-right: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px; }
        button { background-color: #2196F3; color: white; border: none; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        table th { background-color: #f1f1f1; }
        .search-form { text-align: right; margin-bottom: 10px; }
        .action-btn { padding: 5px 10px; border: none; border-radius: 4px; color: white; cursor: pointer; font-size: 14px; }
        .edit-btn { background: #f0ad4e; }
        .delete-btn { background: #d9534f; }
        .back-btn {
            background: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Staff Attendance</h1>
    <a href="user_page.php" class="back-btn">⬅️ Back to Home</a>

    <div class="card">
        <h2>Mark Attendance</h2>
        <form method="post">
            <select name="staff_id" required>
                <option value="">-- Select Staff --</option>
                <?php while ($staff = $staffList->fetch_assoc()): ?>
                    <option value="<?= $staff['id'] ?>"><?= htmlspecialchars($staff['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <select name="status" required>
                <option value="Present">Present</option>
                <option value="Absent">Absent</option>
            </select>
            <input type="time" name="clock_in" placeholder="Clock In">
            <input type="time" name="clock_out" placeholder="Clock Out">
            <button type="submit" name="submit_attendance">Submit</button>
        </form>
    </div>

    <div class="card">
        <h2>Attendance History</h2>
        <form method="get" class="search-form">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name">
            <input type="date" name="history_date" value="<?= htmlspecialchars($history_date) ?>">
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Clock In</th>
                    <th>Clock Out</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($att = $attendanceList->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($att['name']) ?></td>
                    <td><?= $att['date'] ?></td>
                    <td><?= $att['status'] ?></td>
                    <td><?= $att['status'] === 'Absent' ? 'Not Available' : $att['clock_in'] ?></td>
                    <td><?= $att['status'] === 'Absent' ? 'Not Available' : $att['clock_out'] ?></td>
                    <td>
                        <form method="post" style="display:inline-block;">
                            <input type="hidden" name="id" value="<?= $att['id'] ?>">
                            <select name="status">
                                <option value="Present" <?= $att['status'] === 'Present' ? 'selected' : '' ?>>Present</option>
                                <option value="Absent" <?= $att['status'] === 'Absent' ? 'selected' : '' ?>>Absent</option>
                            </select>
                            <input type="time" name="clock_in" value="<?= $att['clock_in'] ?>">
                            <input type="time" name="clock_out" value="<?= $att['clock_out'] ?>">
                            <button class="action-btn edit-btn" type="submit" name="update_attendance">Update</button>
                        </form>
                        <a class="action-btn delete-btn" href="?delete=<?= $att['id'] ?>" onclick="return confirm('Delete this attendance record?')">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
