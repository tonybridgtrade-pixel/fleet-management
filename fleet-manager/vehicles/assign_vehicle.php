```php id="c9n5x7"
<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$msg = "";

if(isset($_POST['save'])){

    $vehicle_id  = $_POST['vehicle_id'];
    $engineer_id = $_POST['engineer_id'];
    $start_km    = $_POST['start_km'];
    $assign_date = $_POST['assign_date'];
    $notes       = $_POST['notes'];

    /* حفظ التسليم */

    $sql = "INSERT INTO vehicle_assignments
    (
        vehicle_id,
        engineer_id,
        start_km,
        assign_date,
        notes
    )

    VALUES
    (
        '$vehicle_id',
        '$engineer_id',
        '$start_km',
        '$assign_date',
        '$notes'
    )";

    if($conn->query($sql)){

        /* تحديث العربية */

        $conn->query("
        UPDATE vehicles
        SET
        vehicle_status='مع مهندس',
        assigned_engineer='$engineer_id'
        WHERE id='$vehicle_id'
        ");

        /* تحديث المهندس */

        $conn->query("
        UPDATE engineers
        SET status='مستلم سيارة'
        WHERE id='$engineer_id'
        ");

        $msg = "تم تسليم العربية بنجاح";

    }else{

        $msg = "حدث خطأ : " . $conn->error;

    }
}

/* العربيات المتاحة فقط */

$vehicles = $conn->query("
SELECT *
FROM vehicles
WHERE vehicle_status='متاحة'
");

/* المهندسين بدون سيارة */

$engineers = $conn->query("
SELECT *
FROM engineers
WHERE status='بدون سيارة'
");

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تسليم عربية</title>

<style>

body{
    font-family:Arial;
    direction:rtl;
    background:#f5f5f5;
    padding:20px;
}

.container{
    width:700px;
    margin:auto;
    background:white;
    padding:25px;
    border-radius:12px;
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
    width:100%;
    background:#007bff;
    color:white;
    border:none;
    padding:14px;
    border-radius:8px;
    cursor:pointer;
    font-size:16px;
}

button:hover{
    background:#0056b3;
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
}

</style>

</head>
<body>

<div class="container">

<h2>🚗 تسليم عربية لمهندس</h2>

<?php if($msg != ""){ ?>
<div class="msg"><?= $msg ?></div>
<?php } ?>

<form method="POST">

<label>اختار العربية</label>

<select name="vehicle_id" required>

<option value="">اختر العربية</option>

<?php while($v = $vehicles->fetch_assoc()) { ?>

<option value="<?= $v['id'] ?>">

    <?= $v['plate_number'] ?>
    -
    <?= $v['brand'] ?>

</option>

<?php } ?>

</select>

<label>اختار المهندس</label>

<select name="engineer_id" required>

<option value="">اختر المهندس</option>

<?php while($e = $engineers->fetch_assoc()) { ?>

<option value="<?= $e['id'] ?>">

    <?= $e['full_name'] ?>

</option>

<?php } ?>

</select>

<label>عداد البداية</label>
<input type="number" name="start_km" required>

<label>تاريخ التسليم</label>
<input type="date" name="assign_date" required>

<label>ملاحظات</label>
<textarea name="notes"></textarea>

<button type="submit" name="save">
    حفظ التسليم
</button>

</form>

<div class="back-btn-box">

    <a class="back-btn"
    href="../dashboard.php">

        ⬅ رجوع للوحة التحكم

    </a>

</div>

</div>

</body>
</html>
```
