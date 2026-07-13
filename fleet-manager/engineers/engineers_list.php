```php id="x2m7q9"
<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

/* حذف مهندس */

if(isset($_GET['delete'])){

    $delete_id = $_GET['delete'];

    $conn->query("
    DELETE FROM engineers
    WHERE id='$delete_id'
    ");

    header("Location: engineers_list.php");
    exit;
}

/* البحث */

$search = "";

if(isset($_GET['search'])){
    $search = $_GET['search'];
}

/* عرض المهندسين */

$engineers = $conn->query("

SELECT *
FROM engineers

WHERE
full_name LIKE '%$search%'
OR phone LIKE '%$search%'
OR department LIKE '%$search%'

ORDER BY id DESC

");

?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>المهندسين</title>

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
    gap:15px;
}

.add-btn{
    background:#007bff;
    color:white;
    padding:12px 18px;
    text-decoration:none;
    border-radius:8px;
}

.search-box{
    display:flex;
    gap:10px;
}

.search-box input{
    padding:12px;
    width:250px;
    border:1px solid #ccc;
    border-radius:8px;
}

.search-box button{
    background:#111827;
    color:white;
    border:none;
    padding:12px 18px;
    border-radius:8px;
    cursor:pointer;
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

.free{
    background:#dc2626;
}

.assigned{
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

    <div class="top-bar">

        <a class="add-btn"
        href="add_engineer.php">

            + إضافة مهندس

        </a>

        <form class="search-box" method="GET">

            <input type="text"
            name="search"
            placeholder="بحث عن مهندس..."
            value="<?= $search ?>">

            <button type="submit">
                بحث
            </button>

        </form>

    </div>

    <div class="table-box">

        <table>

            <tr>

                <th>#</th>
                <th>الاسم</th>
                <th>الموبايل</th>
                <th>القسم</th>
                <th>الوظيفة</th>
                <th>المرتب</th>
                <th>الحالة</th>
                <th>تعديل</th>
                <th>حذف</th>

            </tr>

            <?php while($row = $engineers->fetch_assoc()) { ?>

            <?php

            if($row['status'] == "بدون سيارة"){
                $class = "free";
            }else{
                $class = "assigned";
            }

            ?>

            <tr>

                <td><?= $row['id'] ?></td>

                <td><?= $row['full_name'] ?></td>

                <td><?= $row['phone'] ?></td>

                <td><?= $row['department'] ?></td>

                <td><?= $row['job_title'] ?></td>

                <td><?= $row['salary'] ?> ج</td>

                <td>

                    <span class="status <?= $class ?>">
                        <?= $row['status'] ?>
                    </span>

                </td>

                <td>

                    <a class="edit-btn"
                    href="edit_engineer.php?id=<?= $row['id'] ?>">

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

    <div class="back-btn-box">

        <a class="back-btn"
        href="../dashboard.php">

            ⬅ رجوع للوحة التحكم

        </a>

    </div>

</div>

<!-- popup -->

<div class="popup-overlay" id="deletePopup">

    <div class="popup-card">

        <h3>⚠ تأكيد الحذف</h3>

        <p>
            هل تريد حذف المهندس؟
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
    "engineers_list.php?delete=" + id;
}

function closeDeletePopup(){

    document.getElementById("deletePopup").style.display = "none";
}

</script>

</body>
</html>
```
