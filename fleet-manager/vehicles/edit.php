<?php
session_start();
if(!isset($_SESSION['user'])){ header("Location: ../login.php"); exit; }
include "../config/db.php";

$id = $_GET['id'];
$car = $conn->query("SELECT * FROM vehicles WHERE id=$id")->fetch_assoc();
$engineers = $conn->query("SELECT id, full_name FROM engineers ORDER BY full_name ASC");

if(!$car) die("العربية غير موجودة");

if(isset($_POST['update'])){
    $plate_number = $_POST['plate_number'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $color = $_POST['color'];
    $kilometers = $_POST['kilometers'];
    $manufacture_year = $_POST['manufacture_year'];
    $license_end = $_POST['license_end'];
    $phone = $_POST['phone'];
    $notes = $_POST['notes'];
    $ownership_type = $_POST['ownership_type'];
    $vehicle_status = $_POST['vehicle_status'];
    
    $assigned_engineer = ($vehicle_status == 'مع مهندس') ? $_POST['assigned_engineer'] : NULL;
    // حفظ تاريخ الإيجار فقط إذا كان النوع إيجار
    $rent_end_date = ($ownership_type == 'إيجار') ? $_POST['rent_end_date'] : NULL;
    
    $chassis_number = $_POST['chassis_number'];
    $engine_number = $_POST['engine_number'];
    $insurance_policy = $_POST['insurance_policy'];
    $insurance_number = $_POST['insurance_number'];
    $insurance_end = $_POST['insurance_end'];

    $image = $car['image'];
    if($_FILES['image']['name'] != ""){
        $image = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/uploads/" . $image);
    }

    $stmt = $conn->prepare("UPDATE vehicles SET plate_number=?, brand=?, model=?, color=?, kilometers=?, manufacture_year=?, license_end=?, phone=?, ownership_type=?, vehicle_status=?, assigned_engineer=?, chassis_number=?, engine_number=?, insurance_policy=?, insurance_number=?, insurance_end=?, rent_end_date=?, notes=?, image=? WHERE id=?");
    $stmt->bind_param("sssssssssssssssssssi", $plate_number, $brand, $model, $color, $kilometers, $manufacture_year, $license_end, $phone, $ownership_type, $vehicle_status, $assigned_engineer, $chassis_number, $engine_number, $insurance_policy, $insurance_number, $insurance_end, $rent_end_date, $notes, $image, $id);
    $stmt->execute();

    header("Location: list.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<title>تعديل بيانات المركبة</title>
<style>
    :root { --primary: #2563eb; --success: #16a34a; --bg: #f8fafc; }
    body { font-family: 'Segoe UI', Tahoma, Arial; background: var(--bg); padding: 20px; }
    .container { max-width: 900px; margin: auto; background: white; padding: 30px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    h2 { text-align: center; color: #1e293b; margin-bottom: 30px; }
    .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: 600; color: #475569; font-size: 14px; }
    input, select, textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 15px; }
    textarea { height: 100px; resize: none; }
    .btn-save { background: var(--success); color: white; border: none; padding: 15px; border-radius: 10px; width: 100%; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; }
    .back-link { display: block; text-align: center; margin-top: 20px; color: #64748b; text-decoration: none; }
    .car-image { width: 150px; height: 150px; object-fit: cover; border-radius: 15px; margin: 10px 0; border: 3px solid #e2e8f0; }
</style>
</head>
<body>

<div class="container">
    <h2>تعديل بيانات المركبة: <?= $car['plate_number'] ?></h2>
    <form method="POST" enctype="multipart/form-data">
        <div class="grid">
            <div class="form-group"><label>رقم اللوحة</label><input type="text" name="plate_number" value="<?= $car['plate_number'] ?>" required></div>
            <div class="form-group"><label>الماركة</label><input type="text" name="brand" value="<?= $car['brand'] ?>"></div>
            <div class="form-group"><label>الموديل</label><input type="text" name="model" value="<?= $car['model'] ?>"></div>
            <div class="form-group"><label>اللون</label><input type="text" name="color" value="<?= $car['color'] ?>"></div>
            <div class="form-group"><label>العداد (KM)</label><input type="number" name="kilometers" value="<?= $car['kilometers'] ?>"></div>
            <div class="form-group"><label>سنة الصنع</label><input type="number" name="manufacture_year" value="<?= $car['manufacture_year'] ?>"></div>
            <div class="form-group"><label>تاريخ انتهاء الرخصة</label><input type="date" name="license_end" value="<?= $car['license_end'] ?>"></div>
            <div class="form-group"><label>رقم التليفون الملحق</label><input type="text" name="phone" value="<?= $car['phone'] ?>"></div>
            
            <div class="form-group"><label>نوع الملكية</label>
                <select name="ownership_type" id="ownershipSelect">
                    <option value="ملك" <?= $car['ownership_type']=="ملك"?"selected":"" ?>>ملك</option>
                    <option value="إيجار" <?= $car['ownership_type']=="إيجار"?"selected":"" ?>>إيجار</option>
                </select>
            </div>

            <div class="form-group" id="rentDateBox" style="display:none;">
                <label>تاريخ انتهاء الإيجار</label>
                <input type="date" name="rent_end_date" value="<?= $car['rent_end_date'] ?>">
            </div>

            <div class="form-group"><label>حالة المركبة</label>
                <select name="vehicle_status" id="statusSelect">
                    <option value="متاحة" <?= $car['vehicle_status']=="متاحة"?"selected":"" ?>>متاحة</option>
                    <option value="مع مهندس" <?= $car['vehicle_status']=="مع مهندس"?"selected":"" ?>>مع مهندس</option>
                    <option value="في الصيانة" <?= $car['vehicle_status']=="في الصيانة"?"selected":"" ?>>في الصيانة</option>
                </select>
            </div>

            <div class="form-group" id="engBox"><label>المهندس المسؤول</label>
                <select name="assigned_engineer">
                    <option value="">بدون مهندس</option>
                    <?php while($eng=$engineers->fetch_assoc()){ ?>
                        <option value="<?= $eng['id'] ?>" <?= $car['assigned_engineer']==$eng['id']?'selected':'' ?>><?= $eng['full_name'] ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group"><label>رقم الشاسيه</label><input type="text" name="chassis_number" value="<?= $car['chassis_number'] ?>"></div>
            <div class="form-group"><label>رقم الماتور</label><input type="text" name="engine_number" value="<?= $car['engine_number'] ?>"></div>
            <div class="form-group"><label>بوليصة التأمين</label><input type="text" name="insurance_policy" value="<?= $car['insurance_policy'] ?>"></div>
            <div class="form-group"><label>رقم التأمين</label><input type="text" name="insurance_number" value="<?= $car['insurance_number'] ?>"></div>
            <div class="form-group"><label>تاريخ انتهاء التأمين</label><input type="date" name="insurance_end" value="<?= $car['insurance_end'] ?>"></div>
        </div>

        <div class="form-group"><label>ملاحظات إضافية</label><textarea name="notes"><?= $car['notes'] ?></textarea></div>
        
        <?php if($car['image'] != ""){ ?>
            <label>الصورة الحالية:</label>
            <img class="car-image" src="../assets/uploads/<?= $car['image'] ?>">
        <?php } ?>
        
        <div class="form-group"><label>تحديث صورة المركبة</label><input type="file" name="image"></div>

        <button type="submit" name="update" class="btn-save">حفظ التعديلات</button>
    </form>
    <a href="list.php" class="back-link">← العودة للقائمة</a>
</div>

<script>
    const statusSelect = document.getElementById('statusSelect');
    const engBox = document.getElementById('engBox');
    const ownershipSelect = document.getElementById('ownershipSelect');
    const rentDateBox = document.getElementById('rentDateBox');

    // وظيفة المهندس
    statusSelect.addEventListener('change', function() {
        engBox.style.display = (this.value === 'مع مهندس') ? 'block' : 'none';
    });
    engBox.style.display = (statusSelect.value === 'مع مهندس') ? 'block' : 'none';

    // وظيفة الإيجار
    ownershipSelect.addEventListener('change', function() {
        rentDateBox.style.display = (this.value === 'إيجار') ? 'block' : 'none';
    });
    rentDateBox.style.display = (ownershipSelect.value === 'إيجار') ? 'block' : 'none';
</script>

</body>
</html>