<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Add Schedule
if (isset($_POST['add_schedule'])) {
    $doctor_id = (int)$_POST['doctor_id'];
    $date = $_POST['date'];
    $shift_time = $conn->real_escape_string($_POST['shift_time']);
    $notes = $conn->real_escape_string($_POST['notes']);
$conn->query("INSERT INTO doctor_schedule (doctor_id, date, shift_time, notes) VALUES ($doctor_id, '$date', '$shift_time', '$notes')");

    header("Location: doctor_schedule.php");
    exit();
}

// Delete Schedule
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM doctor_schedule WHERE id = $id");
    header("Location: doctor_schedule.php");
    exit();
}

// Update Schedule
if (isset($_POST['update_schedule'])) {
    $id = (int)$_POST['id'];
    $doctor_id = (int)$_POST['doctor_id'];
    $date = $_POST['date'];
    $shift_time = $conn->real_escape_string($_POST['shift_time']);
    $notes = $conn->real_escape_string($_POST['notes']);

    $conn->query("UPDATE doctor_schedule 
                  SET doctor_id = $doctor_id, date = '$date', shift_time = '$shift_time', notes = '$notes' 
                  WHERE id = $id");

    header("Location: doctor_schedule.php");
    exit();
}


// Search & Pagination
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$searchQuery = $search ? "WHERE s.name LIKE '%$search%'" : '';
$total = $conn->query("SELECT COUNT(*) AS total FROM doctor_schedule ds JOIN staff s ON ds.doctor_id = s.id $searchQuery")->fetch_assoc()['total'];
$totalPages = ceil($total / $limit);

// Fetch schedules
$schedules = $conn->query("SELECT ds.*, s.name as doctor_name 
                           FROM doctor_schedule ds 
                           JOIN staff s ON ds.doctor_id = s.id 
                           $searchQuery 
                           ORDER BY ds.date DESC 
                           LIMIT $limit OFFSET $offset");

// Fetch all doctors
$doctors = $conn->query("SELECT * FROM staff WHERE role = 'Doctor' ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Doctor Schedule</title>
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
        form input, form select, form textarea, form button, .search-box input {
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
    <h1>Doctor Schedule</h1>
    <a href="user_page.php" class="back-btn">⬅️ Back to Home</a>

    <div class="card">
        <h2>Add New Schedule</h2>
        <form action="doctor_schedule.php" method="post">
            <select name="doctor_id" required>
                <option value="">-- Select Doctor --</option>
                <?php while ($d = $doctors->fetch_assoc()): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <input type="date" name="date" required>
            <input type="text" name="shift_time" placeholder="e.g. 9am - 1pm" required>
            <textarea name="notes" placeholder="Purpose or remarks..." rows="2" style="width:60%;"></textarea>
            <button type="submit" name="add_schedule">Add Schedule</button>
        </form>
    </div>

    <div class="card">
        <div class="search-box">
            <form method="get" action="doctor_schedule.php">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Doctor Name...">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2>Schedule List</h2>
        <table>
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Shift Time</th>
                    <th>notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $schedules->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['doctor_name']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['shift_time']) ?></td>
                        <td><?= htmlspecialchars($row['notes']) ?></td>
                        <td>
                            <a class="action-btn edit-btn" href="doctor_schedule.php?edit=<?= $row['id'] ?>">Edit</a>
                            <a class="action-btn delete-btn" href="doctor_schedule.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this schedule?')">Delete</a>
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
        $edit_row = $conn->query("SELECT * FROM doctor_schedule WHERE id = $id")->fetch_assoc();
    ?>
    <div class="card">
        <h2>Edit Schedule</h2>
        <form action="doctor_schedule.php" method="post">
            <input type="hidden" name="id" value="<?= $edit_row['id'] ?>">
            <select name="doctor_id" required>
                <option value="">-- Select Doctor --</option>
                <?php
                $doctors = $conn->query("SELECT * FROM staff WHERE role = 'Doctor' ORDER BY name ASC");

                while ($d = $doctors->fetch_assoc()):
                ?>
                    <option value="<?= $d['id'] ?>" <?= $d['id'] == $edit_row['doctor_id'] ? 'selected' : '' ?>><?= htmlspecialchars($d['name']) ?></option>
                <?php endwhile; ?>
            </select>
            <input type="date" name="date" value="<?= $edit_row['date'] ?>" required>
            <input type="text" name="shift_time" value="<?= htmlspecialchars($edit_row['shift_time']) ?>" required>
            <textarea name="notes" rows="2" style="width:60%;"><?= htmlspecialchars($edit_row['notes']) ?></textarea>
            <button type="submit" name="update_schedule" style="background: #FF9800;">Update Schedule</button>
        </form>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
