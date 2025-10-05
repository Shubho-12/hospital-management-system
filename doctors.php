<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}


$specialties = [
    "Cardiology",
    "Dermatology",
    "Neurology",
    "Orthopedics",
    "Pediatrics",
    "Psychiatry",
    "Radiology",
    "Oncology",
    "Gastroenterology",
    "Endocrinology"
];


if (isset($_POST['add_doctor'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $specialty = $conn->real_escape_string($_POST['specialty']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $insertQuery = "INSERT INTO doctors (name, specialty, phone) VALUES ('$name', '$specialty', '$phone')";
    if ($conn->query($insertQuery)) {
        header("Location: doctors.php");
        exit();
    } else {
        echo "Error inserting doctor: " . $conn->error;
    }
}


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM doctors WHERE id = $id");
    header("Location: doctors.php");
    exit();
}


if (isset($_POST['update_doctor'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $specialty = $conn->real_escape_string($_POST['specialty']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $conn->query("UPDATE doctors SET name='$name', specialty='$specialty', phone='$phone' WHERE id=$id");
    header("Location: doctors.php");
    exit();
}


$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$searchQuery = $search ? "WHERE name LIKE '%$search%' OR specialty LIKE '%$search%'" : '';
$totalDoctors = $conn->query("SELECT COUNT(*) as total FROM doctors $searchQuery")->fetch_assoc()['total'];
$totalPages = ceil($totalDoctors / $limit);

$doctors = $conn->query("SELECT * FROM doctors $searchQuery ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Doctors</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        
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
    <h1>Doctor Management </h1>
    <a href="user_page.php" class="back-btn">⬅️ Back to Home</a>

    <div class="card">
        <h2>Add New Doctor</h2>
        <form action="doctors.php" method="post">
            <input type="text" name="name" placeholder="Doctor Name" required>
            <select name="specialty" required>
                <option value="">Select Specialty</option>
                <?php foreach ($specialties as $spec): ?>
                    <option value="<?= htmlspecialchars($spec) ?>"><?= htmlspecialchars($spec) ?></option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <button type="submit" name="add_doctor">Add Doctor</button>
        </form>
    </div>

    <div class="card">
        <div class="search-box">
            <form method="get" action="doctors.php">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name or Specialty...">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2>Doctor List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($doctor = $doctors->fetch_assoc()): ?>
                    <tr>
                        <td><?= $doctor['id'] ?></td>
                        <td><?= htmlspecialchars($doctor['name']) ?></td>
                        <td><?= htmlspecialchars($doctor['specialty']) ?></td>
                        <td><?= htmlspecialchars($doctor['phone']) ?></td>
                        <td>
                            <a class="action-btn" href="doctors.php?edit=<?= $doctor['id'] ?>">Edit</a>
                            <a class="action-btn delete" href="doctors.php?delete=<?= $doctor['id'] ?>" onclick="return confirm('Delete this doctor?')">Delete</a>
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
        $doctor = $conn->query("SELECT * FROM doctors WHERE id = $id")->fetch_assoc();
    ?>
    <div class="card">
        <h2>Edit Doctor</h2>
        <form action="doctors.php" method="post">
            <input type="hidden" name="id" value="<?= $doctor['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($doctor['name']) ?>" required>
            <select name="specialty" required>
                <?php foreach ($specialties as $spec): ?>
                    <option value="<?= htmlspecialchars($spec) ?>" <?= $doctor['specialty'] == $spec ? 'selected' : '' ?>>
                        <?= htmlspecialchars($spec) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="phone" value="<?= htmlspecialchars($doctor['phone']) ?>" required>
            <button type="submit" name="update_doctor" style="background: #FF9800;">Update Doctor</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
