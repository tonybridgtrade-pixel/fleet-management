
<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

/* حذف الصيانة */

if(isset($_GET['delete'])){

    $delete_id = $_GET['delete'];

    $conn->query("
    DELETE FROM vehicle_services
    WHERE id='$delete_id'
    ");

    header("Location: services_list.php");
    exit;
}

/* عرض الصيانات */

$services = $conn->query("
SELECT vehicle_services.*, vehicles.plate_number
FROM vehicle_services
LEFT JOIN vehicles
ON vehicle_services.vehicle_id = vehicles.id
ORDER BY vehicle_services.id DESC
");

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>سجل الصيانة</title>

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f9;
    direction:rtl;
}

.container{
    padding:25px;
}

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:20px;
}

.top-bar a{
    background:#007bff;
    color:white;
    padding:12px 18px;
    text-decoration:none;
    border-radius:8px;
}

.table-box{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.08);
}

table{
    width:100%;
    border-collapse:collapse;
}

table th,
table td{
    padding:14px;
    border-bottom:1px solid #ddd;
    text-align:center;
}

table th{
    background:#111827;
    color:white;
}

.status{
    padding:6px 12px;
    border-radius:5px;
    color:white;
    font-size:13px;
}

.pending{
    background:orange;
}

.progress{
    background:#007bff;
}

.completed{
    background:green;
}

.edit-btn{
    background:#f59e0b;
    color:white;
    padding:8px 12px;
    text-decoration:none;
    border-radius:5px;
}

.delete-btn{
    background:#dc2626;
    color:white;
    padding:8px 12px;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

/* popup */

.popup-overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.5);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.popup-card{
    background:white;
    width:350px;
    padding:25px;
    border-radius:15px;
    text-align:center;
    animation:popup 0.3s ease;
}

@keyframes popup{

    from{
        transform:scale(0.8);
        opacity:0;
    }

    to{
        transform:scale(1);
        opacity:1;
    }
}

.popup-card h3{
    color:#dc2626;
    margin-bottom:15px;
}

.popup-buttons{
    margin-top:20px;
    display:flex;
    justify-content:center;
    gap:10px;
}

.confirm-delete{
    background:#dc2626;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    text-decoration:none;
}

.cancel-delete{
    background:#111827;
    color:white;
    padding:10px 18px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}

</style>

</head>
<body>

<div class="container">

    <div class="top-bar">

        <h2>🛠 سجل الصيانة</h2>

        <a href="add_service.php">
            + إضافة صيانة
        </a>

    </div>

    <div class="table-box">

        <table>

            <tr>

                <th>#</th>
                <th>رقم العربية</th>
                <th>نوع الصيانة</th>
                <th>التكلفة</th>
                <th>العداد</th>
                <th>الصيانة القادمة</th>
                <th>التاريخ</th>
                <th>الحالة</th>
                <th>تعديل</th>
                <th>حذف</th>

            </tr>

            <?php while($row = $services->fetch_assoc()) { ?>

            <?php

            if($row['status'] == "Pending"){
                $class = "pending";
            }
            elseif($row['status'] == "In Progress"){
                $class = "progress";
            }
            else{
                $class = "completed";
            }

            ?>

            <tr>

                <td><?= $row['id'] ?></td>

                <td><?= $row['plate_number'] ?></td>

                <td><?= $row['service_type'] ?></td>

                <td><?= $row['cost'] ?> ج</td>

                <td><?= $row['current_km'] ?> KM</td>

                <td><?= $row['next_service_km'] ?> KM</td>

                <td><?= $row['service_date'] ?></td>

                <td>
                    <span class="status <?= $class ?>">
                        <?= $row['status'] ?>
                    </span>
                </td>

                <td>

                    <a class="edit-btn"
                    href="edit_service.php?id=<?= $row['id'] ?>">

                        تعديل

                    </a>

                </td>

                <td>

                    <button class="delete-btn"
                    onclick="openDeletePopup(<?= $row['id'] ?>)">

                        حذف

                    </button>

                </td>

            </tr>

            <?php } ?>

        </table>

    </div>

</div>

<!-- popup -->

<div class="popup-overlay" id="deletePopup">

    <div class="popup-card">

        <h3>⚠ تأكيد الحذف</h3>

        <p>
            هل تريد حذف الصيانة؟
        </p>

        <div class="popup-buttons">

            <a href=""
            id="confirmDeleteBtn"
            class="confirm-delete">

                🗑 حذف

            </a>

            <button class="cancel-delete"
            onclick="closeDeletePopup()">

                إلغاء

            </button>

        </div>

    </div>

</div>

<script>

function openDeletePopup(id){

    document.getElementById("deletePopup").style.display = "flex";

    document.getElementById("confirmDeleteBtn").href =
    "services_list.php?delete=" + id;
}

function closeDeletePopup(){

    document.getElementById("deletePopup").style.display = "none";
}

</script>

</body>
</html>
```
