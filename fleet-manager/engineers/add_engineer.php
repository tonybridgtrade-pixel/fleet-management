<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$msg = "";
$is_success = false;

if(isset($_POST['save'])){

    $full_name   = $_POST['full_name'];
    $phone       = $_POST['phone'];
    $national_id = $_POST['national_id'];
    $department  = $_POST['department'];
    $job_title   = $_POST['job_title'];
    $hire_date   = $_POST['hire_date'];
    $address     = $_POST['address'];
    $notes       = $_POST['notes'];

    $sql = "INSERT INTO engineers
    (
        full_name,
        phone,
        national_id,
        department,
        job_title,
        hire_date,
        address,
        notes,
        status
    )
    VALUES
    (
        '$full_name',
        '$phone',
        '$national_id',
        '$department',
        '$job_title',
        '$hire_date',
        '$address',
        '$notes',
        'بدون سيارة'
    )";

    if($conn->query($sql)){
        $msg = "✅ تم إضافة بيانات المهندس بنجاح إلى قاعدة البيانات.";
        $is_success = true;
    }else{
        $msg = "⚠️ حدث خطأ أثناء الحفظ: " . $conn->error;
        $is_success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إضافة مهندس جديد</title>

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

/* الشريط العلوي الاحترافي */
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

.navbar .nav-links a {
    color: white;
    text-decoration: none;
    background: rgba(255,255,255,0.1);
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    margin-right: 10px;
    transition: 0.3s;
}
.navbar .nav-links a:hover {
    background: rgba(255,255,255,0.2);
}

.container{
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.card{
    background: white;
    padding: 35px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border-top: 6px solid #2563eb; /* اللون الأزرق المميز لإضافة البيانات */
}

h2.form-title{
    margin-top: 0;
    margin-bottom: 30px;
    color: #0f172a;
    font-size: 22px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* التنبيهات المنبثقة */
.alert{
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 25px;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
}
.alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* تقسيم الحقول لشبكة منظمة */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

/* جعل حقول التكست اريا تأخذ السطر بالكامل */
.full-width {
    grid-column: span 2;
}

.form-group {
    display: flex;
    flex-direction: column;
}

label{
    font-weight: 600;
    margin-bottom: 8px;
    color: #334155;
    font-size: 14px;
}

input, select, textarea{
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 14px;
    background: #fff;
    color: #0f172a;
    transition: all 0.2s ease;
}

input:focus, textarea:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

textarea {
    resize: vertical;
    min-height: 80px;
}

button.submit-btn{
    width: 100%;
    background: #2563eb;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    font-size: 16px;
    margin-top: 15px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

button.submit-btn:hover{
    background: #1d4ed8;
    transform: translateY(-1px);
}

.back-box {
    text-align: center;
    margin-top: 25px;
}

.back-link {
    color: #64748b;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s;
}
.back-link:hover {
    color: #0f172a;
}

/* للتجاوب مع الشاشات الصغيرة */
@media (max-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    .full-width {
        grid-column: span 1;
    }
}
</style>
</head>
<body>

<div class="navbar">
    <h2>👷 إدارة شؤون المهندسين</h2>
    <div class="nav-links">
        <a href="list.php">📋 قائمة المهندسين</a>
        <a href="../dashboard.php">📊 لوحة التحكم</a>
    </div>
</div>

<div class="container">

    <div class="card">
        <h2 class="form-type-title">👷 تسجيل مهندس جديد بالنظام</h2>

        <?php if($msg != ""){ ?>
            <div class="alert <?= $is_success ? 'alert-success' : 'alert-danger' ?>">
                <?= $msg ?>
            </div>
        <?php } ?>

        <form method="POST">
            <div class="form-grid">
                
                <div class="form-group">
                    <label>👤 اسم المهندس بالكامل</label>
                    <input type="text" name="full_name" placeholder="أدخل الاسم الحقيقي للمهندس..." required>
                </div>

                <div class="form-group">
                    <label>📞 رقم الموبايل</label>
                    <input type="text" name="phone" placeholder="01xxxxxxxxx">
                </div>

                <div class="form-group">
                    <label>🪪 الرقم القومي (14 رقم)</label>
                    <input type="text" name="national_id" placeholder="أدخل الرقم القومي الخاص بالبطاقة...">
                </div>

                <div class="form-group">
                    <label>🏢 القسم التابع له</label>
                    <input type="text" name="department" placeholder="مثل: التنفيذ، المشروعات، المكتب الفني...">
                </div>

                <div class="form-group">
                    <label>💼 المسمى الوظيفي</label>
                    <input type="text" name="job_title" placeholder="مثل: مهندس موقع، مدير مشروع...">
                </div>

                <div class="form-group">
                    <label>📅 تاريخ التسجيل بالمنظومة</label>
                    <input type="date" name="hire_date" value="<?= date('Y-m-d') ?>">
                </div>

                <div class="form-group full-width">
                    <label>📍 العنوان السكني الحالي</label>
                    <textarea name="address" placeholder="أدخل تفاصيل العنوان..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label>📝 ملاحظات إضافية</label>
                    <textarea name="notes" placeholder="أي ملاحظات إضافية تخص المهندس أو طبيعة عمله..."></textarea>
                </div>

            </div>

            <button type="submit" name="save" class="submit-btn">
                💾 حفظ بيانات المهندس بالسيستم
            </button>
        </form>
    </div>

    <div class="back-box">
        <a class="back-link" href="../dashboard.php">⬅ رجوع للوحة التحكم الرئيسية</a>
    </div>

</div>

</body>
</html>