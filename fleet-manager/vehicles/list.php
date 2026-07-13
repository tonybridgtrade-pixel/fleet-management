<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

/* الفلترة */
if(isset($_GET['status'])){
    if($_GET['status'] == "expired"){
        $vehicles = $conn->query("
        SELECT * FROM vehicles
        WHERE license_end < CURDATE()
        ORDER BY id DESC
        ");
        $pageTitle = "العربيات ذات الرخص المنتهية";
    }
    elseif($_GET['status'] == "soon"){
        $vehicles = $conn->query("
        SELECT * FROM vehicles
        WHERE license_end BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        ORDER BY id DESC
        ");
        $pageTitle = "العربيات ذات الرخص القريبة من الانتهاء";
    }
} else {
    $vehicles = $conn->query("
    SELECT * FROM vehicles
    ORDER BY id DESC
    ");
    $pageTitle = "كل العربيات في النظام";
}

$count = $vehicles->num_rows;
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $pageTitle; ?></title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

*{
    box-sizing: border-box;
    font-family: 'Cairo', Arial, sans-serif;
}

body{
    background: #f8fafc;
    padding: 30px 20px;
    margin: 0;
    color: #334155;
}

.container{
    max-width: 1400px;
    margin: 0 auto;
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

/* Header Section */
.top{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 20px;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 20px;
}

.top-left {
    display: flex;
    gap: 12px;
}

.top-left a{
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    transition: all 0.3s ease;
}

.btn-add { background: #2563eb; color: white; }
.btn-add:hover { background: #1d4ed8; transform: translateY(-2px); }

.btn-dashboard { background: #64748b; color: white; }
.btn-dashboard:hover { background: #475569; transform: translateY(-2px); }

.page-title{
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
}

/* Search Box */
.search-box input{
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    outline: none;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.search-box input:focus{
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

/* Modern Table Layout */
.table-box{
    overflow-x: auto;
    margin-top: 20px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}

table{
    width: 100%;
    border-collapse: collapse;
    min-width: 1600px;
}

table th, table td{
    padding: 16px;
    text-align: center;
    font-size: 14px;
}

table th{
    background: #0f172a;
    color: white;
    font-weight: 600;
}

table tr{
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.2s ease;
}

table tr:hover{
    background: #f1f5f9;
}

.car-img{
    width: 80px;
    height: 55px;
    object-fit: cover;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.badge{
    display: inline-block;
    padding: 6px 12px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 12px;
}

.badge-gray { background: #e2e8f0; color: #334155; }
.badge-info { background: #e0f2fe; color: #0369a1; }

.status{ font-size: 12px; font-weight: 700; }
.green{ background: #dcfce7; color: #15803d; }
.orange{ background: #fef3c7; color: #b45309; }
.red{ background: #fee2e2; color: #b91c1c; }

/* تنسيق أزرار التحكم لتصبح في سطر واحد */
table td:last-child {
    white-space: nowrap; /* يمنع نزول أي زرار في سطر جديد */
    display: flex;
    justify-content: center;
    gap: 6px; /* مسافة متناسقة بين كل زرار والتاني */
    border-bottom: none; /* عشان يحافظ على شكل الجدول */
}

.btn{
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    color: white;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.2s ease;
}

.btn:hover{ transform: scale(1.05); }
.view{ background: #3b82f6; }
.edit{ background: #10b981; }
.delete{ background: #ef4444; }

.empty-box{
    background: #fffbeb;
    border-right: 4px solid #f59e0b;
    color: #b45309;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 25px;
    font-weight: 600;
}

.popup-overlay{
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 999;
}

.popup-box{
    background: white;
    width: 380px;
    padding: 30px;
    border-radius: 16px;
    text-align: center;
}

.popup-buttons{ display: flex; justify-content: center; gap: 10px; margin-top: 20px;}
.confirm-btn{ background: #ef4444; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;}
.cancel-btn{ background: #e2e8f0; color: #334155; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600;}
</style>
</head>
<body>

<div class="container">

    <div class="top">
        <div class="page-title"><?php echo $pageTitle; ?></div>
        <div class="top-left">
            <a href="add.php" class="btn-add">+ إضافة عربية جديدة</a>
            <a href="../dashboard.php" class="btn-dashboard">لوحة التحكم</a>
        </div>
    </div>

    <?php if($count == 0){ ?>
        <div class="empty-box">⚠️ لا توجد عربيات مسجلة حالياً.</div>
    <?php } ?>

    <div class="search-box">
        <input type="text" id="searchInput" placeholder="🔍 ابحث برقم العربية، المهندس، الماركة، رقم الشاسيه أو البوليصة...">
    </div>

    <div class="table-box">
        <table>
            <thead>
                <tr>
                    <th>الصورة</th>
                    <th>رقم العربية</th>
                    <th>الماركة / الموديل</th>
                    <th>نوع الملكية</th>
                    <th>انتهاء الإيجار</th>
                    <th>المهندس المستلم</th>
                    <th>رقم الماتور</th>
                    <th>رقم الشاسيه</th>
                    <th>عداد الشريحة (كم)</th>
                    <th>انتهاء الرخصة</th>
                    <th>رقم البوليصة</th>
                    <th>انتهاء البوليصة</th>
                    <th>حالة البوليصة</th>
                    <th>التحكم</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                while($car = $vehicles->fetch_assoc()) { 
                    
                    // حسابات الرخص
                    $today = time();
                    $license_end_time = strtotime($car['license_end']);
                    $license_days = ceil(($license_end_time - $today) / 86400);

                    if($license_days > 30){
                        $license_status = "سارية (" . $license_days . " يوم)";
                        $license_class = "green";
                    } elseif($license_days > 0){
                        $license_status = "قربت تنتهي (" . $license_days . " يوم)";
                        $license_class = "orange";
                    } else {
                        $license_status = "منتهية";
                        $license_class = "red";
                    }

                    // حسابات الإيجار
                    $rent_status = "";
                    $rent_class = "";
                    $has_rent = (!empty($car['rent_end_date']) && $car['rent_end_date'] != "0000-00-00");
                    if($has_rent) {
                        $rent_days = ceil((strtotime($car['rent_end_date']) - $today) / 86400);
                        if($rent_days > 30){
                            $rent_status = "سارية (" . $rent_days . " يوم)";
                            $rent_class = "green";
                        } elseif($rent_days > 0){
                            $rent_status = "قربت تنتهي (" . $rent_days . " يوم)";
                            $rent_class = "orange";
                        } else {
                            $rent_status = "منتهية";
                            $rent_class = "red";
                        }
                    }

                    // حسابات البوليصة
                    $policy_end_time = (!empty($car['insurance_end']) && $car['insurance_end'] != "0000-00-00") ? strtotime($car['insurance_end']) : 0;
                    if($policy_end_time > 0) {
                        $policy_days = ceil(($policy_end_time - $today) / 86400);
                        if($policy_days > 30){
                            $policy_status = "سارية";
                            $policy_class = "green";
                        } elseif($policy_days > 0){
                            $policy_status = "قربت تنتهي";
                            $policy_class = "orange";
                        } else {
                            $policy_status = "منتهية";
                            $policy_class = "red";
                        }
                    } else {
                        $policy_status = "غير محدد";
                        $policy_class = "badge-gray";
                        $policy_days = 0;
                    }

                    // جلب اسم المهندس
                    $eng_name = "لم يحدد";
                    if(!empty($car['assigned_engineer']) && $car['assigned_engineer'] != 0) {
                        $eng_id = $car['assigned_engineer'];
                        $eng_query = $conn->query("SELECT * FROM engineers WHERE id = '$eng_id'");
                        if($eng_query && $eng_query->num_rows > 0) {
                            $eng_data = $eng_query->fetch_assoc();
                            if(isset($eng_data['name'])) $eng_name = $eng_data['name'];
                            elseif(isset($eng_data['engineer_name'])) $eng_name = $eng_data['engineer_name'];
                            elseif(isset($eng_data['username'])) $eng_name = $eng_data['username'];
                            else {
                                $keys = array_keys($eng_data);
                                $eng_name = $eng_data[$keys[1]]; 
                            }
                        } else {
                            $eng_name = "مهندس رقم " . $eng_id;
                        }
                    }
                ?>
                <tr class="car-row">
                    <td>
                        <?php if(!empty($car['image'])) { ?>
                            <img class="car-img" src="../assets/uploads/<?php echo $car['image']; ?>">
                        <?php } else { ?>
                            <span class="badge badge-gray">لا يوجد</span>
                        <?php } ?>
                    </td>

                    <td style="font-weight: 600; color: #2563eb;"><?php echo $car['plate_number']; ?></td>
                    
                    <td>
                        <strong><?php echo $car['brand']; ?></strong>
                        <div style="font-size: 11px; color:#64748b;"><?php echo $car['model']; ?></div>
                    </td>

                    <td>
                        <span class="badge badge-info">
                            <?php echo !empty($car['ownership_type']) ? $car['ownership_type'] : 'غير محدد'; ?>
                        </span>
                    </td>

                    <!-- خانة الإيجار -->
                    <td>
                        <?php if($has_rent) { ?>
                            <span class="badge status <?php echo $rent_class; ?>">
                                <?php echo $car['rent_end_date']; ?><br>
                                <small style="font-size: 10px;"><?php echo $rent_status; ?></small>
                            </span>
                        <?php } else { echo "-"; } ?>
                    </td>

                    <td>
                        <div style="font-weight: 600; color: #0f172a;">
                            <?php echo $eng_name; ?>
                        </div>
                    </td>

                    <td><code><?php echo !empty($car['engine_number']) ? $car['engine_number'] : '-'; ?></code></td>
                    <td><code><?php echo !empty($car['chassis_number']) ? $car['chassis_number'] : '-'; ?></code></td>
                    
                    <td>
                        <?php 
                        echo !empty($car['kilometers']) 
                        ? number_format($car['kilometers'])
                        : "0"; 
                        ?>
                    </td>

                    <td>
                        <span class="badge status <?php echo $license_class; ?>">
                            <?php echo $car['license_end']; ?><br>
                            <small style="font-size: 10px;"><?php echo $license_status; ?></small>
                        </span>
                    </td>

                    <td><?php echo !empty($car['insurance_policy']) ? $car['insurance_policy'] : '-'; ?></td>
                    
                    <td><?php echo (!empty($car['insurance_end']) && $car['insurance_end'] != "0000-00-00") ? $car['insurance_end'] : '-'; ?></td>

                    <td>
                        <span class="badge status <?php echo $policy_class; ?>">
                            <?php echo $policy_status; ?> 
                            <?php echo ($policy_days > 0) ? "($policy_days يوم)" : ""; ?>
                        </span>
                    </td>

                    <td>
                        <a class="btn view" href="view.php?id=<?php echo $car['id']; ?>">عرض</a>
                        <a class="btn edit" href="edit.php?id=<?php echo $car['id']; ?>">تعديل</a>
                        <a class="btn delete" href="#" onclick="showDeletePopup(<?php echo $car['id']; ?>)">حذف</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<div id="deletePopup" class="popup-overlay">
    <div class="popup-box">
        <h3>تأكيد الحذف</h3>
        <p>هل أنت متأكد تماماً من حذف هذه المركبة؟ لا يمكن التراجع عن هذا الإجراء.</p>
        <div class="popup-buttons">
            <a id="confirmDelete" class="confirm-btn">نعم، احذف الآن</a>
            <button onclick="closePopup()" class="cancel-btn">إلغاء</button>
        </div>
    </div>
</div>

<script>
function showDeletePopup(id){
    document.getElementById("deletePopup").style.display = "flex";
    document.getElementById("confirmDelete").href = "delete.php?id=" + id;
}
function closePopup(){
    document.getElementById("deletePopup").style.display = "none";
}

const searchInput = document.getElementById("searchInput");
searchInput.addEventListener("keyup", function(){
    let value = this.value.toLowerCase().trim();
    let rows = document.querySelectorAll(".car-row");

    rows.forEach(function(row){
        let text = row.innerText.toLowerCase();
        if(text.includes(value)){
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

</body>
</html>