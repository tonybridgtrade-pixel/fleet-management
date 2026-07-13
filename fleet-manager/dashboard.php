<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: login.php"); exit; }
include "config/db.php";

// استعلامات دقيقة بناءً على طلبك
$totalCars = $conn->query("SELECT COUNT(*) as total FROM vehicles")->fetch_assoc()['total'];
$availableCars = $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE vehicle_status='متاحة'")->fetch_assoc()['total'];
$assignedCars = $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE vehicle_status='مع مهندس'")->fetch_assoc()['total'];
$maintenanceCars = $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE vehicle_status='في الصيانة'")->fetch_assoc()['total'];
$expired = $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE license_end < CURDATE()")->fetch_assoc()['total'];

// استعلام الرخص التي ستنتهي خلال 30 يوم
$soon = $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE license_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['total'];

$latest = $conn->query("SELECT * FROM vehicles ORDER BY id DESC LIMIT 6");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Fleet Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;700&display=swap');
        
        body { font-family: 'Cairo', sans-serif; background: #f0f2f5; margin: 0; transition: 0.4s; }
        .navbar { background: #111827; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .sidebar { position: fixed; right: 0; width: 250px; height: 100%; background: #1e293b; padding: 20px; color: white; }
        .sidebar a { display: block; color: #cbd5e1; text-decoration: none; padding: 12px; margin-bottom: 8px; border-radius: 8px; transition: 0.3s; }
        .sidebar a:hover { background: #2563eb; color: white; }
        .content { margin-right: 250px; padding: 30px; }
        
        .theme-toggle { background: #111827; color: white; border: none; padding: 10px 20px; border-radius: 50px; cursor: pointer; margin-bottom: 20px; font-size: 14px; transition: 0.3s; display: flex; align-items: center; gap: 8px; }
        .theme-toggle:hover { background: #2563eb; }

        .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: white; padding: 20px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-decoration: none; color: #333; transition: 0.3s; border-right: 5px solid #2563eb; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        .card h3 { font-size: 14px; color: #6b7280; margin: 0; }
        .card h1 { font-size: 28px; margin: 10px 0 0; }
        .card i { font-size: 20px; margin-bottom: 10px; color: #2563eb; }

        .table-box { background: white; padding: 25px; border-radius: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: right; color: #6b7280; padding: 15px; border-bottom: 2px solid #f3f4f6; }
        td { padding: 15px; border-bottom: 1px solid #f3f4f6; }
        
        body.dark { background: #0f172a; color: white; }
        body.dark .card, body.dark .table-box { background: #1e293b; color: white; }
        body.dark h3 { color: #9ca3af; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo"><strong><i class="fa-solid fa-truck-fast"></i> Fleet Manager</strong></div>
    <div>مرحبًا، <?= $_SESSION['user']; ?> | <a href="logout.php" style="color:#ef4444; text-decoration:none;">خروج</a></div>
</div>

<div class="sidebar">
    <a href="dashboard.php"><i class="fa-solid fa-house"></i> الرئيسية</a>
    <a href="vehicles/add.php"><i class="fa-solid fa-plus"></i> إضافة سيارة</a>
    <a href="vehicles/fleet.php"><i class="fa-solid fa-car"></i> الأسطول</a>
    <a href="vehicles/list.php"><i class="fa-solid fa-list"></i> قائمة المركبات</a>
    <a href="engineers/list.php"><i class="fa-solid fa-user-gear"></i> المهندسين</a>
</div>

<div class="content">
    <button onclick="toggleTheme()" class="theme-toggle" id="themeBtn">
        <i class="fa-solid fa-moon"></i> تفعيل الوضع الليلي
    </button>
    
    <div class="cards">
        <div class="card">
            <i class="fa-solid fa-layer-group"></i> <h3>إجمالي الأسطول</h3>
            <h1><?= $totalCars ?></h1>
        </div>
        <div class="card" style="border-color: #10b981;">
            <i class="fa-solid fa-check-circle"></i> <h3>المركبات الجاهزة</h3>
            <h1><?= $availableCars ?></h1>
        </div>
        <div class="card" style="border-color: #8b5cf6;">
            <i class="fa-solid fa-user-clock"></i> <h3>تحت إدارة المهندسين</h3>
            <h1><?= $assignedCars ?></h1>
        </div>
        <div class="card" style="border-color: #f59e0b;">
            <i class="fa-solid fa-wrench"></i> <h3>في مركز الصيانة</h3>
            <h1><?= $maintenanceCars ?></h1>
        </div>
        <div class="card" style="border-color: #f59e0b;">
            <i class="fa-solid fa-hourglass-half"></i> <h3>رخص قربت تنتهي</h3>
            <h1><?= $soon ?></h1>
        </div>
        <div class="card" style="border-color: #ef4444;">
            <i class="fa-solid fa-triangle-exclamation"></i> <h3>رخص منتهية</h3>
            <h1><?= $expired ?></h1>
        </div>
    </div>

    <div class="table-box">
        <h3>أحدث المركبات المضافة</h3>
        <table>
            <tr><th>الصورة</th><th>المركبة</th><th>الماركة</th><th>تاريخ انتهاء الترخيص</th><th>الحالة</th></tr>
            <?php while($car = $latest->fetch_assoc()) { 
                $days = ceil((strtotime($car['license_end']) - time()) / 86400);
                $status = ($days > 30) ? "سارية" : (($days > 0) ? "توشك على الانتهاء" : "منتهية");
            ?>
            <tr>
                <td><img src="assets/uploads/<?= $car['image'] ?>" style="width:50px; border-radius:10px;"></td>
                <td><?= $car['plate_number'] ?></td>
                <td><?= $car['brand'] ?></td>
                <td><?= $car['license_end'] ?></td>
                <td><?= $status ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>

<script>
    function toggleTheme() {
        document.body.classList.toggle('dark');
        const btn = document.getElementById('themeBtn');
        if(document.body.classList.contains('dark')){
            btn.innerHTML = '<i class="fa-solid fa-sun"></i> تفعيل الوضع النهاري';
        } else {
            btn.innerHTML = '<i class="fa-solid fa-moon"></i> تفعيل الوضع الليلي';
        }
    }
</script>

</body>
</html>