<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../login.php"); exit; }
include "../config/db.php";

// --- الفلاتر الذكية ---
$filter = "";
if(isset($_GET['status'])){
    if($_GET['status'] == "متاحة") {
        $filter = "WHERE vehicles.assigned_engineer IS NULL OR vehicles.assigned_engineer = 0";
    } elseif($_GET['status'] == "مع مهندس") {
        $filter = "WHERE vehicles.assigned_engineer > 0";
    } else {
        $filter = "WHERE vehicles.vehicle_status='".$_GET['status']."'";
    }
}
if(isset($_GET['license'])){
    if($_GET['license'] == "soon") $filter = "WHERE vehicles.license_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
    if($_GET['license'] == "expired") $filter = "WHERE vehicles.license_end < CURDATE()";
}
if(isset($_GET['ownership'])) {
    $filter = "WHERE vehicles.ownership_type='".$_GET['ownership']."'";
}

// --- الإحصائيات المحدثة ---
$stats = [
    'total'       => $conn->query("SELECT COUNT(*) as total FROM vehicles")->fetch_assoc()['total'],
    'available'   => $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE assigned_engineer IS NULL OR assigned_engineer = 0")->fetch_assoc()['total'],
    'assigned'    => $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE assigned_engineer > 0")->fetch_assoc()['total'],
    'maintenance' => $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE vehicle_status='في الصيانة'")->fetch_assoc()['total'],
    'soon'        => $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE license_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)")->fetch_assoc()['total'],
    'expired'     => $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE license_end < CURDATE()")->fetch_assoc()['total'],
    'owned'       => $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE ownership_type='ملك'")->fetch_assoc()['total'],
    'rent'        => $conn->query("SELECT COUNT(*) as total FROM vehicles WHERE ownership_type='إيجار'")->fetch_assoc()['total']
];

$vehicles = $conn->query("SELECT vehicles.*, engineers.full_name FROM vehicles LEFT JOIN engineers ON vehicles.assigned_engineer = engineers.id $filter ORDER BY vehicles.id DESC");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة الأسطول - التحكم الذكي</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
        body { font-family: 'Cairo', sans-serif; background: #f1f5f9; margin: 0; padding: 20px; }
        .container { max-width: 1400px; margin: auto; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .back-btn { background: #475569; color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: bold; }
        
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 30px; }
        .stat-card { color: white; padding: 15px; border-radius: 12px; text-align: center; text-decoration: none; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-size: 13px; font-weight: bold; }
        .stat-card:hover { transform: translateY(-5px); opacity: 0.9; }
        
        .fleet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .fleet-card { background: white; border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-right: 8px solid #cbd5e1; transition: 0.3s; }
        
        .status-available { border-right-color: #16a34a; }
        .status-assigned { border-right-color: #2563eb; }
        .status-maintenance { border-right-color: #dc2626; }
        
        .logo-box { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .logo-box img { width: 45px; height: 45px; object-fit: contain; }
        .btns { display: flex; gap: 8px; margin-top: 15px; }
        .btn { flex: 1; padding: 10px; border-radius: 8px; text-align: center; text-decoration: none; font-size: 13px; font-weight: bold; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="header-bar">
        <h2>🚗 إدارة الأسطول (النظام الذكي)</h2>
        <a href="../dashboard.php" class="back-btn">⬅ لوحة التحكم</a>
    </div>

    <div class="stats">
        <a href="?" class="stat-card" style="background:#1e293b;">إجمالي<br><h2><?= $stats['total'] ?></h2></a>
        <a href="?status=متاحة" class="stat-card" style="background:#16a34a;">متاحة<br><h2><?= $stats['available'] ?></h2></a>
        <a href="?status=مع مهندس" class="stat-card" style="background:#2563eb;">مع مهندس<br><h2><?= $stats['assigned'] ?></h2></a>
        <a href="?status=في الصيانة" class="stat-card" style="background:#dc2626;">صيانة<br><h2><?= $stats['maintenance'] ?></h2></a>
        <a href="?license=soon" class="stat-card" style="background:#f59e0b;">رخص قريبة<br><h2><?= $stats['soon'] ?></h2></a>
        <a href="?license=expired" class="stat-card" style="background:#b91c1c;">رخص منتهية<br><h2><?= $stats['expired'] ?></h2></a>
        <a href="?ownership=ملك" class="stat-card" style="background:#7c3aed;">ملك<br><h2><?= $stats['owned'] ?></h2></a>
        <a href="?ownership=إيجار" class="stat-card" style="background:#d97706;">إيجار<br><h2><?= $stats['rent'] ?></h2></a>
    </div>

    <div class="fleet-grid">
    <?php while($car = $vehicles->fetch_assoc()) { 
        $status_class = "status-available";
        if($car['vehicle_status'] == "في الصيانة") $status_class = "status-maintenance";
        elseif($car['assigned_engineer'] > 0) $status_class = "status-assigned";
    ?>
        <div class="fleet-card <?= $status_class ?>">
            <div class="logo-box">
                <img src="https://cdn.simpleicons.org/<?= strtolower(str_replace(' ', '', $car['brand'])) ?>" onerror="this.src='https://cdn-icons-png.flaticon.com/512/3774/3774278.png'">
                <a href="vehicle_details.php?id=<?= $car['id'] ?>" style="font-weight:bold; color:#1e293b; text-decoration:none; font-size:18px;"><?= $car['brand'] ?></a>
            </div>
            <p><b>اللوحة:</b> <?= $car['plate_number'] ?></p>
            <p><b>الحالة:</b> <?= $car['vehicle_status'] ?></p>
            <p><b>المهندس:</b> <?= $car['full_name'] ?: '---' ?></p>
            <div class="btns">
                <a href="report.php?id=<?= $car['id'] ?>" class="btn" style="background:#1e293b;">تقرير</a>
                <a href="../services/add_service.php?vehicle_id=<?= $car['id'] ?>" class="btn" style="background:#dc2626;">صيانة</a>
                <a href="../movement/add_movement.php?vehicle_id=<?= $car['id'] ?>" class="btn" style="background:#16a34a;">حركة</a>
            </div>
        </div>
    <?php } ?>
    </div>
</div>
</body>
</html>