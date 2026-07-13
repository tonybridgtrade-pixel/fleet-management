<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

if(!isset($_GET['id'])){
    header("Location: list.php");
    exit;
}

$id = intval($_GET['id']);

$result = $conn->query("
SELECT vehicles.*, engineers.full_name
FROM vehicles
LEFT JOIN engineers
ON vehicles.assigned_engineer = engineers.id
WHERE vehicles.id = $id
");

if($result->num_rows == 0){
    die("العربية غير موجودة");
}

$car = $result->fetch_assoc();

$today = time();
$end = strtotime($car['license_end']);

$days = ceil(($end - $today) / 86400);

if($days > 30){

    $status = "سارية";
    $class = "green";
}
elseif($days > 0){

    $status = "قربت تنتهي";
    $class = "orange";
}
else{

    $status = "منتهية";
    $class = "red";
}

/* حالة التأمين */

$insuranceEnd = strtotime($car['insurance_end']);

if(!empty($car['insurance_end'])){

    $insuranceDays = ceil(($insuranceEnd - time()) / 86400);

    if($insuranceDays > 30){

        $insuranceStatus = "ساري";
        $insuranceClass = "green";

    }elseif($insuranceDays > 0){

        $insuranceStatus = "قرب ينتهي";
        $insuranceClass = "orange";

    }else{

        $insuranceStatus = "منتهي";
        $insuranceClass = "red";

    }

}else{

    $insuranceStatus = "لا يوجد";
    $insuranceClass = "red";

}

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>تفاصيل العربية</title>

<style>

*{
    box-sizing:border-box;
}

body{
    margin:0;
    padding:30px;
    font-family:Arial;
    background:#f4f6f9;
}

.container{
    max-width:1100px;
    margin:auto;
}

.card{
    background:white;
    border-radius:20px;
    overflow:hidden;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
}

.top-image{
    width:100%;
    height:380px;
    object-fit:cover;
}

.content{
    padding:35px;
}

.title{
    font-size:34px;
    margin-bottom:30px;
    color:#111827;
    font-weight:bold;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:20px;
}

.info-box{
    background:#f9fafb;
    padding:22px;
    border-radius:14px;
    border:1px solid #eee;
    transition:0.3s;
}

.info-box:hover{
    transform:translateY(-3px);
    box-shadow:0 5px 15px rgba(0,0,0,0.06);
}

.info-box h3{
    margin:0 0 12px;
    color:#666;
    font-size:16px;
}

.info-box p{
    margin:0;
    font-size:22px;
    font-weight:bold;
    color:#111827;
    word-break:break-word;
}

.status{
    padding:8px 14px;
    border-radius:30px;
    color:white;
    font-size:14px;
    font-weight:bold;
    display:inline-block;
}

.green{
    background:#16a34a;
}

.orange{
    background:#f59e0b;
}

.red{
    background:#dc2626;
}

.actions{
    margin-top:35px;
}

.btn{
    display:inline-block;
    padding:12px 20px;
    border-radius:10px;
    text-decoration:none;
    color:white;
    margin-right:10px;
    transition:0.3s;
}

.btn:hover{
    opacity:0.9;
}

.back{
    background:#007bff;
}

.edit{
    background:#16a34a;
}

.phone{
    direction:ltr;
}

.notes{
    margin-top:25px;
    background:#f9fafb;
    padding:25px;
    border-radius:14px;
    border:1px solid #eee;
}

.notes h3{
    margin-top:0;
    color:#444;
}

.notes p{
    line-height:1.8;
    color:#333;
    font-size:17px;
}

.no-image{
    width:100%;
    height:380px;
    background:#e5e7eb;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:30px;
    color:#6b7280;
    font-weight:bold;
}

</style>

</head>
<body>

<div class="container">

    <div class="card">

        <?php if($car['image'] != "") { ?>

            <img class="top-image"
            src="../assets/uploads/<?php echo $car['image']; ?>">

        <?php } else { ?>

            <div class="no-image">
                لا توجد صورة
            </div>

        <?php } ?>

        <div class="content">

            <div class="title">
                <?php echo $car['brand']; ?>
                -
                <?php echo $car['model']; ?>
            </div>

            <div class="grid">

                <div class="info-box">
                    <h3>رقم العربية</h3>
                    <p><?php echo $car['plate_number']; ?></p>
                </div>

                <div class="info-box">
                    <h3>الماركة</h3>
                    <p><?php echo $car['brand']; ?></p>
                </div>

                <div class="info-box">
                    <h3>الموديل</h3>
                    <p><?php echo $car['model']; ?></p>
                </div>

                <div class="info-box">
                    <h3>اللون</h3>
                    <p>
                        <?php
                        echo $car['color'] != ""
                        ? $car['color']
                        : "غير محدد";
                        ?>
                    </p>
                </div>

                <div class="info-box">
                    <h3>سنة الصنع</h3>
                    <p>
                        <?php
                        echo $car['manufacture_year'] != ""
                        ? $car['manufacture_year']
                        : "-";
                        ?>
                    </p>
                </div>

                <div class="info-box">
    <h3>المهندس</h3>
    <p>
        <?= $car['full_name'] ?: "بدون"; ?>
    </p>
</div>

                <div class="info-box">
                    <h3>رقم التليفون</h3>
                    <p class="phone">
                        <?php
                        echo $car['phone'] != ""
                        ? $car['phone']
                        : "-";
                        ?>
                    </p>
                </div>
<div class="info-box">
    <h3>نوع الملكية</h3>
    <p>
        <?= $car['ownership_type'] ?: "غير محدد"; ?>
    </p>
</div>

<div class="info-box">
    <h3>رقم الشاسيه</h3>
    <p>
        <?= $car['chassis_number'] ?: "-"; ?>
    </p>
</div>

<div class="info-box">
    <h3>رقم الماتور</h3>
    <p>
        <?= $car['engine_number'] ?: "-"; ?>
    </p>
</div>

<div class="info-box">
    <h3>رقم بوليصة التأمين</h3>
    <p>
        <?= $car['insurance_policy'] ?: "-"; ?>
    </p>
</div>

<div class="info-box">
    <h3>رقم التأمين</h3>
    <p>
        <?= $car['insurance_number'] ?: "-"; ?>
    </p>
</div>

<div class="info-box">
    <h3>تاريخ انتهاء التأمين</h3>
    <p>
        <?= $car['insurance_end'] ?: "-"; ?>
    </p>
</div>

<div class="info-box">
    <h3>حالة التأمين</h3>

    <span class="status <?= $insuranceClass ?>">
        <?= $insuranceStatus ?>
    </span>
</div>

<div class="info-box">
    <h3>حالة العربية</h3>
    <p>
        <?= $car['vehicle_status'] ?: "-"; ?>
    </p>
</div>

<div class="info-box">
    <h3>تاريخ انتهاء الإيجار</h3>
    <p>
        <?= ($car['rent_end_date'] && $car['rent_end_date']!="0000-00-00") ? $car['rent_end_date'] : "-"; ?>
    </p>
</div>
                <div class="info-box">
                    <h3>تاريخ انتهاء الرخصة</h3>
                    <p><?php echo $car['license_end']; ?></p>
                </div>

                <div class="info-box">
                    <h3>الأيام المتبقية</h3>
                    <p>

                    <?php

                    if($days > 0){
                        echo $days . " يوم";
                    }
                    else{
                        echo "منتهية";
                    }

                    ?>

                    </p>
                </div>

                <div class="info-box">
                    <h3>الحالة</h3>

                    <span class="status <?php echo $class; ?>">
                        <?php echo $status; ?>
                    </span>
                </div>

            </div>

            <div class="notes">

                <h3>الملاحظات</h3>

                <p>

                <?php

                if($car['notes'] != ""){
                    echo nl2br($car['notes']);
                }
                else{
                    echo "لا توجد ملاحظات";
                }

                ?>

                </p>

            </div>

            <div class="actions">

                <a class="btn back"
                href="list.php">

                    رجوع

                </a>

                <a class="btn edit"
                href="edit.php?id=<?php echo $car['id']; ?>">

                    تعديل

                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>