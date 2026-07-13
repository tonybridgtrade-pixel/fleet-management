<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$msg = "";
$error = "";

// لقط معرّف المهندس لو جاي محول من صفحة كارت المهندس مباشرة
$target_engineer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if(isset($_POST['return'])){

    $assignment_id = intval($_POST['assignment_id']);
    $return_date = $_POST['return_date'];
    $end_km = intval($_POST['end_km']);

    /* جلب بيانات عملية التسليم الحالية للتأكد */
    $assignment_query = $conn->query("SELECT * FROM vehicle_assignments WHERE id='$assignment_id'");
    
    if($assignment_query && $assignment_query->num_rows > 0) {
        $assignment = $assignment_query->fetch_assoc();
        $vehicle_id = $assignment['vehicle_id'];
        $engineer_id = $assignment['engineer_id'];
        $start_km = intval($assignment['start_km']);

        // شرط أمان: منع إدخال عداد أقل من عداد البدء
        if($end_km < $start_km) {
            $error = "⚠️ خطأ: العداد الحالي ($end_km) لا يمكن أن يكون أقل من عداد التسليم ($start_km) KM.";
        } else {
            
            /* 1. تحديث عملية التسليم وإغلاقها بتسجيل التاريخ والعداد النهائي بنجاح */
            $conn->query("UPDATE vehicle_assignments SET return_date='$return_date', end_km='$end_km', assignment_status='منتهية' WHERE id='$assignment_id'");

            /* 2. تحديث بيانات المركبة وإتاحتها من جديد */
            $conn->query("UPDATE vehicles SET assigned_engineer=NULL, vehicle_status='متاحة', kilometers='$end_km' WHERE id='$vehicle_id'");

            /* 3. تحديث حالة المهندس */
            $conn->query("UPDATE engineers SET status='بدون سيارة' WHERE id='$engineer_id'");

            $msg = "✅ تم استرجاع العربية بنجاح وتحديث عداد المركبة إلى " . number_format($end_km) . " كم.";
            
            // إعادة توجيه شيك بعد ثانيتين لصفحة المهندسين
            echo "<script>
                setTimeout(function(){
                    window.location.href = '../engineers/list.php';
                }, 2000);
            </script>";
        }
    } else {
        $error = "⚠️ حدث خطأ: لم يتم العثور على عملية التسليم النشطة.";
    }
}

/* جلب قائمة المهندسين اللي عندهم عهد نشطة حالياً (لقائمة الاختيار) */
$all_active_engineers = $conn->query("
    SELECT engineers.id, engineers.full_name 
    FROM vehicle_assignments 
    LEFT JOIN engineers ON vehicle_assignments.engineer_id = engineers.id 
    WHERE vehicle_assignments.assignment_status='نشطة'
    GROUP BY engineers.id
");

/* جلب العملية المستهدفة فقط للعهدة النشطة */
$query_str = "
SELECT vehicle_assignments.*,
vehicles.plate_number,
vehicles.brand,
vehicles.model,
engineers.full_name,
engineers.phone,
engineers.department,
engineers.national_id

FROM vehicle_assignments
LEFT JOIN vehicles ON vehicle_assignments.vehicle_id = vehicles.id
LEFT JOIN engineers ON vehicle_assignments.engineer_id = engineers.id
WHERE vehicle_assignments.assignment_status='نشطة'
";

if($target_engineer_id > 0) {
    $query_str .= " AND vehicle_assignments.engineer_id = '$target_engineer_id' ";
} else {
    if(isset($_GET['select_eng']) && intval($_GET['select_eng']) > 0) {
        $sel_id = intval($_GET['select_eng']);
        $query_str .= " AND vehicle_assignments.engineer_id = '$sel_id' ";
    }
}

$query_str .= " GROUP BY vehicle_assignments.id ORDER BY vehicle_assignments.id DESC LIMIT 1";
$assignments = $conn->query($query_str);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إيصال استرجاع مركبة للعهدة</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

*{
    box-sizing: border-box;
    font-family: 'Cairo', Arial, sans-serif;
}

body{
    margin: 0;
    background: #f8fafc;
    color: #334155;
}

.navbar{
    background: #0f172a;
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.navbar h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
}

.navbar a{
    color: white;
    text-decoration: none;
    background: rgba(255,255,255,0.1);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}
.navbar a:hover { background: rgba(255,255,255,0.2); }

.container{
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
}

.action-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 15px;
}

h2.page-title {
    font-size: 24px;
    color: #0f172a;
    margin: 0;
    font-weight: 700;
    white-space: nowrap;
}

.filter-box {
    display: flex;
    align-items: center;
    gap: 10px;
    background: white;
    padding: 6px 12px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
}

.filter-box select {
    padding: 6px 12px;
    border: none;
    font-weight: 600;
    outline: none;
    background: transparent;
    font-size: 14px;
    color: #0f172a;
    cursor: pointer;
}

.print-btn {
    background: #475569;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: 0.2s;
}
.print-btn:hover { background: #1e293b; }

.alert{
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
}
.alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

.card{
    background: white;
    padding: 35px;
    border-radius: 16px;
    margin-bottom: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border-top: 6px solid #ef4444;
}

.card h3{
    margin-top: 0;
    margin-bottom: 25px;
    color: #0f172a;
    font-size: 20px;
    font-weight: 700;
    text-align: center;
    border-bottom: 1px dashed #cbd5e1;
    padding-bottom: 15px;
}

.section-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 12px;
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.info-grid p{
    margin: 0;
    font-size: 14px;
    color: #475569;
}

.info-grid b { color: #0f172a; }

label{
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #334155;
    font-size: 14px;
    margin-top: 20px;
}

input{
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 10px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    box-sizing: border-box;
    font-size: 14px;
    background: #fff;
    color: #0f172a;
    transition: 0.2s;
}

input:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

button.submit-btn{
    width: 100%;
    background: #ef4444;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-size: 15px;
    margin-top: 20px;
    transition: all 0.3s ease;
}

button.submit-btn:hover{
    background: #dc2626;
    transform: translateY(-1px);
}

.print-signatures {
    display: none;
    margin-top: 50px;
    justify-content: space-between;
    padding: 0 20px;
}
.signature-lane {
    text-align: center;
    width: 200px;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    border-top: 1px solid #334155;
    padding-top: 10px;
}

.empty{
    background: white;
    padding: 40px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    color: #94a3b8;
    font-weight: 600;
    font-size: 16px;
}

@media print {
    body { background: white; color: black; }
    .navbar, .print-btn, .submit-btn, label, input, h2.page-title, .filter-box { display: none !important; }
    .container { max-width: 100%; padding: 0; margin: 0; }
    .card { box-shadow: none !important; border: none !important; padding: 0 !important; margin: 0 !important; }
    .card h3 { font-size: 24px; margin-bottom: 30px; color: black; }
    .info-grid { background: transparent !important; border: 1px solid #000 !important; padding: 15px; }
    .info-grid p { color: black !important; font-size: 16px; }
    .print-signatures { display: flex !important; }
}
</style>
</head>
<body>

<div class="navbar">
    <h2>🔄 نظام استرجاع المركبات للعهدة</h2>
    <a href="../engineers/list.php">قائمة المهندسين</a>
</div>

<div class="container">

    <div class="action-bar">
        <h2 class="page-title">🚘 محضر إغلاق العهدة</h2>
        
        <?php if($target_engineer_id == 0 && $all_active_engineers && $all_active_engineers->num_rows > 0) { ?>
            <div class="filter-box">
                <label style="margin:0; font-size:13px;">عرض عهدة المهندس:</label>
                <select onchange="location = this.value;">
                    <option value="return_vehicle.php">اختر المهندس...</option>
                    <?php while($eng = $all_active_engineers->fetch_assoc()) { 
                        $selected = (isset($_GET['select_eng']) && $_GET['select_eng'] == $eng['id']) ? 'selected' : '';
                    ?>
                        <option value="return_vehicle.php?select_eng=<?= $eng['id'] ?>" <?= $selected ?>><?= $eng['full_name'] ?></option>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>

        <?php if($assignments && $assignments->num_rows > 0){ ?>
            <button onclick="window.print()" class="print-btn">🖨️ طباعة محضر الاسترجاع</button>
        <?php } ?>
    </div>

    <?php if($msg != ""){ ?>
        <div class="alert alert-success"><?= $msg ?></div>
    <?php } ?>

    <?php if($error != ""){ ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php } ?>

    <?php if($assignments && $assignments->num_rows > 0){ ?>
        
        <?php 
        while($row = $assignments->fetch_assoc()) { 
        ?>
        
        <div class="card">
            <h3>📋 محضر استلام واسترجاع سيارة من مهندس</h3>
            
            <div class="section-title">👤 بيانات المهندس المستلم:</div>
            <div class="info-grid">
                <p><b>👷 اسم المهندس:</b> <?= $row['full_name'] ?></p>
                <p><b>🪪 الرقم القومي:</b> <?= !empty($row['national_id']) ? $row['national_id'] : 'غير مسجل' ?></p>
                <p><b>📞 رقم الموبايل:</b> <?= !empty($row['phone']) ? $row['phone'] : 'غير مسجل' ?></p>
                <p><b>🏢 القسم التابع له:</b> <?= !empty($row['department']) ? $row['department'] : 'عام' ?></p>
            </div>

            <div class="section-title">🚗 بيانات المركبة والعهدة النشطة:</div>
            <div class="info-grid">
                <p><b>رقم اللوحة:</b> <span style="font-weight:700; color:#ef4444;"><?= $row['plate_number'] ?></span></p>
                <p><b>الماركة والموديل:</b> <?= $row['brand'] ?> (<?= isset($row['model']) ? $row['model'] : '-' ?>)</p>
                <p><b>📅 تاريخ بدء التسليم:</b> <?= $row['assign_date'] ?></p>
                <p><b>📟 عداد الحركة البدئي:</b> <span style="color:#10b981; font-weight:700;"><?= number_format($row['start_km']) ?> KM</span></p>
            </div>

            <form method="POST">
                <input type="hidden" name="assignment_id" value="<?= $row['id'] ?>">

                <label>📅 تاريخ الاسترجاع الفعلي للشركة</label>
                <input type="date" name="return_date" value="<?= date('Y-m-d') ?>" required>

                <label>📟 قراءة العداد الحالي عند الاستلام النهائي (KM)</label>
                <input type="number" name="end_km" placeholder="أدخل قراءة العداد الحالية المسجلة بالسيارة..." min="<?= $row['start_km'] ?>" required>

                <button type="submit" name="return" class="submit-btn">
                    🔄 تأكيد استرجاع السيارة وإتاحتها بالسيستم
                </button>
            </form>

            <div class="print-signatures">
                <div class="signature-lane" style="border: none; text-align: right;">تاريخ الاسترجاع الفعلي: <?= date('Y-m-d') ?></div>
                <div class="signature-lane">توقيع المهندس المسترد</div>
                <div class="signature-lane">توقيع المستلم (الحركة)</div>
            </div>
        </div>
        
        <?php 
            // قفل الأمان المطلق: اقطع اللوب واخرج فوراً بعد عرض أول كارت
            break; 
        } 
        ?>

    <?php } else { ?>
        <div class="empty">
            📭 لا توجد سيارات مستلمة نشطة حالياً مطابقة لهذا البحث.
        </div>
    <?php } ?>

</div>

</body>
</html>