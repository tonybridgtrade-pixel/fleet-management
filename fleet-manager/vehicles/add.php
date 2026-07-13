<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

/* المهندسين */
$engineers = $conn->query("
SELECT *
FROM engineers
ORDER BY full_name ASC
");

$msg = "";

if(isset($_POST['save'])){

    $plate_number      = $_POST['plate_number'];
    $brand             = $_POST['brand'];
    $model             = $_POST['model'];
    $color             = $_POST['color'];
    $kilometers        = $_POST['kilometers'];
    $manufacture_year  = $_POST['manufacture_year'];
    $license_end       = $_POST['license_end'];

    $chassis_number    = $_POST['chassis_number'];
    $engine_number     = $_POST['engine_number'];

    $insurance_number  = $_POST['insurance_number'];
    $insurance_end     = $_POST['insurance_end'];

    $ownership_type    = $_POST['ownership_type'];
    $rent_end_date     = ($ownership_type == 'إيجار') ? $_POST['rent_end_date'] : '';

    $fuel_card_meter   = $_POST['fuel_card_meter'];
    $notes             = $_POST['notes'];
    $vehicle_status    = $_POST['vehicle_status'];
    $manual_engineer   = $_POST['manual_engineer'];

    $uploaded_images = [];

    if(isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $total_files = count($_FILES['images']['name']);
        
        for($i = 0; $i < $total_files; $i++) {
            if($_FILES['images']['error'][$i] == 0) {
                $file_name = $_FILES['images']['name'][$i];
                $file_tmp  = $_FILES['images']['tmp_name'][$i];
                
                $new_image_name = time() . "_" . $i . "_" . $file_name;
                
                if(move_uploaded_file($file_tmp, "../assets/uploads/" . $new_image_name)) {
                    $uploaded_images[] = $new_image_name;
                }
            }
        }
    }

    $image_string = implode(",", $uploaded_images);

    $conn->query("
        INSERT INTO vehicles
        (
            plate_number, brand, model, color, kilometers, manufacture_year, license_end,
            chassis_number, engine_number, insurance_number, insurance_end, rent_end_date,
            fuel_card_meter, notes, image, ownership_type, vehicle_status, assigned_engineer
        )
        VALUES
        (
            '$plate_number', '$brand', '$model', '$color', '$kilometers', '$manufacture_year', '$license_end',
            '$chassis_number', '$engine_number', '$insurance_number', '$insurance_end', '$rent_end_date',
            '$fuel_card_meter', '$notes', '$image_string', '$ownership_type', '$vehicle_status', '$manual_engineer'
        )
    ");

    $msg = "✅ تم إضافة السيارة وحفظ الصور بنجاح في المنظومة.";
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>إضافة مركبة جديدة</title>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap');

*{
    box-sizing: border-box;
    font-family: 'Cairo', Arial, sans-serif;
}

body{
    background: #f8fafc;
    color: #334155;
    padding: 30px 20px;
    margin: 0;
}

.container{
    background: white;
    padding: 40px;
    border-radius: 20px;
    max-width: 1200px;
    margin: auto;
    box-shadow: 0 10px 25px rgba(0,0,0,0.03);
    border-top: 6px solid #2563eb;
}

h2{
    margin-top: 0;
    margin-bottom: 30px;
    color: #0f172a;
    text-align: center;
    font-size: 26px;
    font-weight: 700;
}

/* تعديل الصندوق وتفريغه تماماً من الإطارات بناءً على طلبك */
.logo-preview-container {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 25px;
    background: #f1f5f9;
    padding: 20px;
    border-radius: 16px;
    min-height: 140px;
    border: none; /* شيلنا الإطار الخارجي */
    transition: all 0.3s ease;
}

.logo-preview-box {
    text-align: center;
}

/* شيلنا الإطار الأبيض والظل تماماً من حول اللوجو ليظهر نظيف شفاف */
.brand-logo-large {
    width: 110px;
    height: 110px;
    object-fit: contain;
    display: none;
    animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    background: transparent; /* خلفية شفافة تماماً */
    padding: 0;
    border: none; /* بدون أي إطارات حول اللوجو */
    box-shadow: none; /* إلغاء الظل */
}

.logo-placeholder {
    color: #94a3b8;
    font-weight: 600;
    font-size: 14px;
}

@keyframes popIn {
    0% { transform: scale(0.6); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.grid{
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

label{
    margin-bottom: 8px;
    font-weight: 600;
    color: #475569;
    font-size: 13px;
}

input,
textarea,
select{
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s ease;
    outline: none;
    background: #f8fafc;
    color: #0f172a;
}

input:focus,
textarea:focus,
select:focus{
    border-color: #2563eb;
    background: white;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

textarea{
    min-height: 100px;
    resize: vertical;
}

.full-width {
    grid-column: 1 / -1;
}

button{
    background: #2563eb;
    color: white;
    border: none;
    padding: 14px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 700;
    transition: all 0.3s ease;
    width: 100%;
    margin-top: 30px;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}

button:hover{
    background: #1d4ed8;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
}

.success{
    background: #dcfce7;
    color: #15803d;
    padding: 15px;
    margin-bottom: 25px;
    border-radius: 10px;
    text-align: center;
    font-size: 15px;
    font-weight: 600;
    border: 1px solid #bbf7d0;
}

.back-box{
    text-align: center;
    margin-top: 25px;
}

.back-btn{
    display: inline-block;
    background: #64748b;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
    font-weight: 600;
}

.back-btn:hover{
    background: #475569;
    transform: translateY(-2px);
}

.section-title{
    margin-top: 30px;
    margin-bottom: 20px;
    font-size: 16px;
    color: #0f172a;
    border-right: 4px solid #2563eb;
    padding-right: 10px;
    font-weight: 700;
}

.rent-box{
    display: none;
}

.rent-box.show {
    display: flex;
    animation: fadeIn 0.3s ease forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

</head>
<body>

<div class="container">

<h2>🚘 إضافة مركبة جديدة للمنظومة</h2>

<?php if($msg != ""){ ?>
    <div class="success"><?= $msg ?></div>
<?php } ?>

<div class="logo-preview-container" id="logoPreviewContainer">
    <div class="logo-preview-box">
        <img id="brandLogoLarge" class="brand-logo-large" alt="لوجو الماركة">
        <div id="logoPlaceholder" class="logo-placeholder">سيتم عرض لوجو الماركة المختار هنا تلقائياً 🏷️</div>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">

    <div class="section-title">البيانات الأساسية للمركبة</div>
    <div class="grid">

        <div class="form-group">
            <label>رقم اللوحة (الحروف والأرقام)</label>
            <input type="text" name="plate_number" placeholder="مثال: أ ب ج 1234" required>
        </div>

        <div class="form-group">
            <label>الماركة</label>
            <input type="text" name="brand" id="brand" list="brands_list" placeholder="اكتب أو اختر الماركة" autocomplete="off" required>
            <datalist id="brands_list"></datalist>
        </div>

        <div class="form-group">
            <label>الموديل</label>
            <input type="text" name="model" id="model" list="models" placeholder="اختر الموديل" autocomplete="off" required>
            <datalist id="models"></datalist>
        </div>

        <div class="form-group">
            <label>اللون</label>
            <input type="text" name="color" placeholder="لون المركبة">
        </div>

        <div class="form-group">
            <label>قراءة العداد الحالية (KM)</label>
            <input type="number" name="kilometers" placeholder="العداد الحالي للسيارة" required>
        </div>

        <div class="form-group">
            <label>سنة الصنع (الموديل)</label>
            <input type="number" name="manufacture_year" placeholder="مثال: 2023">
        </div>

    </div>

    <div class="section-title">التراخيص والبيانات القانونية</div>
    <div class="grid">

        <div class="form-group">
            <label>رقم الشاسيه</label>
            <input type="text" name="chassis_number" placeholder="أدخل رقم الشاسيه بالكامل">
        </div>

        <div class="form-group">
            <label>رقم المحرك (الماتور)</label>
            <input type="text" name="engine_number" placeholder="أدخل رقم الماتور">
        </div>

        <div class="form-group">
            <label>تاريخ انتهاء الرخصة</label>
            <input type="date" name="license_end">
        </div>

        <div class="form-group">
            <label>رقم بوليصة التأمين</label>
            <input type="text" name="insurance_number" placeholder="رقم بوليصة التأمين">
        </div>

        <div class="form-group">
            <label>تاريخ انتهاء التأمين</label>
            <input type="date" name="insurance_end">
        </div>

        <div class="form-group">
            <label>عداد شريحة الوقود</label>
            <input type="text" name="fuel_card_meter" placeholder="أدخل عداد شريحة الوقود">
        </div>

    </div>

    <div class="section-title">حالة المركبة والملكية</div>
    <div class="grid">

        <div class="form-group">
            <label>نوع الملكية</label>
            <select name="ownership_type" id="ownership_type">
                <option value="ملك">ملك للشركة</option>
                <option value="إيجار">إيجار / عهدة خارجية</option>
            </select>
        </div>

        <div class="form-group rent-box" id="rentBox">
            <label>تاريخ انتهاء عقد الإيجار</label>
            <input type="date" name="rent_end_date" id="rent_end_date">
        </div>

        <div class="form-group">
            <label>الحالة التشغيلية</label>
            <select name="vehicle_status">
                <option value="متاحة">متاحة بجراج الشركة</option>
                <option value="مع مهندس">مع مهندس</option>
                <option value="في الصيانة">في الصيانة</option>
            </select>
        </div>



    </div>

    <div class="section-title">ملفات وملاحظات إضافية</div>
    <div class="grid">
        <div class="form-group">
            <label>ألبوم صور السيارة (يمكنك اختيار أكثر من صورة معاً) 📷</label>
            <input type="file" name="images[]" multiple accept="image/*">
        </div>
        
        <div class="form-group full-width" style="margin-top: 10px;">
            <label>ملاحظات عامة خط السير أو العيوب</label>
            <textarea name="notes" placeholder="اكتب أي ملاحظات إضافية حول حالة السيارة أو العهدة هنا..."></textarea>
        </div>
    </div>

    <button type="submit" name="save">🚘 ترحيل وحفظ بيانات المركبة</button>

</form>

<div class="back-box">
    <a class="back-btn" href="../dashboard.php">← العودة للوحة التحكم</a>
</div>

</div>

<script>
const brands = {
"BMW":{ logo:"../assets/brands/bmw.svg", models:["X1","X3","X4","X5","X6","X7","M2","M3","M4","M5","320","330","520","730"] },
"Mercedes":{ logo:"../assets/brands/mercedes.svg", models:["A180","C180","C200","E200","E300","S500","CLA","GLA","GLC","GLE","G63"] },
"Audi":{ logo:"https://cdn.simpleicons.org/audi", models:["A3","A4","A5","A6","A7","Q2","Q3","Q5","Q7","Q8","RS6"] },
"Toyota":{ logo:"https://cdn.simpleicons.org/toyota", models:["Corolla","Camry","Yaris","Fortuner","Hilux","Prado","Land Cruiser","Rush"] },
"Hyundai":{ logo:"https://cdn.simpleicons.org/hyundai", models:["Accent","Elantra","Tucson","Sonata","Creta","Santa Fe","i10","i20"] },
"Kia":{ logo:"https://cdn.simpleicons.org/kia", models:["Cerato","Rio","Sportage","Sorento","Picanto","K5","Seltos"] },
"Nissan":{ logo:"https://cdn.simpleicons.org/nissan", models:["Sunny","Sentra","Altima","Patrol","Qashqai","Juke","X-Trail"] },
"Chevrolet":{ logo:"../assets/brands/chevrolet.svg", models:["Optra","Cruze","Captiva","Tahoe","Malibu","Spark"] },
"Ford":{ logo:"https://cdn.simpleicons.org/ford", models:["Focus","Fusion","Explorer","Ranger","Mustang","Edge"] },
"Honda":{ logo:"https://cdn.simpleicons.org/honda", models:["Civic","Accord","CRV","HRV","City","Pilot"] },
"Peugeot":{ logo:"https://cdn.simpleicons.org/peugeot", models:["301","508","3008","5008","208","2008"] },
"Renault":{ logo:"https://cdn.simpleicons.org/renault", models:["Logan","Sandero","Duster","Megane","Kadjar"] },
"Volkswagen":{ logo:"https://cdn.simpleicons.org/volkswagen", models:["Golf","Passat","Tiguan","Touareg","Polo","Jetta"] },
"Jeep":{ logo:"https://cdn.simpleicons.org/jeep", models:["Wrangler","Cherokee","Compass","Grand Cherokee"] },
"MG":{ logo:"https://cdn.simpleicons.org/mg", models:["MG5","MG6","ZS","RX5","HS","One"] },
"Skoda":{ logo:"https://cdn.simpleicons.org/skoda", models:["Octavia","Superb","Kodiaq","Kamiq","Fabia"] },
"Seat":{ logo:"https://cdn.simpleicons.org/seat", models:["Leon","Ibiza","Ateca","Arona"] },
"Fiat":{ logo:"../assets/brands/fiat.svg", models:["Tipo","500","Panda","Doblo"] },
"Suzuki":{ logo:"https://cdn.simpleicons.org/suzuki", models:["Swift","Dzire","Ciaz","Vitara","Ertiga"] },
"Mazda":{ logo:"https://cdn.simpleicons.org/mazda", models:["Mazda 3","Mazda 6","CX3","CX5","CX9"] },
"Mitsubishi":{ logo:"https://cdn.simpleicons.org/mitsubishi", models:["Lancer","Pajero","Outlander","Eclipse Cross","Attrage"] },
"Subaru":{ logo:"https://cdn.simpleicons.org/subaru", models:["Impreza","Forester","Outback","XV"] },
"Lexus":{ logo:"https://cdn.simpleicons.org/lexus", models:["ES","LS","RX","LX","NX"] },
"Infiniti":{ logo:"https://cdn.simpleicons.org/infiniti", models:["Q50","QX50","QX60","QX80"] },
"Volvo":{ logo:"https://cdn.simpleicons.org/volvo", models:["S60","S90","XC40","XC60","XC90"] },
"Land Rover":{ logo:"https://cdn.simpleicons.org/landrover", models:["Defender","Discovery","Range Rover","Evoque","Velar"] },
"Porsche":{ logo:"https://cdn.simpleicons.org/porsche", models:["911","Cayenne","Macan","Panamera"] },
"Ferrari":{ logo:"https://cdn.simpleicons.org/ferrari", models:["488","Roma","F8","Portofino"] },
"Lamborghini":{ logo:"https://cdn.simpleicons.org/lamborghini", models:["Huracan","Aventador","Urus"] },
"Bentley":{ logo:"https://cdn.simpleicons.org/bentley", models:["Bentayga","Flying Spur","Continental"] },
"Rolls Royce":{ logo:"https://cdn.simpleicons.org/rollsroyce", models:["Ghost","Phantom","Cullinan"] },
"Bugatti":{ logo:"https://cdn.simpleicons.org/bugatti", models:["Chiron","Veyron","Divo"] },
"Tesla":{ logo:"https://cdn.simpleicons.org/tesla", models:["Model S","Model 3","Model X","Model Y","Cybertruck"] },
"BYD":{ logo:"https://upload.wikimedia.org/wikipedia/commons/4/44/BYD_Auto_2022_logo.svg", models:["Han","Tang","Qin","Dolphin","Atto 3"] },
"Chery":{ logo:"https://upload.wikimedia.org/wikipedia/commons/0/0f/Chery_logo.svg", models:["Arrizo 5","Tiggo 3","Tiggo 7","Tiggo 8"] },
"Geely":{ logo:"https://cdn.simpleicons.org/geely", models:["Coolray","Emgrand","Azkarra"] },
"Haval":{ logo:"https://cdn.simpleicons.org/haval", models:["H6","Jolion","H9"] },
"Opel":{ logo:"https://cdn.simpleicons.org/opel", models:["Corsa","Astra","Grandland","Crossland"] },
"Citroen":{ logo:"https://cdn.simpleicons.org/citroen", models:["C3","C4","C5 Aircross"] },
"Dodge":{ logo:"https://cdn.simpleicons.org/dodge", models:["Charger","Challenger","Durango"] },
"GMC":{ logo:"https://cdn.simpleicons.org/gmc", models:["Yukon","Terrain","Acadia","Sierra"] },
"Cadillac":{ logo:"https://cdn.simpleicons.org/cadillac", models:["Escalade","XT5","CT5"] },
"Lincoln":{ logo:"https://cdn.simpleicons.org/lincoln", models:["Navigator","Corsair","Aviator"] },
"Mini":{ logo:"https://cdn.simpleicons.org/mini", models:["Cooper","Countryman","Clubman"] },
"Alfa Romeo":{ logo:"https://cdn.simpleicons.org/alfaromeo", models:["Giulia","Stelvio","Tonale"] },
"Daewoo":{ logo:"https://cdn.simpleicons.org/daewoo", models:["Lanos","Nubira","Espero"] },
"Saab":{ logo:"https://cdn.simpleicons.org/saab", models:["9-3","9-5"] }
};

const brandInput = document.getElementById("brand");
const modelsList = document.getElementById("models");
const brandsList = document.getElementById("brands_list");
const logoLarge = document.getElementById("brandLogoLarge");
const logoPlaceholder = document.getElementById("logoPlaceholder");
const logoContainer = document.getElementById("logoPreviewContainer");

for(let brand in brands){
    let option = document.createElement("option");
    option.value = brand;
    brandsList.appendChild(option);
}

brandInput.addEventListener("input", function(){
    let rawBrand = this.value.trim();
    let foundBrandKey = Object.keys(brands).find(key => key.toLowerCase() === rawBrand.toLowerCase());

    modelsList.innerHTML = "";

    if(foundBrandKey){
        let matchedBrand = brands[foundBrandKey];
        
        if (matchedBrand.logo.includes("simpleicons.org")) {
            logoLarge.src = "https://cdn.simpleicons.org/" + foundBrandKey.toLowerCase();
        } else {
            logoLarge.src = matchedBrand.logo;
        }

        logoLarge.style.display = "block";
        logoPlaceholder.style.display = "none";
        logoContainer.style.background = "#ffffff";
    } else {
        logoLarge.style.display = "none";
        logoPlaceholder.style.display = "block";
        logoContainer.style.background = "#f1f5f9";
    }
});

const ownership = document.getElementById("ownership_type");
const rentBox = document.getElementById("rentBox");
const rentInput = document.getElementById("rent_end_date");

ownership.addEventListener("change", function(){
    if(this.value === "إيجار"){
        rentBox.classList.add("show");
    } else {
        rentBox.classList.remove("show");
        rentInput.value = "";
    }
});
</script>

</body>
</html>