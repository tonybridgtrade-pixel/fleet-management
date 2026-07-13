<?php
session_start();
include "../config/db.php";

$msg = "";
$current_vehicle_id = isset($_GET['vehicle_id']) ? $_GET['vehicle_id'] : '';

// جلب اسم العربية المختارة للعرض
$selected_vehicle_name = "لم يتم اختيار عربية";
if($current_vehicle_id) {
    $v_res = $conn->query("SELECT plate_number, brand FROM vehicles WHERE id='$current_vehicle_id'");
    if($v_res && $row = $v_res->fetch_assoc()) {
        $selected_vehicle_name = $row['brand'] . " - " . $row['plate_number'];
    }
}

if(isset($_POST['save'])){
    $vehicle_id      = $_POST['vehicle_id']; // القيمة هتيجي من الـ input المخفي
    $service_type    = $_POST['service_type'];
    $cost            = $_POST['cost'];
    $current_km      = $_POST['current_km'];
    $service_date    = $_POST['service_date'];
    $status          = $_POST['status'];
    // ... باقي المتغيرات
    
    $sql = "INSERT INTO vehicle_services (vehicle_id, service_type, cost, current_km, service_date, status) 
            VALUES ('$vehicle_id', '$service_type', '$cost', '$current_km', '$service_date', '$status')";

    if($conn->query($sql)){
        $conn->query("UPDATE vehicles SET kilometers='$current_km', vehicle_status='".($status == 'Completed' ? 'متاحة' : 'في الصيانة')."' WHERE id='$vehicle_id'");
        $msg = "تم حفظ الصيانة وتحديث حالة العربية بنجاح!";
    } else {
        $msg = "خطأ: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة صيانة</title>
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f8fafc; padding: 20px; }
        .container { background: #fff; padding: 30px; border-radius: 20px; width: 600px; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-top: 6px solid #2563eb; }
        h2 { text-align: center; color: #1e293b; }
        .vehicle-banner { background: #eff6ff; padding: 15px; border-radius: 12px; text-align: center; font-weight: bold; color: #1e40af; margin-bottom: 20px; border: 1px solid #bfdbfe; }
        label { font-weight: bold; color: #475569; display: block; margin-top: 15px; }
        input, textarea, select { width: 100%; padding: 12px; margin-top: 5px; border: 1px solid #e2e8f0; border-radius: 8px; box-sizing: border-box; }
        button { background: #2563eb; color: white; border: none; padding: 15px; width: 100%; border-radius: 10px; font-weight: bold; margin-top: 20px; cursor: pointer; }
        .msg { background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛠 إضافة صيانة جديدة</h2>
    
    <div class="vehicle-banner">
        العربية الحالية: <?= $selected_vehicle_name ?>
    </div>

    <?php if($msg != ""){ ?><div class="msg"><?= $msg ?></div><?php } ?>

    <form method="POST">
        <input type="hidden" name="vehicle_id" value="<?= $current_vehicle_id ?>">

        <label>نوع الصيانة</label>
        <select name="service_type">
            <option>تغيير زيت</option><option>فرامل</option><option>بطارية</option><option>عفشة</option>
            <option>كاوتش</option><option>كهرباء</option><option>موتور</option>
        </select>

        <label>التكلفة</label>
        <input type="number" name="cost" required>

        <label>العداد الحالي (KM)</label>
        <input type="number" name="current_km" required>

        <label>تاريخ الصيانة</label>
        <input type="date" name="service_date" value="<?= date('Y-m-d') ?>" required>

        <label>الحالة</label>
        <select name="status">
            <option value="In Progress">في الصيانة</option>
            <option value="Completed">تم الانتهاء</option>
        </select>

        <button type="submit" name="save">حفظ الصيانة</button>
    </form>

    <a href="../vehicles/fleet.php" style="display:block; text-align:center; margin-top:20px; color:#64748b; text-decoration:none;">⬅ العودة للقائمة</a>
</div>
</body>
</html>