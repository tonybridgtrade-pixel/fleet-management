<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

/* المهندسين */
$engineers = $conn->query("
SELECT engineers.*,
vehicles.plate_number,
vehicles.brand,
vehicles.kilometers,
vehicles.vehicle_status
FROM engineers
LEFT JOIN vehicles ON engineers.id = vehicles.assigned_engineer
ORDER BY engineers.id DESC
");

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إدارة المهندسين</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

*{
    box-sizing: border-box;
    font-family: 'Cairo', Arial, sans-serif;
}

body{
    margin: 0;
    background: #f8fafc;
    color: #334155;
}

.navbar{
    background: #0f172a;
    color: white;
    padding: 15px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.navbar h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
}

.navbar a{
    color: white;
    text-decoration: none;
    background: rgba(255,255,255,0.1);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.3s;
}
.navbar a:hover {
    background: rgba(255,255,255,0.2);
}

.container{
    max-width: 1400px;
    margin: 0 auto;
    padding: 30px 20px;
}

.top-bar{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 20px;
}

.top-bar h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
}

.add-btn{
    background: #2563eb;
    color: white;
    padding: 12px 24px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.add-btn:hover {
    background: #1d4ed8;
    transform: translateY(-2px);
}

/* شبكة كروت المهندسين */
.grid{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 25px;
}

.card{
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border-top: 6px solid #e2e8f0;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.card:hover{
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.card.active-eng { border-top-color: #10b981; }
.card.waiting-eng { border-top-color: #f59e0b; }

.card h3{
    margin-top: 0;
    margin-bottom: 15px;
    color: #0f172a;
    font-size: 18px;
    font-weight: 700;
}

.card p{
    margin: 8px 0;
    color: #475569;
    font-size: 14px;
}

.card b {
    color: #0f172a;
}

.status{
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
}

.green{ background: #dcfce7; color: #15803d; }
.orange{ background: #fef3c7; color: #b45309; }

.car-box{
    background: #f8fafc;
    padding: 15px;
    border-radius: 12px;
    margin-top: 15px;
    border: 1px solid #f1f5f9;
}

.no-car{
    color: #94a3b8;
    font-weight: 600;
    text-align: center;
    font-size: 13px;
}

/* الأزرار داخل الكارت */
.btns{
    margin-top: 20px;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn{
    text-decoration: none;
    color: white;
    padding: 8px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    flex: 1;
    min-width: 70px;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: scale(1.03);
}

.assign-btn{ background: #2563eb; }
.return-btn{ background: #ef4444; }
.report-btn{ background: #475569; }
.edit-btn{ background: #10b981; }
.delete-btn{ background: #dc2626; }

/* بوب اب التأكيد والتحذير الذكي */
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
    width: 400px;
    padding: 30px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.popup-box h3 {
    margin-top: 0;
    color: #0f172a;
    font-size: 20px;
}

.popup-buttons{ display: flex; justify-content: center; gap: 10px; margin-top: 25px;}
.confirm-btn{ background: #ef4444; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 14px;}
.cancel-btn{ background: #e2e8f0; color: #334155; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 14px;}
.close-alert-btn { background: #64748b; color: white; padding: 10px 30px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 14px;}

</style>
</head>
<body>

<div class="navbar">
    <h2>👨‍🔧 إدارة عهد المهندسين</h2>
    <a href="../dashboard.php">لوحة التحكم</a>
</div>

<div class="container">

    <div class="top-bar">
        <h2>قائمة المهندسين الحاليين</h2>
        <a class="add-btn" href="add_engineer.php">+ إضافة مهندس جديد</a>
    </div>

    <div class="grid">

    <?php while($eng = $engineers->fetch_assoc()) { 
        
        $is_assigned = ($eng['status'] == "مستلم سيارة");
        $statusClass = $is_assigned ? "green" : "orange";
        $cardClass = $is_assigned ? "active-eng" : "waiting-eng";
    ?>

    <div class="card <?= $cardClass ?>">
        <div>
            <h3>👷 <?= $eng['full_name'] ?></h3>
            <p><b>📞 الموبايل:</b> <?= $eng['phone'] ?></p>
            <p><b>🏢 القسم:</b> <?= $eng['department'] ?></p>
            <p><b>🚦 الحالة:</b> <span class="status <?= $statusClass ?>"><?= $eng['status'] ?></span></p>

            <div class="car-box">
                <?php if(!empty($eng['plate_number'])) { ?>
                    <p><b>🚗 السيارة المستلمة:</b> <span style="color:#2563eb; font-weight:700;"><?= $eng['plate_number'] ?></span></p>
                    <p><b>الماركة:</b> <?= $eng['brand'] ?></p>
                    <p><b>العداد الحالي:</b> <?= number_format($eng['kilometers']) ?> KM</p>
                    <p><b>حالة المركبة:</b> <?= $eng['vehicle_status'] ?></p>
                <?php } else { ?>
                    <div class="no-car">📭 لا توجد عربية بعهدة المهندس حالياً</div>
                <?php } ?>
            </div>
        </div>

        <div class="btns">
            <?php if(!$is_assigned) { ?>
                <a class="btn assign-btn" href="../assignments/assign_vehicle.php?engineer_id=<?= $eng['id'] ?>">🚗 تسليم</a>
            <?php } else { ?>
                <a class="btn return-btn" href="../assignments/return_vehicle.php?id=<?= $eng['id'] ?>">🔄 استرجاع</a>
            <?php } ?>

            <a class="btn report-btn" href="report.php?id=<?= $eng['id'] ?>">📊 تقرير</a>
            
            <a class="btn edit-btn" href="edit_engineer.php?id=<?= $eng['id'] ?>">✏️ تعديل</a>
            <a class="btn delete-btn" href="#" onclick="showDeletePopup(<?= $eng['id'] ?>)">🗑️ حذف</a>
        </div>
    </div>

    <?php } ?>

    </div>

</div>

<div id="deletePopup" class="popup-overlay">
    <div class="popup-box">
        <h3>⚠️ تأكيد حذف المهندس</h3>
        <p>هل أنت متأكد تماماً من حذف هذا المهندس من النظام؟ سيتم حذف بياناته بالكامل ولا يمكن التراجع عن هذا الأمر.</p>
        <div class="popup-buttons">
            <a id="confirmDelete" class="confirm-btn">نعم، احذف الآن</a>
            <button onclick="closePopup('deletePopup')" class="cancel-btn">إلغاء</button>
        </div>
    </div>
</div>

<div id="errorPopup" class="popup-overlay">
    <div class="popup-box" style="border-top: 6px solid #ef4444;">
        <span style="font-size: 50px;">❌</span>
        <h3 style="color: #b91c1c; margin-top: 10px;">عذراً، لا يمكن الحذف!</h3>
        <p id="errorMsgText" style="font-weight: 600; color: #475569; margin: 15px 0;">لا يمكن حذف المهندس لأنه مستلم سيارة حالياً. قم بعمل استرجاع للسيارة أولاً لحماية سلامة البيانات.</p>
        <div class="popup-buttons">
            <button onclick="closePopup('errorPopup')" class="close-alert-btn">حسناً، فهمت</button>
        </div>
    </div>
</div>

<script>
function showDeletePopup(id){
    document.getElementById("deletePopup").style.display = "flex";
    document.getElementById("confirmDelete").href = "delete.php?id=" + id;
}

function closePopup(popupId){
    document.getElementById(popupId).style.display = "none";
    if(popupId === 'errorPopup'){
        window.history.replaceState({}, document.title, window.location.pathname);
    }
}

window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('error') && urlParams.get('error') === 'assigned') {
        document.getElementById("errorPopup").style.display = "flex";
    }
});
</script>

</body>
</html>