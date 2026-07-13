<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$msg = "";

/* =========================
   جلب العربية المختارة
========================= */

$selected_vehicle = null;

if(isset($_GET['vehicle_id'])){

    $vehicle_id = $_GET['vehicle_id'];

    $vehicleQuery = $conn->query("
    SELECT vehicles.*, engineers.full_name
    FROM vehicles

    LEFT JOIN engineers
    ON vehicles.assigned_engineer = engineers.id

    WHERE vehicles.id='$vehicle_id'
    ");

    $selected_vehicle = $vehicleQuery->fetch_assoc();
}

/* =========================
   حفظ الحركة
========================= */

if(isset($_POST['save'])){

    $vehicle_id      = $_POST['vehicle_id'];

    $engineer_id =
    !empty($_POST['engineer_id'])
    ? $_POST['engineer_id']
    : "NULL";

    $month_name      = $_POST['month_name'];

    $start_km        = $_POST['start_km'];
    $end_km          = $_POST['end_km'];

    $fuel_liters     = $_POST['fuel_liters'];
    $fuel_cost       = $_POST['fuel_cost'];

    $other_expenses  = $_POST['other_expenses'];

    $notes           = $_POST['notes'];

    /* الحسابات */

    $total_distance = $end_km - $start_km;

    if($fuel_liters > 0){

        $fuel_average = $total_distance / $fuel_liters;

    }else{

        $fuel_average = 0;

    }

    if($total_distance > 0){

        $cost_per_km =
        ($fuel_cost + $other_expenses)
        / $total_distance;

    }else{

        $cost_per_km = 0;

    }

    /* الحفظ */

    $sql = "INSERT INTO vehicle_movements
    (
        vehicle_id,
        engineer_id,
        month_name,
        start_km,
        end_km,
        total_distance,
        fuel_liters,
        fuel_cost,
        fuel_average,
        cost_per_km,
        other_expenses,
        notes
    )

    VALUES
    (
        '$vehicle_id',
        $engineer_id,
        '$month_name',
        '$start_km',
        '$end_km',
        '$total_distance',
        '$fuel_liters',
        '$fuel_cost',
        '$fuel_average',
        '$cost_per_km',
        '$other_expenses',
        '$notes'
    )";

    if($conn->query($sql)){

        /* تحديث عداد العربية */

        $conn->query("
        UPDATE vehicles
        SET kilometers='$end_km'
        WHERE id='$vehicle_id'
        ");

        $msg = "✅ تم حفظ الحركة بنجاح";

    }else{

        $msg = "❌ حدث خطأ : " . $conn->error;

    }
}

/* =========================
   كل العربيات
========================= */

$allVehicles = $conn->query("
SELECT *
FROM vehicles
ORDER BY id DESC
");

/* =========================
   المهندسين
========================= */

$engineers = $conn->query("
SELECT *
FROM engineers
ORDER BY full_name ASC
");

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>إضافة حركة شهرية</title>

<style>

body{
    font-family:Arial;
    direction:rtl;
    background:#f5f5f5;
    padding:20px;
}

.container{
    width:750px;
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
    background:#16a34a;
    color:white;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    text-align:center;
}

.calc-box{
    background:#f4f6f9;
    padding:15px;
    border-radius:10px;
    margin-top:20px;
    line-height:2;
    font-size:17px;
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

.back-btn:hover{
    background:#007bff;
}

.vehicle-name{
    background:#111827;
    color:white;
    padding:15px;
    border-radius:10px;
    margin-bottom:20px;
    text-align:center;
    font-size:20px;
}

</style>

</head>
<body>

<div class="container">

<h2>📊 إضافة حركة شهرية</h2>

<?php if($msg != ""){ ?>

<div class="msg">

<?= $msg ?>

</div>

<?php } ?>

<?php if($selected_vehicle){ ?>

<div class="vehicle-name">

🚘 العربية الحاليه :

<?= $selected_vehicle['brand'] ?>

-

<?= $selected_vehicle['plate_number'] ?>

<?php if($selected_vehicle['full_name']){ ?>

<br><br>

👷 المهندس الحالي :

<?= $selected_vehicle['full_name'] ?>

<?php } ?>

</div>

<?php } ?>

<form method="POST">

<label>الشهر</label>

<input type="text"
name="month_name"
placeholder="مثال : July 2026"
required>

<!-- لو داخل من زرار الحركة -->

<?php if($selected_vehicle){ ?>

<input type="hidden"
name="vehicle_id"
value="<?= $selected_vehicle['id'] ?>">

<label>العربية</label>

<input type="text"
value="<?= $selected_vehicle['brand'] ?> - <?= $selected_vehicle['plate_number'] ?>"
readonly>

<?php } else { ?>

<label>العربية</label>

<select name="vehicle_id" required>

<option value="">اختر العربية</option>

<?php while($v = $allVehicles->fetch_assoc()) { ?>

<option value="<?= $v['id'] ?>">

<?= $v['brand'] ?>

-

<?= $v['plate_number'] ?>

</option>

<?php } ?>

</select>

<?php } ?>

<label>المهندس (اختياري)</label>

<select name="engineer_id">

<option value="">بدون مهندس</option>

<?php while($e = $engineers->fetch_assoc()) { ?>

<option value="<?= $e['id'] ?>"

<?php
if(
$selected_vehicle &&
$selected_vehicle['assigned_engineer'] == $e['id']
){
    echo "selected";
}
?>

>

<?= $e['full_name'] ?>

</option>

<?php } ?>

</select>

<label>عداد أول الشهر</label>

<input type="number"
name="start_km"
id="start_km"
required>

<label>عداد آخر الشهر</label>

<input type="number"
name="end_km"
id="end_km"
required>

<label>عدد لترات الوقود</label>

<input type="number"
step="0.01"
name="fuel_liters"
id="fuel_liters"
required>

<label>تكلفة الوقود</label>

<input type="number"
step="0.01"
name="fuel_cost"
id="fuel_cost"
required>

<label>مصاريف إضافية</label>

<input type="number"
step="0.01"
name="other_expenses"
id="other_expenses"
value="0">

<label>ملاحظات</label>

<textarea name="notes"></textarea>

<div class="calc-box">

<b>📌 الحسابات التلقائية</b>

<br><br>

🚗 المسافة:
<span id="distance">0</span>
KM

<br>

⛽ معدل الاستهلاك:
<span id="average">0</span>
KM/L

<br>

💰 تكلفة الكيلو:
<span id="costkm">0</span>
ج

</div>

<br>

<button type="submit" name="save">

حفظ الحركة

</button>

</form>

<div class="back-btn-box">

<a class="back-btn"
href="../dashboard.php">

⬅ رجوع للوحة التحكم

</a>

</div>

</div>

<script>

function calculate(){

    let start =
    parseFloat(document.getElementById("start_km").value) || 0;

    let end =
    parseFloat(document.getElementById("end_km").value) || 0;

    let liters =
    parseFloat(document.getElementById("fuel_liters").value) || 0;

    let fuelCost =
    parseFloat(document.getElementById("fuel_cost").value) || 0;

    let other =
    parseFloat(document.getElementById("other_expenses").value) || 0;

    let distance = end - start;

    if(distance < 0){

        distance = 0;

    }

    let average = 0;

    if(liters > 0){

        average = distance / liters;

    }

    let costkm = 0;

    if(distance > 0){

        costkm =
        (fuelCost + other)
        / distance;

    }

    document.getElementById("distance")
    .innerHTML = distance.toFixed(2);

    document.getElementById("average")
    .innerHTML = average.toFixed(2);

    document.getElementById("costkm")
    .innerHTML = costkm.toFixed(2);
}

document.querySelectorAll("input").forEach(input=>{

    input.addEventListener("input",calculate);

});

</script>

</body>
</html>