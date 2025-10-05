<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['add_appointment'])) {
    $patient_name = $conn->real_escape_string($_POST['patient_name']);
    $doctor_name = $conn->real_escape_string($_POST['doctor_name']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);
    $purpose = $conn->real_escape_string($_POST['purpose']);

    $insertQuery = "INSERT INTO appointments (patient_name, doctor_name, date, time, purpose)
                    VALUES ('$patient_name', '$doctor_name', '$date', '$time', '$purpose')";
    if ($conn->query($insertQuery)) {
        header("Location: appointments.php");
        exit();
    } else {
        echo "Error inserting appointment: " . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM appointments WHERE id = $id");
    header("Location: appointments.php");
    exit();
}

if (isset($_POST['update_appointment'])) {
    $id = (int)$_POST['id'];
    $patient_name = $conn->real_escape_string($_POST['patient_name']);
    $doctor_name = $conn->real_escape_string($_POST['doctor_name']);
    $date = $conn->real_escape_string($_POST['date']);
    $time = $conn->real_escape_string($_POST['time']);
    $purpose = $conn->real_escape_string($_POST['purpose']);

    $conn->query("UPDATE appointments 
                  SET patient_name='$patient_name', doctor_name='$doctor_name', date='$date', time='$time', purpose='$purpose' 
                  WHERE id=$id");
    header("Location: appointments.php");
    exit();
}

$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$searchQuery = $search ? "WHERE patient_name LIKE '%$search%' OR doctor_name LIKE '%$search%'" : '';
$totalAppointments = $conn->query("SELECT COUNT(*) as total FROM appointments $searchQuery")->fetch_assoc()['total'];
$totalPages = ceil($totalAppointments / $limit);

$appointments = $conn->query("SELECT * FROM appointments $searchQuery ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Appointments</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* [Keep all your original CSS styling here, unchanged...] */
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .card {
            background: #fff;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        form input, form button, .search-box input {
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

        form button:hover {
            background-color: #45a049;
        }

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

        table th {
            background-color: #f4f6f9;
            color: #333;
        }

        table tr:nth-child(even) {
            background-color: #fafafa;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

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

        .action-btn:hover {
            opacity: 0.8;
        }

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

        .pagination a.active {
            background: #4CAF50;
            color: white;
        }

        .pagination a:hover {
            background: #bbb;
        }

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
        .back-btn:hover {
            background: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Appointment Management</h1>
    <a href="user_page.php" class="back-btn">⬅️ Back to Home</a>

    <div class="card">
        <h2>Add New Appointment</h2>
        <form action="appointments.php" method="post">
            <input type="text" name="patient_name" placeholder="Patient Name" required>
            <input type="text" name="doctor_name" placeholder="Doctor Name" required>
            <input type="date" name="date" required>
            <input type="time" name="time" required>
            <input type="text" name="purpose" placeholder="Purpose of Visit" required>
            <button type="submit" name="add_appointment">Add Appointment</button>
        </form>
    </div>

    <div class="card">
        <div class="search-box">
            <form method="get" action="appointments.php">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Patient or Doctor...">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2>Appointment List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Patient Name</th>
                    <th>Doctor Name</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Purpose</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($appointment = $appointments->fetch_assoc()): ?>
                    <tr>
                        <td><?= $appointment['id'] ?></td>
                        <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                        <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                        <td><?= $appointment['date'] ?></td>
                        <td><?= $appointment['time'] ?></td>
                        <td><?= htmlspecialchars($appointment['purpose']) ?></td>
                        <td>
                            <a class="action-btn" href="appointments.php?edit=<?= $appointment['id'] ?>">Edit</a>
                            <a class="action-btn delete" href="appointments.php?delete=<?= $appointment['id'] ?>" onclick="return confirm('Delete this appointment?')">Delete</a>
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
        $appointment = $conn->query("SELECT * FROM appointments WHERE id = $id")->fetch_assoc();
    ?>
    <div class="card">
        <h2>Edit Appointment</h2>
        <form action="appointments.php" method="post">
            <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
            <input type="text" name="patient_name" value="<?= htmlspecialchars($appointment['patient_name']) ?>" required>
            <input type="text" name="doctor_name" value="<?= htmlspecialchars($appointment['doctor_name']) ?>" required>
            <input type="date" name="date" value="<?= $appointment['date'] ?>" required>
            <input type="time" name="time" value="<?= $appointment['time'] ?>" required>
            <input type="text" name="purpose" value="<?= htmlspecialchars($appointment['purpose']) ?>" required>
            <button type="submit" name="update_appointment" style="background: #FF9800;">Update Appointment</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
