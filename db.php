<?php
$servername = "localhost";
$username = "root"; 
$password = "";     
$db = "db_construction_materials"; 


$con = mysqli_connect($servername, $username, $password, $db);


if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($con, "utf8");
?>