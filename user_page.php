<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: url('img.png') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .box {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            text-align: center;
            max-width: 400px;
        }

        .box h1 {
            margin-bottom: 10px;
            color: #333;
        }

        .box span {
            color: #5a67d8;
        }

        .box p {
            margin-bottom: 30px;
            color: #555;
            font-size: 16px;
        }

        .box button {
            background-color: #5a67d8;
            color: white;
            border: none;
            padding: 12px 20px;
            margin: 8px 5px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .box button:hover {
            background-color: #434190;
        }

        @media (max-width: 500px) {
            .box {
                padding: 30px 20px;
                width: 90%;
            }

            .box button {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="box">
        <h1>Welcome, <span><?= htmlspecialchars($_SESSION['name']); ?></span></h1>
        <p>This is your <span>User</span> dashboard.</p>

        <button onclick="window.location.href='patients.php'">Patients</button>
        <button onclick="window.location.href='appointments.php'">Appointments</button>
        <button onclick="window.location.href='doctors.php'">Doctors</button>
        <button onclick="window.location.href='bills.php'">Bills</button>
        <button onclick="window.location.href='staff.php'">staffs</button>
        <button onclick="window.location.href='doctor_schedule.php'">doctor_schedules</button>
        <button onclick="window.location.href='staff_attendance.php'">staff_attendance</button>
        <br><br>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>
</html>
