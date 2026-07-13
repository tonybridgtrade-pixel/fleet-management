<?php
session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

if(isset($_GET['id'])){
    $engineer_id = intval($_GET['id']);

    // التأكد أولاً أن المهندس مش معاه عربية مستلمها
    $check = $conn->query("SELECT status FROM engineers WHERE id = '$engineer_id'");
    if($check && $check->num_rows > 0){
        $engineer = $check->fetch_assoc();
        
        if($engineer['status'] == "مستلم سيارة"){
            // التوجيه لصفحة القائمة مع إرسال بارامتر الخطأ في الرابط
            header("Location: list.php?error=assigned");
            exit;
        }
    }

    // تنفيذ أمر الحذف لو حالته تسمح (بدون سيارة)
    $sql = "DELETE FROM engineers WHERE id = '$engineer_id'";
    
    if($conn->query($sql)){
        header("Location: list.php?success=deleted");
        exit;
    } else {
        header("Location: list.php?error=sql");
        exit;
    }
} else {
    header("Location: list.php");
    exit;
}
?>