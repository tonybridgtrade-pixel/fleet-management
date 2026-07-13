<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$engineer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. جلب بيانات المهندس
$engineer_query = $conn->query("SELECT * FROM engineers WHERE id = '$engineer_id'");
if(!$engineer_query || $engineer_query->num_rows == 0){
    die("<h3 style='text-align:center; margin-top:50px; font-family:Cairo;'>⚠️ خطأ: لم يتم العثور على المهندس المطلوب.</h3>");
}
$engineer = $engineer_query->fetch_assoc();

$msg = "";
$error = "";

// جلب السيارة الحالية المربوطة بالمهندس عهدة
$current_vehicle = null;
$v_query = $conn->query("SELECT * FROM vehicles WHERE assigned_engineer = '$engineer_id' LIMIT 1");
if($v_query && $v_query->num_rows > 0) {
    $current_vehicle = $v_query->fetch_assoc();
}

// 2. معالجة إضافة تقرير جديد
if(isset($_POST['add_report'])) {
    $report_date  = $_POST['report_date'];
    $start_km     = intval($_POST['start_km']);
    $end_km       = intval($_POST['end_km']);
    $work_km      = intval($_POST['work_km']);
    $fuel_liters  = floatval($_POST['fuel_liters']);
    $fuel_cost    = floatval($_POST['fuel_cost']);
    $notes        = $_POST['notes'];
    
    $vehicle_id   = $current_vehicle ? $current_vehicle['id'] : 0;

    // حسابات تلقائية ذكية
    $total_km     = $end_km - $start_km;
    $personal_km  = $total_km - $work_km;

    if($end_km < $start_km) {
        $error = "⚠️ خطأ: عداد نهاية اليوم لا يمكن أن يكون أقل من عداد البداية!";
    } elseif($work_km > $total_km) {
        $error = "⚠️ خطأ: كيلومترات الشغل لا يمكن أن تكون أكبر من إجمالي المسافة المقطوعة اليوم ($total_km كم)!";
    } else {
        $sql_report = "INSERT INTO daily_reports (engineer_id, vehicle_id, report_date, start_km, end_km, work_km, personal_km, total_km, fuel_liters, fuel_cost, notes) 
                       VALUES ('$engineer_id', '$vehicle_id', '$report_date', '$start_km', '$end_km', '$work_km', '$personal_km', '$total_km', '$fuel_liters', '$fuel_cost', '$notes')";
        
        if($conn->query($sql_report)) {
            // تحديث عداد السيارة الحالي في السيستم تلقائياً
            if($vehicle_id > 0) {
                $conn->query("UPDATE vehicles SET kilometers = '$end_km' WHERE id = '$vehicle_id'");
                $current_vehicle['kilometers'] = $end_km;
            }
            $msg = "✅ تم تسجيل التقرير اليومي بنجاح وتحديث العداد في المنظومة.";
        } else {
            $error = "⚠️ حدث خطأ أثناء الحفظ: " . $conn->error;
        }
    }
}

// 3. بناء فلاتر التقارير والبحث (يومي / شهري / مخصص)
$where_clause = " WHERE engineer_id = '$engineer_id' ";
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to   = isset($_GET['date_to']) ? $_GET['date_to'] : '';

if(!empty($date_from) && !empty($date_to)) {
    $where_clause .= " AND report_date BETWEEN '$date_from' AND '$date_to' ";
}

// 4. جلب إحصائيات التقرير (اللوحة الرقمية للحسابات)
$stats_query = $conn->query("
    SELECT 
        SUM(total_km) as total_kms,
        SUM(work_km) as total_work_kms,
        SUM(personal_km) as total_personal_kms,
        SUM(fuel_liters) as total_liters,
        SUM(fuel_cost) as total_costs
    FROM daily_reports 
    $where_clause
");
$stats = $stats_query->fetch_assoc();

// حساب معدل الاستهلاك العام (كم لكل لتر بنزين)
$avg_consumption = 0;
if($stats['total_liters'] > 0 && $stats['total_kms'] > 0) {
    $avg_consumption = $stats['total_kms'] / $stats['total_liters'];
}

// 5. جلب السجل التفصيلي لعرضه بالجدول
$reports_query = $conn->query("
    SELECT daily_reports.*, vehicles.plate_number, vehicles.brand
    FROM daily_reports
    LEFT JOIN vehicles ON daily_reports.vehicle_id = vehicles.id
    $where_clause
    ORDER BY daily_reports.report_date DESC, daily_reports.id DESC
");
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>الحسابات والتشغيل: <?= $engineer['full_name'] ?></title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');
*{ box-sizing: border-box; font-family: 'Cairo', Arial, sans-serif; }
body{ margin: 0; background: #f8fafc; color: #334155; padding-bottom: 60px; }
.navbar{ background: #0f172a; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
.navbar h2 { margin:0; font-size:18px; }
.navbar a{ color: white; text-decoration: none; background: rgba(255,255,255,0.1); padding: 6px 12px; border-radius: 6px; font-size: 13px; }
.container{ max-width: 1200px; margin: 30px auto; padding: 0 20px; }
.card{ background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); margin-bottom: 20px; border-top: 4px solid #0284c7; }
.card h3 { margin-top: 0; font-size: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 10px; margin-bottom: 15px; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }

/* المربعات الإحصائية الرقمية */
.stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 15px; margin-bottom: 20px; }
.stat-box { background: white; padding: 15px; border-radius: 10px; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 2px 5px rgba(0,0,0,0.01); }
.stat-box h4 { margin: 0; font-size: 13px; color: #64748b; }
.stat-box p { margin: 5px 0 0 0; font-size: 20px; font-weight: 700; color: #0f172a; }

/* فورم الفلترة والإدخال */
.filter-form, .report-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
.form-group { display: flex; flex-direction: column; flex: 1; min-width: 140px; }
label { font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 5px; }
input, select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; }
.btn { padding: 9px 15px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; font-size: 13px; }
.btn-primary { background: #0284c7; color: white; }
.btn-success { background: #10b981; color: white; width: 100%; margin-top: 15px; }
.btn-secondary { background: #64748b; color: white; text-decoration: none; text-align: center; }

.alert{ padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; font-size: 13px; font-weight: 600; }
.alert-success { background: #dcfce7; color: #15803d; }
.alert-danger { background: #fee2e2; color: #b91c1c; }

table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 13px; text-align: right; }
th { background: #f8fafc; color: #0f172a; padding: 10px; border-bottom: 2px solid #cbd5e1; text-align: center; }
td { padding: 10px; border-bottom: 1px solid #e2e8f0; text-align: center; }
tr:hover { background: #f8fafc; }

@media print {
    .navbar, .no-print, .card-form { display: none !important; }
    body { background: white; }
    .card { border: none; box-shadow: none; padding: 0; }
}
</style>
</head>
<body>

<div class="navbar">
    <h2>📊 منظومة تقارير التشغيل وحساب الوقود الذكية</h2>
    <a href="list.php">📋 قائمة المهندسين</a>
</div>

<div class="container">

    <div class="card no-print">
        <h3>🔍 استخراج تقرير تشغيل مخصص (يومي / شهري)</h3>
        <form method="GET" class="filter-form">
            <input type="hidden" name="id" value="<?= $engineer_id ?>">
            <div class="form-group">
                <label>من تاريخ</label>
                <input type="date" name="date_from" value="<?= $date_from ?>" required>
            </div>
            <div class="form-group">
                <label>إلى تاريخ</label>
                <input type="date" name="date_to" value="<?= $date_to ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">📊 عرض التقرير المالي</button>
            <a href="report.php?id=<?= $engineer_id ?>" class="btn btn-secondary">🔄 عرض الكل</a>
            <button onclick="window.print()" type="button" class="btn btn-secondary">🖨️ طباعة المستند</button>
        </form>
    </div>

    <div class="stats-container">
        <div class="stat-box" style="border-top: 3px solid #0284c7;">
            <h4>إجمالي الحركة المقطوعة</h4>
            <p><?= number_format(floatval($stats['total_kms'])) ?> كم</p>
        </div>
        <div class="stat-box" style="border-top: 3px solid #10b981;">
            <h4>مسافة الشغل والعمل</h4>
            <p><?= number_format(floatval($stats['total_work_kms'])) ?> كم</p>
        </div>
        <div class="stat-box" style="border-top: 3px solid #f59e0b;">
            <h4>المشاوير الشخصية</h4>
            <p><?= number_format(floatval($stats['total_personal_kms'])) ?> كم</p>
        </div>
        <div class="stat-box" style="border-top: 3px solid #ec4899;">
            <h4>إجمالي لترات البنزين</h4>
            <p><?= number_format(floatval($stats['total_liters']), 1) ?> لتر</p>
        </div>
        <div class="stat-box" style="border-top: 3px solid #ef4444;">
            <h4>إجمالي تكلفة الوقود</h4>
            <p><?= number_format(floatval($stats['total_costs']), 2) ?> ج.م</p>
        </div>
        <div class="stat-box" style="border-top: 3px solid #8b5cf6;">
            <h4>معدل استهلاك البنزين</h4>
            <p><?= $avg_consumption > 0 ? number_format($avg_consumption, 2) . " كم/لتر" : "0" ?></p>
        </div>
    </div>

    <?php if($msg != "") { ?><div class="alert alert-success"><?= $msg ?></div><?php } ?>
    <?php if($error != "") { ?><div class="alert alert-danger"><?= $error ?></div><?php } ?>

    <?php if($current_vehicle) { ?>
    <div class="card card-form no-print" style="border-top-color: #10b981;">
        <h3>✍️ مدخلات التشغيل اليومي للسيارة</h3>
        <form method="POST">
            <div class="info-grid" style="margin-bottom: 15px; background: #f8fafc; padding: 10px; border-radius: 6px; text-align: right;">
                <div><b>👷 المهندس:</b> <?= $engineer['full_name'] ?></div>
                <div><b>🚗 المركبة المربوطة:</b> <?= $current_vehicle['brand'] ?> (<?= $current_vehicle['plate_number'] ?>)</div>
                <div><b>📟 آخر قراءة بالعداد:</b> <?= number_format($current_vehicle['kilometers']) ?> كم</div>
            </div>
            
            <div class="filter-form">
                <div class="form-group">
                    <label>📅 التاريخ</label>
                    <input type="date" name="report_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label>📟 عداد بداية اليوم (KM)</label>
                    <input type="number" name="start_km" value="<?= $current_vehicle['kilometers'] ?>" required>
                </div>
                <div class="form-group">
                    <label>📟 عداد نهاية اليوم (KM)</label>
                    <input type="number" name="end_km" placeholder="القراءة مساءً..." required>
                </div>
                <div class="form-group">
                    <label>💼 مسافة الشغل والعمل</label>
                    <input type="number" name="work_km" placeholder="كم كيلومتر في الشغل؟" required>
                </div>
                <div class="form-group">
                    <label>⛽ البنزين المستهلك (لتر)</label>
                    <input type="number" step="0.01" name="fuel_liters" value="0" required>
                </div>
                <div class="form-group">
                    <label>💰 إجمالي تكلفة البنزين</label>
                    <input type="number" step="0.01" name="fuel_cost" value="0" required>
                </div>
                <div class="form-group" style="min-width: 100%;">
                    <label>📝 ملاحظات خط السير اليومي</label>
                    <input type="text" name="notes" placeholder="اكتب أي ملاحظة عن مشوار اليوم...">
                </div>
            </div>
            <button type="submit" name="add_report" class="btn btn-success">💾 ترحيل وحساب التشغيل اليومي</button>
        </form>
    </div>
    <?php } else { ?>
        <div class="alert alert-danger no-print">⚠️ لا يمكن تسجيل حركة جديدة؛ المهندس ليس بعهدته سيارة في الوقت الحالي.</div>
    <?php } ?>

    <div class="card">
        <h3>📜 السجل الحسابي للتشغيل وحرق الوقود</h3>
        <?php if($reports_query && $reports_query->num_rows > 0) { ?>
            <table>
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>عداد البدء</th>
                        <th>عداد النهاية</th>
                        <th>الإجمالي المقطوع</th>
                        <th>مسافة العمل</th>
                        <th>مسافة شخصي</th>
                        <th>الوقود (لتر)</th>
                        <th>التكلفة اليومية</th>
                        <th style="background: #fdf2f8; color: #9d174d; font-weight: bold;">معدل الاستهلاك اليومي</th>
                        <th style="background: #f0fdf4; color: #166534; font-weight: bold;">تكلفة الكيلومتر الواحد</th>
                        <th>الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $reports_query->fetch_assoc()) { 
                        // حسابات الصف الحالي (اليومي) داخلياً
                        $day_total_km = floatval($row['total_km']);
                        $day_fuel_liters = floatval($row['fuel_liters']);
                        $day_fuel_cost = floatval($row['fuel_cost']);

                        // 1. معدل استهلاك البنزين لليوم (كم / لتر)
                        $day_avg_consumption = ($day_fuel_liters > 0 && $day_total_km > 0) ? ($day_total_km / $day_fuel_liters) : 0;

                        // 2. تكلفة الكيلومتر الواحد لليوم (جنيه / كم)
                        $day_km_cost = ($day_total_km > 0 && $day_fuel_cost > 0) ? ($day_fuel_cost / $day_total_km) : 0;
                    ?>
                        <tr>
                            <td><b><?= $row['report_date'] ?></b></td>
                            <td><?= number_format($row['start_km']) ?></td>
                            <td><?= number_format($row['end_km']) ?></td>
                            <td style="font-weight: 600; color:#0284c7;"><?= number_format($day_total_km) ?> كم</td>
                            <td style="color:#10b981; font-weight: 600;"><?= number_format($row['work_km']) ?> كم</td>
                            <td style="color:#f59e0b;"><?= number_format($row['personal_km']) ?> كم</td>
                            <td><?= number_format($day_fuel_liters, 2) ?> لتر</td>
                            <td style="font-weight: bold; color:#ef4444;"><?= number_format($day_fuel_cost, 2) ?> ج.م</td>
                            
                            <td style="background: #fff5f5; font-weight: bold; color: #9d174d;">
                                <?= $day_avg_consumption > 0 ? number_format($day_avg_consumption, 2) . " كم/لتر" : "0" ?>
                            </td>
                            
                            <td style="background: #f6fdf9; font-weight: bold; color: #166534;">
                                <?= $day_km_cost > 0 ? number_format($day_km_cost, 2) . " ج.م/كم" : "0" ?>
                            </td>

                            <td><small><?= htmlspecialchars($row['notes']) ?></small></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p style="text-align:center; color:#94a3b8; margin:20px 0;">📭 لا توجد سجلات تشغيل في الفترة المحددة بالبحث.</p>
        <?php } ?>
    </div>

</div>

</body>
</html>