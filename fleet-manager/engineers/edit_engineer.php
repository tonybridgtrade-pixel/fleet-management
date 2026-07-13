<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$msg = "";
$is_success = false;

$engineer_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// جلب بيانات المهندس الحالية لعرضها داخل الحقول
$engineer_query = $conn->query("SELECT * FROM engineers WHERE id = '$engineer_id'");
if(!$engineer_query || $engineer_query->num_rows == 0){
    die("<h3 style='text-align:center; margin-top:50px; font-family:Cairo;'>⚠️ خطأ: لم يتم العثور على المهندس المطلوب.</h3>");
}
$engineer = $engineer_query->fetch_assoc();

if(isset($_POST['update'])){

    $full_name   = $_POST['full_name'];
    $phone       = $_POST['phone'];
    $national_id = $_POST['national_id'];
    $department  = $_POST['department'];
    $job_title   = $_POST['job_title'];
    $hire_date   = $_POST['hire_date'];
    $address     = $_POST['address'];
    $notes       = $_POST['notes'];

    $sql = "UPDATE engineers SET 
                full_name = '$full_name',
                phone = '$phone',
                national_id = '$national_id',
                department = '$department',
                job_title = '$job_title',
                hire_date = '$hire_date',
                address = '$address',
                notes = '$notes'
            WHERE id = '$engineer_id'";

    if($conn->query($sql)){
        $msg = "✅ تم تحديث بيانات المهندس بنجاح.";
        $is_success = true;
        
        // تحديث مصفوفة العرض بالداتا الجديدة فوراً
        $engineer_query = $conn->query("SELECT * FROM engineers WHERE id = '$engineer_id'");
        $engineer = $engineer_query->fetch_assoc();
    }else{
        $msg = "⚠️ حدث خطأ أثناء التحديث: " . $conn->error;
        $is_success = false;
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>تعديل بيانات المهندس</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

*{ box-sizing: border-box; font-family: 'Cairo', Arial, sans-serif; }
body{ margin: 0; background: #f8fafc; color: #334155; }

.navbar{
    background: #0f172a; color: white; padding: 15px 30px;
    display: flex; justify-content: space-between; align-items: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.navbar h2 { margin: 0; font-size: 20px; font-weight: 700; }
.navbar a {
    color: white; text-decoration: none; background: rgba(255,255,255,0.1);
    padding: 8px 16px; border-radius: 8px; font-size: 14px; font-weight: 600; transition: 0.3s;
}
.navbar a:hover { background: rgba(255,255,255,0.2); }

.container{ max-width: 800px; margin: 40px auto; padding: 0 20px; }

.card{
    background: white; padding: 35px; border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04); border-top: 6px solid #eab308; /* اللون الأصفر الملوكي للتعديل */
}

.alert{
    padding: 14px; border-radius: 10px; margin-bottom: 25px;
    font-weight: 600; font-size: 14px; text-align: center;
}
.alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.full-width { grid-column: span 2; }
.form-group { display: flex; flex-direction: column; }

label{ font-weight: 600; margin-bottom: 8px; color: #334155; font-size: 14px; }
input, textarea{
    width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1;
    border-radius: 10px; font-size: 14px; background: #fff; color: #0f172a; transition: all 0.2s ease;
}
input:focus, textarea:focus {
    outline: none; border-color: #eab308; box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.1);
}
textarea { resize: vertical; min-height: 80px; }

button.submit-btn{
    width: 100%; background: #eab308; color: #0f172a; border: none; padding: 14px;
    border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 16px; margin-top: 15px;
    transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(234, 179, 8, 0.2);
}
button.submit-btn:hover{ background: #ca8a04; transform: translateY(-1px); }
</style>
</head>
<body>

<div class="navbar">
    <h2>🛠️ تعديل ملف المهندس</h2>
    <a href="list.php">📋 قائمة المهندسين</a>
</div>

<div class="container">
    <div class="card">
        <h2 style="margin-top:0; margin-bottom:30px; color:#0f172a; font-size:22px;">📝 تحديث بيانات المهندس: <?= $engineer['full_name'] ?></h2>

        <?php if($msg != ""){ ?>
            <div class="alert <?= $is_success ? 'alert-success' : 'alert-danger' ?>">
                <?= $msg ?>
            </div>
        <?php } ?>

        <form method="POST">
            <div class="form-grid">
                
                <div class="form-group">
                    <label>👤 اسم المهندس بالكامل</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($engineer['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>📞 رقم الموبايل</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($engineer['phone']) ?>">
                </div>

                <div class="form-group">
                    <label>🪪 الرقم القومي</label>
                    <input type="text" name="national_id" value="<?= htmlspecialchars($engineer['national_id']) ?>">
                </div>

                <div class="form-group">
                    <label>🏢 القسم</label>
                    <input type="text" name="department" value="<?= htmlspecialchars($engineer['department']) ?>">
                </div>

                <div class="form-group">
                    <label>💼 المسمى الوظيفي</label>
                    <input type="text" name="job_title" value="<?= htmlspecialchars($engineer['job_title']) ?>">
                </div>

                <div class="form-group">
                    <label>📅 تاريخ التسجيل بالمنظومة</label>
                    <input type="date" name="hire_date" value="<?= $engineer['hire_date'] ?>">
                </div>

                <div class="form-group full-width">
                    <label>📍 العنوان السكني الحالي</label>
                    <textarea name="address"><?= htmlspecialchars($engineer['address']) ?></textarea>
                </div>

                <div class="form-group full-width">
                    <label>📝 ملاحظات إضافية</label>
                    <textarea name="notes"><?= htmlspecialchars($engineer['notes']) ?></textarea>
                </div>

            </div>

            <button type="submit" name="update" class="submit-btn">
                💾 حفظ التعديلات الجديدة
            </button>
        </form>
    </div>
</div>

</body>
</html>