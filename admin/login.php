<?php
session_start();
include("../db.php"); // เชื่อมต่อฐานข้อมูลของเรา

$error_msg = "";

// หากมีการล็อคอินค้างไว้ ให้เด้งเข้าไปหน้า Dashboard เลย
if (isset($_SESSION['admin_name'])) {
    header('location: admin/index.php');
    exit();
}

// เมื่อกดปุ่ม Log in (name="login_admin" ตามฟอร์มเดิม)
if(isset($_POST['login_admin'])) {
    $username = mysqli_real_escape_string($con, $_POST['admin_username']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    // ค้นหาพนักงานจากตาราง employees
    $sql = "SELECT * FROM employees WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($con, $sql);

    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result);
        
        // เก็บข้อมูลพนักงานลง Session
        $_SESSION['admin_name'] = $row['username'];
        $_SESSION['employee_id'] = $row['employee_id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['full_name'] = $row['full_name'];       
        $_SESSION['employee_id'] = $row['employee_id'];
        $_SESSION['full_name'] = $row['full_name'];
        $_SESSION['role'] = $row['role'];

        // เด้งเข้าหน้าหลัก
        echo "<script>alert('เข้าสู่ระบบสำเร็จ! ยินดีต้อนรับคุณ ".$row['full_name']."'); window.location.href='admin/index.php';</script>";
        exit();
    } else {
        // ถ้ารหัสผิด ให้เก็บข้อความ error ไว้แสดงผลในฟอร์ม
        $error_msg = "ชื่อผู้ใช้งาน หรือ รหัสผ่าน ไม่ถูกต้อง!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Login - ระบบจัดการสต๊อก</title>

    <link rel="stylesheet" href="fonts/material-icon/css/material-design-iconic-font.min.css">

    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>

    <div class="main" style="padding-top: 90px;">

        <section class="sign-in">
            <div class="container">
                <div class="signin-content">
                    <div class="signin-image">
                        <figure><img src="./assets/images/signup-image.jpg" alt="sing up image"></figure>
                        <a href="../index.php" class="signup-image-link">Back To Home</a>
                    </div>

                    <div class="signin-form">
                        <h2 class="form-title">ADMIN LOGIN</h2>
                        <form class="register-form" id="login-form" action="login.php" method="post">
                            
                            <?php if($error_msg != "") { ?>
                                <div class="alert alert-danger" style="color: red; margin-bottom: 20px;">
                                    <h4 id="e_msg" style="color: red; font-size: 14px;"><?php echo $error_msg; ?></h4>
                                </div>
                            <?php } ?>

                            <div class="form-group">
                                <label for="your_name"><i class="zmdi zmdi-account material-icons-name"></i></label>
                                <input type="text" name="admin_username" id="your_name" placeholder="ชื่อผู้ใช้งาน (Username)" required/>
                            </div>
                            <div class="form-group">
                                <label for="your_pass"><i class="zmdi zmdi-lock"></i></label>
                                <input type="password" name="password" id="your_pass" placeholder="Password" required/>
                            </div>
                           
                            <div class="form-group form-button">
                                <input type="submit" name="login_admin" id="signin" class="form-submit" value="Log in"/>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
        </section>

    </div>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="js/main.js"></script>
</body></html>