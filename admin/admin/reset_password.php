<?php
session_start();
include("../../db.php");

// เฉพาะ Admin และ Manager เท่านั้นที่เข้าถึงหน้านี้ได้
if($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Manager') {
    echo "<script>alert('ขออภัย! คุณไม่มีสิทธิ์เข้าถึงหน้านี้'); window.location.href='index.php';</script>";
    exit();
}

// ตรวจสอบว่ามี ID ส่งมาไหม
if(!isset($_GET['id'])) {
    echo "<script>window.location.href='manageuser.php';</script>";
    exit();
}

$emp_id = intval($_GET['id']);

// ดึงข้อมูลพนักงาน
$query = mysqli_query($con, "SELECT * FROM employees WHERE employee_id = '$emp_id'");
if(mysqli_num_rows($query) == 0) {
    echo "<script>alert('ไม่พบข้อมูลพนักงาน!'); window.location.href='manageuser.php';</script>";
    exit();
}
$emp = mysqli_fetch_array($query);

$success_msg = "";
$error_msg = "";

// เมื่อกดบันทึก
if(isset($_POST['btn_reset'])) {
    $new_pass = trim($_POST['new_password']);
    $confirm_pass = trim($_POST['confirm_password']);

    if(empty($new_pass)) {
        $error_msg = "กรุณากรอกรหัสผ่านใหม่";
    } elseif(strlen($new_pass) < 6) {
        $error_msg = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } elseif($new_pass !== $confirm_pass) {
        $error_msg = "รหัสผ่านใหม่และการยืนยันไม่ตรงกัน!";
    } else {
        $update_sql = "UPDATE employees SET password = '$new_pass' WHERE employee_id = '$emp_id'";
        if(mysqli_query($con, $update_sql)) {
            echo "<script>alert('เปลี่ยนรหัสผ่านให้ " . $emp['full_name'] . " สำเร็จแล้ว!'); window.location.href='manageuser.php';</script>";
            exit();
        } else {
            $error_msg = "เกิดข้อผิดพลาด: " . mysqli_error($con);
        }
    }
}

include "sidenav.php";
include "topheader.php";
?>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-6 offset-md-3">
        <div class="card">
          <div class="card-header card-header-info">
            <h4 class="card-title">
              <i class="material-icons" style="vertical-align:middle; margin-right:8px;">lock_reset</i>
              รีเซ็ตรหัสผ่านพนักงาน
            </h4>
            <p class="card-category">เปลี่ยนรหัสผ่านสำหรับพนักงานที่ต้องการ</p>
          </div>
          <div class="card-body">

            <!-- ข้อมูลพนักงาน -->
            <div style="background:#2a2f45; border-radius:10px; padding:15px 20px; margin-bottom:25px; border-left:4px solid #00bcd4;">
              <p style="margin:0; font-size:13px; color:#aaa;">กำลังรีเซ็ตรหัสผ่านให้:</p>
              <h5 style="margin:5px 0 2px; color:#fff;"><?php echo $emp['full_name']; ?></h5>
              <span style="color:#aaa; font-size:13px;">Username: <b style="color:#00bcd4;"><?php echo $emp['username']; ?></b> &nbsp;|&nbsp; Role: <?php echo $emp['role']; ?></span>
            </div>

            <?php if($error_msg): ?>
            <div class="alert" style="background:#f44336; color:#fff; padding:12px 18px; border-radius:8px; margin-bottom:20px;">
              <i class="material-icons" style="vertical-align:middle; margin-right:5px; font-size:18px;">error</i>
              <?php echo $error_msg; ?>
            </div>
            <?php endif; ?>

            <form action="" method="post">
              <div class="form-group">
                <label>รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                <input type="password" name="new_password" class="form-control" placeholder="กรอกรหัสผ่านใหม่ (อย่างน้อย 6 ตัว)" required>
              </div>

              <div class="form-group mt-3">
                <label>ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                <input type="password" name="confirm_password" class="form-control" placeholder="กรอกรหัสผ่านซ้ำอีกครั้ง" required>
              </div>

              <div class="mt-4 d-flex justify-content-between">
                <a href="manageuser.php" class="btn btn-default">
                  <i class="material-icons" style="vertical-align:middle; font-size:16px;">arrow_back</i> ยกเลิก
                </a>
                <button type="submit" name="btn_reset" class="btn btn-info">
                  <i class="material-icons" style="vertical-align:middle; font-size:16px;">save</i>
                  บันทึกรหัสผ่านใหม่
                </button>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>
