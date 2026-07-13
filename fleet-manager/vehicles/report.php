<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../login.php"); exit; }
include "../config/db.php";

$id = $_GET['id'];

// 1. جلب بيانات السيارة الأساسية
$vehicle = $conn->query("SELECT * FROM vehicles WHERE id='$id'")->fetch_assoc();

if (!$vehicle) die("السيارة غير موجودة");

// 2. جلب الحركات (الربط الصحيح باستخدام vehicle_id من جدول الحركات)
$movements = $conn->query("SELECT * FROM vehicle_movements WHERE vehicle_id='$id' ORDER BY created_at DESC");

// 3. جلب خدمات الصيانة
$services = $conn->query("SELECT * FROM vehicle_services WHERE vehicle_id='$id' ORDER BY service_date DESC");

// 4. الحسابات المالية (معدلة لتعتمد على الـ ID)
$data = $conn->query("
    SELECT 
        (SELECT IFNULL(SUM(cost), 0) FROM vehicle_services WHERE vehicle_id='$id') as total_services,
        (SELECT IFNULL(SUM(fuel_cost), 0) FROM vehicle_movements WHERE vehicle_id='$id') as total_fuel,
        (SELECT IFNULL(SUM(other_expenses), 0) FROM vehicle_movements WHERE vehicle_id='$id') as total_others
")->fetch_assoc();

$grand_total = $data['total_services'] + $data['total_fuel'] + $data['total_others'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تقرير السيارة: <?= $vehicle['plate_number'] ?></title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&display=swap');
        body { font-family: 'Cairo', sans-serif; background: #f1f5f9; padding: 20px; }
        .container { max-width: 1200px; margin: auto; }
        .card { background: white; padding: 25px; border-radius: 20px; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .stat-box { background: #0f172a; color: white; padding: 20px; border-radius: 16px; text-align: center; }
        .stat-val { display: block; font-size: 20px; font-weight: bold; color: #38bdf8; margin-top: 5px; }
        .grand-total { background: #dc2626 !important; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #f8fafc; color: #64748b; padding: 12px; border-bottom: 2px solid #e2e8f0; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; text-align: center; }
        .btn { padding: 12px 25px; border-radius: 12px; text-decoration: none; font-weight: bold; color: white; background: #64748b; border: none; cursor: pointer; }
    </style>
</head>
<body>

<div class="container">
    <div style="margin-bottom:20px;">
        <a href="../vehicles/fleet.php" class="btn">⬅ رجوع للأسطول</a>
        <button class="btn" style="background:#059669;" onclick="window.print()">🖨 طباعة التقرير</button>
    </div>

    <div class="card">
        <h2>🚗 بيانات السيارة</h2>
        <div class="stats-grid">
            <p><b>السيارة:</b> <?= $vehicle['brand'] ?> | <?= $vehicle['plate_number'] ?></p>
            <p><b>حالة السيارة:</b> <?= $vehicle['vehicle_status'] ?></p>
        </div>
    </div>

    <div class="card">
        <h2>💰 سجل المصاريف الشامل</h2>
        <div class="stats-grid">
            <div class="stat-box">إجمالي الصيانة <span class="stat-val"><?= number_format($data['total_services']) ?> ج.م</span></div>
            <div class="stat-box">إجمالي الوقود <span class="stat-val"><?= number_format($data['total_fuel']) ?> ج.م</span></div>
            <div class="stat-box">مصاريف جانبية <span class="stat-val"><?= number_format($data['total_others']) ?> ج.م</span></div>
            <div class="stat-box grand-total">التكلفة الإجمالية <span class="stat-val"><?= number_format($grand_total) ?> ج.م</span></div>
        </div>
    </div>

    <div class="card">
        <h2>📊 سجل الحركة والوقود</h2>
        <table>
            <tr><th>التاريخ</th><th>الشهر</th><th>المسافة</th><th>الوقود (لتر)</th><th>تكلفة الوقود</th><th>مصاريف أخرى</th></tr>
            <?php 
            if($movements->num_rows > 0) {
                while($m = $movements->fetch_assoc()) { ?>
                <tr>
                    <td><?= $m['created_at'] ?></td>
                    <td><?= $m['month_name'] ?></td>
                    <td><?= $m['total_distance'] ?> كم</td>
                    <td><?= $m['fuel_liters'] ?> لتر</td>
                    <td><?= number_format($m['fuel_cost']) ?> ج</td>
                    <td><?= number_format($m['other_expenses']) ?> ج</td>
                </tr>
            <?php } 
            } else { echo "<tr><td colspan='6'>لا توجد بيانات حركة مسجلة لهذه السيارة</td></tr>"; } ?>
        </table>
    </div>

    <div class="card">
        <h2>🛠 سجل الصيانة الكامل</h2>
        <table>
            <tr><th>التاريخ</th><th>نوع الصيانة</th><th>القطع المبدلة</th><th>التكلفة</th><th>ملاحظات</th></tr>
            <?php while($s = $services->fetch_assoc()) { ?>
            <tr>
                <td><?= $s['service_date'] ?></td>
                <td><?= $s['service_type'] ?></td>
                <td><?= $s['changed_parts'] ?></td>
                <td><?= number_format($s['cost']) ?> ج</td>
                <td><?= $s['notes'] ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
</div>
</body>
</html>