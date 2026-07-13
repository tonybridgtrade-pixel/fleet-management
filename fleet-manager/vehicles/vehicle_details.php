<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

/* =========================
   الفلاتر
========================= */

$filter = "";

if(isset($_GET['status'])){

    $status = $_GET['status'];

    $filter = "WHERE vehicles.vehicle_status='$status'";
}

if(isset($_GET['license'])){

    if($_GET['license'] == "soon"){

        $filter = "
        WHERE vehicles.license_end
        BETWEEN CURDATE()
        AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ";
    }

    if($_GET['license'] == "expired"){

        $filter = "
        WHERE vehicles.license_end < CURDATE()
        ";
    }
}

/* =========================
   الإحصائيات
========================= */

$total = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
")->fetch_assoc()['total'];

$available = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
WHERE vehicle_status='متاحة'
")->fetch_assoc()['total'];

$assigned = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
WHERE vehicle_status='مع مهندس'
")->fetch_assoc()['total'];

$maintenance = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
WHERE vehicle_status='في الصيانة'
")->fetch_assoc()['total'];

$rent = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
WHERE ownership_type='إيجار'
")->fetch_assoc()['total'];

$owned = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
WHERE ownership_type='ملك'
")->fetch_assoc()['total'];

$expired = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
WHERE license_end < CURDATE()
")->fetch_assoc()['total'];

$soon = $conn->query("
SELECT COUNT(*) as total
FROM vehicles
WHERE license_end BETWEEN CURDATE()
AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
")->fetch_assoc()['total'];

/* =========================
   العربيات
========================= */

$vehicles = $conn->query("
SELECT vehicles.*, engineers.full_name
FROM vehicles

LEFT JOIN engineers
ON vehicles.assigned_engineer = engineers.id

$filter

ORDER BY vehicles.id DESC
");

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>Fleet Management</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    direction:rtl;
}

/* navbar */

.navbar{
    background:#111827;
    color:white;
    padding:15px 25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.navbar a{
    color:white;
    text-decoration:none;
}

/* container */

.container{
    padding:25px;
}

/* cards */

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:15px;
    margin-bottom:30px;
}

.stat-card{
    color:white;
    padding:25px;
    border-radius:12px;
    text-align:center;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    text-decoration:none;
    transition:0.3s;
    display:block;
}

.stat-card:hover{
    transform:translateY(-5px);
}

.stat-card h2{
    margin:10px 0 0;
    font-size:35px;
}

.blue{
    background:#007bff;
}

.green{
    background:#16a34a;
}

.red{
    background:#dc2626;
}

.orange{
    background:#f59e0b;
}

.dark{
    background:#111827;
}

.purple{
    background:#7c3aed;
}

/* sections */

.section-title{
    margin-top:40px;
    margin-bottom:20px;
    font-size:24px;
    background:#111827;
    color:white;
    padding:15px;
    border-radius:10px;
}

/* fleet cards */

.fleet-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
    gap:20px;
}

.fleet-card{
    background:white;
    border-radius:14px;
    padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
    transition:0.3s;
    border-top:6px solid #007bff;
}

.fleet-card:hover{
    transform:translateY(-5px);
}

.fleet-card h3{
    margin-top:0;
    margin-bottom:15px;
}

.fleet-card p{
    margin:8px 0;
    color:#444;
}

/* status colors */

.available-border{
    border-top-color:#16a34a;
}

.assigned-border{
    border-top-color:#007bff;
}

.maintenance-border{
    border-top-color:#dc2626;
}

.rent-border{
    border-top-color:#f59e0b;
}

.owned-border{
    border-top-color:#7c3aed;
}

/* buttons */

.btns{
    margin-top:20px;
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.btn{
    text-decoration:none;
    color:white;
    padding:10px 14px;
    border-radius:8px;
    font-size:14px;
}

.report-btn{
    background:#111827;
}

.edit-btn{
    background:#007bff;
}

.service-btn{
    background:#dc2626;
}

.move-btn{
    background:#16a34a;
}

.back-btn{
    display:inline-block;
    margin-top:30px;
    background:#111827;
    color:white;
    padding:12px 20px;
    border-radius:10px;
    text-decoration:none;
}

.empty{
    background:white;
    padding:20px;
    border-radius:12px;
    text-align:center;
    color:#777;
}

</style>

</head>
<body>

<div class="navbar">

    <h2>🚗 Fleet Management</h2>

    <div>

        <a href="../dashboard.php">

            Dashboard

        </a>

    </div>

</div>

<div class="container">

<!-- الإحصائيات -->

<div class="stats">

    <a href="fleet.php" class="stat-card dark">

        إجمالي العربيات

        <h2><?= $total ?></h2>

    </a>

    <a href="fleet.php?status=متاحة" class="stat-card green">

        عربيات متاحة

        <h2><?= $available ?></h2>

    </a>

    <a href="fleet.php?status=مع مهندس" class="stat-card blue">

        مع مهندسين

        <h2><?= $assigned ?></h2>

    </a>

    <a href="fleet.php?status=في الصيانة" class="stat-card red">

        في الصيانة

        <h2><?= $maintenance ?></h2>

    </a>

    <a href="fleet.php?license=soon" class="stat-card orange">

        رخص قربت تنتهي

        <h2><?= $soon ?></h2>

    </a>

    <a href="fleet.php?license=expired" class="stat-card red">

        رخص منتهية

        <h2><?= $expired ?></h2>

    </a>

    <a href="fleet.php?ownership=إيجار" class="stat-card orange">

        عربيات إيجار

        <h2><?= $rent ?></h2>

    </a>

    <a href="fleet.php?ownership=ملك" class="stat-card purple">

        عربيات ملك

        <h2><?= $owned ?></h2>

    </a>

</div>

<!-- العربيات -->

<h2 class="section-title">
🚘 العربيات
</h2>

<div class="fleet-grid">

<?php while($car = $vehicles->fetch_assoc()) { ?>

<?php

$border = "available-border";

if($car['vehicle_status'] == "مع مهندس"){

    $border = "assigned-border";
}

if($car['vehicle_status'] == "في الصيانة"){

    $border = "maintenance-border";
}

?>

<div class="fleet-card <?= $border ?>">

    <h3>🚘 <?= $car['brand'] ?></h3>

    <p><b>رقم العربية:</b>
    <?= $car['plate_number'] ?></p>

    <p><b>الموديل:</b>
    <?= $car['model'] ?></p>

    <p><b>العداد:</b>
    <?= $car['kilometers'] ?> KM</p>

    <p><b>الحالة:</b>
    <?= $car['vehicle_status'] ?></p>

    <p><b>نوع الملكية:</b>
    <?= $car['ownership_type'] ?></p>

    <p><b>المهندس:</b>
    <?= $car['full_name'] ?: 'لا يوجد' ?></p>

    <p><b>الرخصة:</b>
    <?= $car['license_end'] ?></p>

    <div class="btns">

        <a class="btn report-btn"
        href="report.php?id=<?= $car['id'] ?>">

            📄 تقرير

        </a>

        <a class="btn edit-btn"
        href="edit.php?id=<?= $car['id'] ?>">

            ✏ تعديل

        </a>

        <a class="btn service-btn"
        href="../services/list.php">

            🛠 صيانة

        </a>

        <a class="btn move-btn"
        href="../movement/add_movement.php">

            📊 حركة

        </a>

    </div>

</div>

<?php } ?>

</div>

<a class="back-btn"
href="../dashboard.php">

⬅ رجوع للوحة التحكم

</a>

</div>

</body>
</html>