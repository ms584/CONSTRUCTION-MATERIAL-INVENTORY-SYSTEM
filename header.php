<?php
session_start();
include "db.php";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ระบบคลังสินค้าวัสดุก่อสร้าง</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,700" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/bootstrap.min.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick.css"/>
    <link type="text/css" rel="stylesheet" href="css/slick-theme.css"/>
    <link type="text/css" rel="stylesheet" href="css/nouislider.min.css"/>
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link type="text/css" rel="stylesheet" href="css/style.css"/>
    <style>
        
        body { background-color: #fbfbfb; }
        #header { background-color: #15161D; }
    </style>
</head>
<body>
    <header>
        <div id="top-header">
            <div class="container">
                <ul class="header-links pull-left">
                    <li><a href="#"><i class="fa fa-phone"></i> +66 012-345-6789</a></li>
                    <li><a href="#"><i class="fa fa-envelope-o"></i> info@constructshop.com</a></li>
                </ul>
                <ul class="header-links pull-right">
                    <li><a href="admin/login.php"><i class="fa fa-user-o"></i> ระบบพนักงาน (Backend)</a></li>
                </ul>
            </div>
        </div>
        <div id="header">
            <div class="container">
                <div class="row">
                    <div class="col-md-3">
                        <div class="header-logo">
                            <a href="index.php" class="logo" style="display:inline-block; margin-top:15px;">
                                <h2 style="color: white; font-weight:bold;">CONSTRUCT<span style="color: #D10024;">SHOP</span></h2>
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6">
                       
                    <div class="col-md-3 clearfix">
                        <div class="header-ctn">
                            <div>
                                <a href="#" onclick="alert('กรุณาติดต่อหน้าร้าน โทร: 012-345-6789 เพื่อสั่งซื้อครับ')">
                                    <i class="fa fa-phone" style="font-size:24px;"></i>
                                    <span>ติดต่อสั่งซื้อ</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
        </header>

    <nav id="navigation">
        <div class="container">
            <div id="responsive-nav">
                <ul class="main-nav nav navbar-nav">
                    <li class="active"><a href="index.php">หน้าหลัก</a></li>
                    <li><a href="store.php">ดูแคตตาล็อกทั้งหมด</a></li>
                    
                    <li><a href="check_bill.php" style="color: #D10024; font-weight: bold;"><i class="fa fa-search"></i> ตรวจสอบบิล</a></li>
                    
                    <?php 
                        // ดึงหมวดหมู่มาแสดงบนเมนูบาร์ (จำกัด 5 หมวดหมู่)
                        $cat_query = mysqli_query($con, "SELECT * FROM categories LIMIT 5");
                        if($cat_query){
                            while($row = mysqli_fetch_array($cat_query)){
                                echo "<li><a href='store.php?cat_id=".$row['category_id']."'>".$row['category_name']."</a></li>";
                            }
                        }
                    ?>
                </ul>
            </div>
        </div>
    </nav>
    ```

