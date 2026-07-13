<?php
session_start();
include "config/db.php";

$error = "";

if(isset($_POST['login'])){

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");

    if($query->num_rows > 0){

        $user = $query->fetch_assoc();

        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: dashboard.php");
        exit;

    } else {
        $error = "اسم المستخدم أو كلمة المرور غير صحيحة";
    }
}
?>

<!DOCTYPE html>
<html lang="ar">
<head>
<meta charset="UTF-8">
<title>Fleet Manager Login</title>

<style>

body{
    font-family: Arial;
    background:#f2f2f2;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.login-box{
    background:#fff;
    width:350px;
    padding:30px;
    border-radius:10px;
    box-shadow:0 0 10px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:20px;
}

input{
    width:100%;
    padding:12px;
    margin-bottom:15px;
    border:1px solid #ccc;
    border-radius:5px;
}

button{
    width:100%;
    padding:12px;
    background:#007bff;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

button:hover{
    background:#0056b3;
}

.error{
    color:red;
    text-align:center;
    margin-bottom:15px;
}

</style>

</head>
<body>

<div class="login-box">

    <h2>Fleet Manager</h2>

    <?php if($error != ""){ ?>
        <div class="error"><?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">

        <input type="text" name="username" placeholder="اسم المستخدم" required>

        <input type="password" name="password" placeholder="كلمة المرور" required>

        <button type="submit" name="login">تسجيل الدخول</button>

    </form>

</div>

</body>
</html>