```php id="u4v9p1"
<?php
session_start();
include "../config/db.php";

$id = $_GET['id'];

$service = $conn->query("
SELECT * FROM vehicle_services
WHERE id='$id'
")->fetch_assoc();

$msg = "";

if(isset($_POST['update'])){

    $service_type      = $_POST['service_type'];
    $problem           = $_POST['problem'];
    $changed_parts     = $_POST['changed_parts'];
    $cost              = $_POST['cost'];
    $current_km        = $_POST['current_km'];
    $next_service_km   = $_POST['next_service_km'];
    $service_date      = $_POST['service_date'];
    $next_service_date = $_POST['next_service_date'];
    $technician        = $_POST['technician'];
    $status            = $_POST['status'];
    $notes             = $_POST['notes'];

    $update = $conn->query("

    UPDATE vehicle_services SET

    service_type='$service_type',
    problem='$problem',
    changed_parts='$changed_parts',
    cost='$cost',
    current_km='$current_km',
    next_service_km='$next_service_km',
    service_date='$service_date',
    next_service_date='$next_service_date',
    technician='$technician',
    status='$status',
    notes='$notes'

    WHERE id='$id'

    ");

    if($update){

        $msg = "تم تعديل الصيانة بنجاح";

        $service = $conn->query("
        SELECT * FROM vehicle_services
        WHERE id='$id'
        ")->fetch_assoc();

    }else{

        $msg = "حدث خطأ";

    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تعديل الصيانة</title>

<style>

body{
    font-family:Arial;
    direction:rtl;
    background:#f5f5f5;
    padding:20px;
}

.container{
    background:#fff;
    padding:25px;
    border-radius:12px;
    width:700px;
    margin:auto;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

input,
textarea,
select{
    width:100%;
    padding:12px;
    margin-top:10px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:8px;
    box-sizing:border-box;
}

button{
    background:#f59e0b;
    color:white;
    border:none;
    padding:14px;
    width:100%;
    border-radius:8px;
    font-size:16px;
    cursor:pointer;
}

button:hover{
    opacity:0.9;
}

.msg{
    background:green;
    color:white;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
}

.back-btn-box{
    text-align:center;
    margin-top:25px;
}

.back-btn{
    display:inline-block;
    background:#111827;
    color:white;
    padding:12px 25px;
    border-radius:10px;
    text-decoration:none;
    transition:0.3s;
    font-size:15px;
    font-weight:bold;
}

.back-btn:hover{
    background:#007bff;
}

</style>
</head>
<body>

<div class="container">

<h2>✏ تعديل الصيانة</h2>

<?php if($msg != ""){ ?>
<div class="msg"><?= $msg ?></div>
<?php } ?>

<form method="POST">

<label>نوع الصيانة</label>

<select name="service_type">

<option <?= $service['service_type']=="تغيير زيت" ? "selected" : "" ?>>
تغيير زيت
</option>

<option <?= $service['service_type']=="فرامل" ? "selected" : "" ?>>
فرامل
</option>

<option <?= $service['service_type']=="بطارية" ? "selected" : "" ?>>
بطارية
</option>

<option <?= $service['service_type']=="عفشة" ? "selected" : "" ?>>
عفشة
</option>

<option <?= $service['service_type']=="كاوتش" ? "selected" : "" ?>>
كاوتش
</option>

<option <?= $service['service_type']=="كهرباء" ? "selected" : "" ?>>
كهرباء
</option>

</select>

<label>وصف المشكلة</label>
<textarea name="problem"><?= $service['problem'] ?></textarea>

<label>القطع المتغيرة</label>
<textarea name="changed_parts"><?= $service['changed_parts'] ?></textarea>

<label>التكلفة</label>
<input type="number"
name="cost"
value="<?= $service['cost'] ?>">

<label>العداد الحالي</label>
<input type="number"
name="current_km"
value="<?= $service['current_km'] ?>">

<label>الصيانة القادمة عند</label>
<input type="number"
name="next_service_km"
value="<?= $service['next_service_km'] ?>">

<label>تاريخ الصيانة</label>
<input type="date"
name="service_date"
value="<?= $service['service_date'] ?>">

<label>تاريخ الصيانة القادمة</label>
<input type="date"
name="next_service_date"
value="<?= $service['next_service_date'] ?>">

<label>الفني</label>
<input type="text"
name="technician"
value="<?= $service['technician'] ?>">

<label>الحالة</label>

<select name="status">

<option value="Pending"
<?= $service['status']=="Pending" ? "selected" : "" ?>>
Pending
</option>

<option value="In Progress"
<?= $service['status']=="In Progress" ? "selected" : "" ?>>
In Progress
</option>

<option value="Completed"
<?= $service['status']=="Completed" ? "selected" : "" ?>>
Completed
</option>

</select>

<label>ملاحظات</label>
<textarea name="notes"><?= $service['notes'] ?></textarea>

<button type="submit" name="update">
    حفظ التعديلات
</button>

</form>

<div class="back-btn-box">

    <a class="back-btn" href="services_list.php">
        ⬅ رجوع لسجل الصيانة
    </a>

</div>

</div>

</body>
</html>
```
