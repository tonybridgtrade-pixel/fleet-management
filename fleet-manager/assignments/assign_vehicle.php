<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../login.php"); exit; }
include "../config/db.php";

$msg = ""; $msg_class = "success";
$selected_engineer_id = isset($_GET['engineer_id']) ? intval($_GET['engineer_id']) : 0;
$fixed_engineer_name = "";

if($selected_engineer_id > 0) {
    $eng_check = $conn->query("SELECT full_name FROM engineers WHERE id='$selected_engineer_id'");
    if($eng_check && $eng_check->num_rows > 0) {
        $eng_data = $eng_check->fetch_assoc();
        $fixed_engineer_name = $eng_data['full_name'];
    }
}

if(isset($_POST['save'])){
    $vehicle_id = $_POST['vehicle_id'];
    $engineer_id = ($selected_engineer_id > 0) ? $selected_engineer_id : $_POST['engineer_id'];
    $assign_date = $_POST['assign_date'];
    $start_km = $_POST['start_km'];
    $notes = $_POST['notes'];

    $sql = "INSERT INTO vehicle_assignments (vehicle_id, engineer_id, assign_date, start_km, assignment_status, notes)
            VALUES ('$vehicle_id', '$engineer_id', '$assign_date', '$start_km', 'نشطة', '$notes')";

    if($conn->query($sql)){
        $conn->query("UPDATE vehicles SET assigned_engineer='$engineer_id', vehicle_status='مع مهندس', kilometers='$start_km' WHERE id='$vehicle_id'");
        $conn->query("UPDATE engineers SET status='مستلم سيارة' WHERE id='$engineer_id'");
        $msg = "✅ تم تسليم المركبة بنجاح.";
        header("refresh:2; url=../engineers/list.php");
    } else {
        $msg = "❌ خطأ: " . $conn->error; $msg_class = "error";
    }
}

$vehicles = $conn->query("SELECT * FROM vehicles WHERE vehicle_status='متاحة'");
$engineers = $conn->query("SELECT * FROM engineers WHERE status='بدون سيارة'");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>محضر تسليم عهدة سيارة</title>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');
    body { background: #f1f5f9; padding: 20px; font-family: 'Cairo', sans-serif; }
    .container { max-width: 800px; margin: auto; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    
    h2 { text-align: center; color: #0f172a; border-bottom: 2px solid #2563eb; padding-bottom: 10px; margin-bottom: 30px; }
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .form-group { margin-bottom: 15px; }
    label { font-weight: 700; color: #475569; display: block; margin-bottom: 5px; }
    input, select, textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #f8fafc; }
    
    .declaration { border: 2px dashed #94a3b8; padding: 20px; margin: 30px 0; background: #fdfdfd; line-height: 1.8; text-align: justify; }
    
    .signatures { display: flex; justify-content: space-between; margin-top: 50px; }
    .sig-box { width: 40%; border-top: 2px solid #000; text-align: center; padding-top: 10px; font-weight: bold; }

    .no-print { display: flex; flex-direction: column; gap: 10px; margin-top: 30px; }
    .btn-row { display: flex; gap: 10px; }
    button { flex: 1; padding: 15px; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; }
    .btn-save { background: #2563eb; color: white; }
    .btn-print { background: #059669; color: white; }
    .btn-back { background: #64748b; color: white; text-decoration: none; text-align: center; padding: 15px; border-radius: 8px; font-weight: bold; }

    /* تنسيق الطباعة الاحترافي */
    @media print {
        @page { margin: 1cm; }
        body { background: white; }
        .container { box-shadow: none; border: none; padding: 0; }
        .no-print { display: none !important; }
        /* إخفاء خلفيات الحقول وإظهار خط تحتها فقط */
        input, select, textarea { border: none !important; border-bottom: 1px solid #000 !important; background: transparent !important; }
        .declaration { border: 1px solid #000 !important; }
    }
</style>
</head>
<body>

<div class="container">
    <h2>📋 محضر تسليم عهدة مركبة</h2>
    <?php if($msg) echo "<div style='text-align:center; padding:10px; color:green; font-weight:bold;'>$msg</div>"; ?>

    <form method="POST">
        <div class="form-grid">
            <div class="form-group">
                <label>المركبة</label>
                <select name="vehicle_id" required>
                    <option value="">اختر المركبة...</option>
                    <?php while($v = $vehicles->fetch_assoc()) echo "<option value='{$v['id']}'>{$v['plate_number']} - {$v['brand']}</option>"; ?>
                </select>
            </div>
            <div class="form-group">
                <label>المهندس المستلم</label>
                <?php if($selected_engineer_id > 0) { ?>
                    <input type="text" value="<?= $fixed_engineer_name ?>" readonly>
                    <input type="hidden" name="engineer_id" value="<?= $selected_engineer_id ?>">
                <?php } else { ?>
                    <select name="engineer_id" required>
                        <option value="">اختر المهندس...</option>
                        <?php while($e = $engineers->fetch_assoc()) echo "<option value='{$e['id']}'>{$e['full_name']}</option>"; ?>
                    </select>
                <?php } ?>
            </div>
            <div class="form-group">
                <label>تاريخ التسليم (يوم/شهر/سنة)</label>
                <input type="text" name="assign_date" value="<?= date('d/m/Y') ?>" 
                       onfocus="(this.type='date')" onblur="(this.type='text')" required>
            </div>
            <div class="form-group">
                <label>عداد الكيلومترات</label>
                <input type="number" name="start_km" placeholder="أدخل القراءة الحالية" required>
            </div>
        </div>

        <div class="form-group">
            <label>ملاحظات الحالة الفنية</label>
            <textarea name="notes" rows="2" placeholder="اذكر أي ملاحظات عن حالة السيارة..."></textarea>
        </div>

        <div class="declaration">
            <strong>إقرار استلام:</strong><br>
            أقر أنا المهندس الموقع أدناه باستلامي السيارة المذكورة أعلاه بحالتها الفنية الحالية، 
            وأتعهد بالحفاظ عليها واتباع الصيانة الدورية، وأتحمل المسؤولية الكاملة عن أي تقصير أو إهمال 
            يؤدي إلى تلفيات بالسيارة أو مخالفات مرورية أثناء فترة عهدتي.
        </div>

        <div class="signatures">
            <div class="sig-box">توقيع المهندس المستلم</div>
            <div class="sig-box">توقيع مسؤول المخزن</div>
        </div>

        <div class="no-print">
            <div class="btn-row">
                <button type="submit" name="save" class="btn-save">💾 حفظ في النظام</button>
                <button type="button" class="btn-print" onclick="window.print()">🖨️ طباعة المحضر</button>
            </div>
            <a href="../engineers/list.php" class="btn-back">⬅️ رجوع لقائمة المهندسين</a>
        </div>
    </form>
</div>
</body>
</html>