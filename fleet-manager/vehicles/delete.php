<?php

session_start();

if(!isset($_SESSION['user'])){
    header("Location: ../login.php");
    exit;
}

include "../config/db.php";

$id = $_GET['id'];

$conn->query("DELETE FROM vehicles WHERE id=$id");

header("Location: list.php");
exit;

?>