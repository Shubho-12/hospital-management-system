<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config.php';

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}


if (isset($_POST['add_patient'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $insertQuery = "INSERT INTO patients (name, age, gender, phone) VALUES ('$name', '$age', '$gender', '$phone')";
    if ($conn->query($insertQuery)) {
        header("Location: patients.php");
        exit();
    } else {
        echo "Error inserting patient: " . $conn->error;
    }
}


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM patients WHERE id = $id");
    header("Location: patients.php");
    exit();
}


if (isset($_POST['update_patient'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $phone = $conn->real_escape_string($_POST['phone']);

    $conn->query("UPDATE patients SET name='$name', age='$age', gender='$gender', phone='$phone' WHERE id=$id");
    header("Location: patients.php");
    exit();
}


$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 5;
$offset = ($page - 1) * $limit;

$searchQuery = $search ? "WHERE name LIKE '%$search%' OR phone LIKE '%$search%'" : '';
$totalPatients = $conn->query("SELECT COUNT(*) as total FROM patients $searchQuery")->fetch_assoc()['total'];
$totalPages = ceil($totalPatients / $limit);

$patients = $conn->query("SELECT * FROM patients $searchQuery ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Patients</title>
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
    <h1>Patient Management </h1>
    <a href="user_page.php" class="back-btn">⬅️ Back to Home</a>

    <div class="card">
        <h2>Add New Patient</h2>
        <form action="patients.php" method="post">
            <input type="text" name="name" placeholder="Patient Name" required>
            <input type="number" name="age" placeholder="Age" required>
            <input type="text" name="gender" placeholder="Gender" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <button type="submit" name="add_patient">Add Patient</button>
        </form>
    </div>

    <div class="card">
        <div class="search-box">
            <form method="get" action="patients.php">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by Name or Phone...">
                <button type="submit">Search</button>
            </form>
        </div>

        <h2>Patient List</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($patient = $patients->fetch_assoc()): ?>
                    <tr>
                        <td><?= $patient['id'] ?></td>
                        <td><?= htmlspecialchars($patient['name']) ?></td>
                        <td><?= $patient['age'] ?></td>
                        <td><?= htmlspecialchars($patient['gender']) ?></td>
                        <td><?= htmlspecialchars($patient['phone']) ?></td>
                        <td>
                            <a class="action-btn" href="patients.php?edit=<?= $patient['id'] ?>">Edit</a>
                            <a class="action-btn delete" href="patients.php?delete=<?= $patient['id'] ?>" onclick="return confirm('Delete this patient?')">Delete</a>
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
        $patient = $conn->query("SELECT * FROM patients WHERE id = $id")->fetch_assoc();
    ?>
    <div class="card">
        <h2>Edit Patient</h2>
        <form action="patients.php" method="post">
            <input type="hidden" name="id" value="<?= $patient['id'] ?>">
            <input type="text" name="name" value="<?= htmlspecialchars($patient['name']) ?>" required>
            <input type="number" name="age" value="<?= $patient['age'] ?>" required>
            <input type="text" name="gender" value="<?= htmlspecialchars($patient['gender']) ?>" required>
            <input type="text" name="phone" value="<?= htmlspecialchars($patient['phone']) ?>" required>
            <button type="submit" name="update_patient" style="background: #FF9800;">Update Patient</button>
        </form>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
