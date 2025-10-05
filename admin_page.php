<?php
session_start();
require_once 'config.php';


if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Clinic Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #e0f7fa, #ffffff);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: auto;
        }
        h1 {
            text-align: center;
            margin-bottom: 40px;
            color: #2c3e50;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 25px;
        }
        .card {
            background: #ffffff;
            padding: 30px 20px;
            border-radius: 14px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
            transition: 0.4s;
        }
        .card:hover {
            transform: translateY(-7px);
            box-shadow: 0 6px 14px rgba(0,0,0,0.15);
        }
        .card h2 {
            margin-bottom: 10px;
            color: #333;
        }
        .card p {
            color: #666;
            font-size: 15px;
            margin-bottom: 20px;
        }
        .card a {
            display: inline-block;
            padding: 10px 20px;
            background: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        .card a:hover {
            background: #004d40;
        }
        .logout-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            float:right;
            transition: background 0.3s;
        }
        .logout-btn:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="logout.php" class="logout-btn">Logout</a>
    <h1>Clinic Management - Admin Dashboard</h1>

    <div class="card-grid">
        <div class="card">
            <h2>Manage Doctors</h2>
            <p>Add, Edit, or Delete Doctors</p>
            <a href="doctors.php">Manage Doctors</a>
        </div>

        <div class="card">
            <h2>Manage Patients</h2>
            <p>Add, View, or Update Patients</p>
            <a href="patients.php">Manage Patients</a>
        </div>

        <div class="card">
            <h2>Appointments</h2>
            <p>View and Manage Appointments</p>
            <a href="appointments.php">Manage Appointments</a>
        </div>

        <div class="card">
            <h2>Staff Management</h2>
            <p>Add, View, or Remove Staff</p>
            <a href="staff.php">Manage Staff</a>
        </div>

        <div class="card">
            <h2>Manage doctor_schedules</h2>
            <p>Add, View, or Update doctor_schedules</p>
            <a href="doctor_schedule.php">Manage doctor_schedules</a>
        </div>

        <div class="card">
            <h2>Billing Management</h2>
            <p>Generate and Manage Bills</p>
            <a href="bills.php">Manage Bills</a>
        </div>
    </div>
</div>

</body>
</html>
