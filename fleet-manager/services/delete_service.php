```php id="d9k3m1"
<?php
session_start();
include "../config/db.php";

if(!isset($_GET['id'])){
    header("Location: services_list.php");
    exit;
}

$id = $_GET['id'];

$service = $conn->query("
SELECT vehicle_services.*, vehicles.plate_number
FROM vehicle_services
LEFT JOIN vehicles
ON vehicle_services.vehicle_id = vehicles.id
WHERE vehicle_services.id='$id'
")->fetch_assoc();

if(!$service){
    header("Location: services_list.php");
    exit;
}

if(isset($_POST['delete'])){

    $conn->query("
    DELETE FROM vehicle_services
    WHERE id='$id'
    ");

    header("Location: services_list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>حذف الصيانة</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    direction:rtl;
}

.container{
    width:100%;
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card{
    background:white;
    width:500px;
    padding:30px;
    border-radius:15px;
    box-shadow:0 2px 15px rgba(0,0,0,0.1);
    text-align:center;
}

.icon{
    font-size:70px;
    margin-bottom:15px;
}

h2{
    color:#dc2626;
    margin-bottom:10px;
}

.info{
    background:#f3f4f6;
    padding:15px;
    border-radius:10px;
    margin-top:20px;
    margin-bottom:25px;
    text-align:right;
    line-height:2;
}

.buttons{
    display:flex;
    gap:15px;
    justify-content:center;
}

.delete-btn{
    background:#dc2626;
    color:white;
    border:none;
    padding:14px 25px;
    border-radius:10px;
    cursor:pointer;
    font-size:15px;
    transition:0.3s;
}

.delete-btn:hover{
    background:#b91c1c;
}

.cancel-btn{
    background:#111827;
    color:white;
    text-decoration:none;
    padding:14px 25px;
    border-radius:10px;
    transition:0.3s;
}

.cancel-btn:hover{
    background:#007bff;
}

</style>
</head>
<body>

<div class="container">

    <div class="card">

        <div class="icon">
            ⚠
        </div>

        <h2>تأكيد حذف الصيانة</h2>

        <p>
            هل أنت متأكد من حذف عملية الصيانة دي؟
        </p>

        <div class="info">

            <strong>رقم العربية:</strong>
            <?= $service['plate_number'] ?>

            <br>

            <strong>نوع الصيانة:</strong>
            <?= $service['service_type'] ?>

            <br>

            <strong>التكلفة:</strong>
            <?= $service['cost'] ?> ج

            <br>

            <strong>التاريخ:</strong>
            <?= $service['service_date'] ?>

        </div>

        <div class="buttons">

            <form method="POST">

                <button type="submit"
                name="delete"
                class="delete-btn">

                    🗑 تأكيد الحذف

                </button>

            </form>

            <a class="cancel-btn"
            href="services_list.php">

                ⬅ رجوع

            </a>

        </div>

    </div>

</div>

</body>
</html>
```
