<?php

  if (!isset($_SESSION['admin_name'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: .././login.php');
  }
  if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['admin_name']);
    header("location: .././login.php");
  }
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="../assets/img/favicon.png">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
        ระบบจัดการสต๊อกวัสดุก่อสร้าง | Admin
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no' name='viewport' />
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css">
    <link href="assets/css/material-dashboard.css?v=2.1.0" rel="stylesheet" />
    <link href="assets/demo/demo.css" rel="stylesheet" />
</head>

<body class="dark-edition">
    <div class="wrapper ">
        <div class="sidebar" data-color="purple" data-background-color="black" data-image="../assets/img/sidebar-2.jpg">
            <div class="logo">
    <a href="index.php" class="simple-text logo-normal" style="font-weight: bold; font-size: 18px;">
        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">store</i>
        ระบบหลังบ้าน
    </a>
</div>
            <div class="sidebar-wrapper">
                <ul class="nav">
                    <li class="nav-item active  ">
                        <a class="nav-link" href="index.php">
                            <i class="material-icons">dashboard</i>
                            <p>หน้าหลัก (Dashboard)</p>
                        </a>
                    </li>
                    
                    <li class="nav-item ">
                        <a class="nav-link" href="products_list.php">
                        <i class="material-icons">list</i>
                        <p>เช็คสต๊อก (Inventory)</p>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="add_products.php">
                        <i class="material-icons">add_box</i>
                        <p>เพิ่มสินค้าใหม่ (Add Material)</p>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="stock_in.php">
                        <i class="material-icons">input</i>
                        <p>รับของเข้า (Stock In)</p>
                        </a>
                    <li class="nav-item ">
                        <a class="nav-link" href="receiving_history.php">
                        <i class="material-icons">history</i>
                        <p>ประวัติรับของเข้า (Receiving)</p>
                        </a>
                    </li>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="stock_out.php">
                        <i class="material-icons">shopping_cart</i>
                        <p>เบิก/ขายสินค้า (Stock Out)</p>
                        </a>
                    </li>

                    <li class="nav-item ">
                        <a class="nav-link" href="salesofday.php">
                            <i class="material-icons">library_books</i>
                            <p>ประวัติการออกบิล (Bill History)</p>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="suppliers_list.php">
                             <i class="material-icons">local_shipping</i>
                              <p>บริษัทคู่ค้า (Suppliers)</p>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="customers_list.php">
                        <i class="material-icons">recent_actors</i>
                        <p>ฐานข้อมูลลูกค้า (Customers)</p>
                             </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="addemployees.php">
                            <i class="material-icons">person_add</i>
                            <p>เพิ่มพนักงาน (Add Users)</p>
                        </a>
                    </li>
                     <li class="nav-item ">
                        <a class="nav-link" href="manageuser.php">
                            <i class="material-icons">people</i>
                            <p>จัดการผู้ใช้งาน (Manage Users)</p>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a class="nav-link" href="profile.php">
                            <i class="material-icons icon-image-preview">settings</i>
                            <p>ตั้งค่าบัญชี (Settings)</p>
                        </a>
                    </li>
                    
                </ul>
            </div>
        </div>