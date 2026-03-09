
  <?php
session_start();
include("../../db.php");

$current_username = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'admin';

// ดึงข้อมูลบัญชีของคนที่กำลังล็อคอิน
$query = mysqli_query($con, "SELECT * FROM employees WHERE username = '$current_username' LIMIT 1");

if(mysqli_num_rows($query) == 0) {
    $query = mysqli_query($con, "SELECT * FROM employees WHERE employee_id = 1");
}
$user = mysqli_fetch_array($query);
$emp_id = $user['employee_id'];

// เมื่อกดปุ่มอัปเดตโปรไฟล์
if(isset($_POST['btn_update_profile'])) {
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $new_password = $_POST['password'];

    $check_dup = mysqli_query($con, "SELECT * FROM employees WHERE username = '$username' AND employee_id != '$emp_id'");
    if(mysqli_num_rows($check_dup) > 0) {
        echo "<script>alert('ข้อผิดพลาด: Username นี้มีคนอื่นใช้แล้ว กรุณาตั้งชื่ออื่น!'); window.history.back();</script>";
        exit();
    }

    $sql_update = "UPDATE employees SET full_name = '$full_name', username = '$username'";
    
    if(!empty($new_password)) {
        $sql_update .= ", password = '$new_password'";
    }
    
    $sql_update .= " WHERE employee_id = '$emp_id'";

    if(mysqli_query($con, $sql_update)) {
        $_SESSION['admin_name'] = $username;
        echo "<script>alert('อัปเดตข้อมูลบัญชีสำเร็จ!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . mysqli_error($con) . "');</script>";
    }
}

include "sidenav.php";
include "topheader.php";
?>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      
      <div class="col-md-8 offset-md-2">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title">ตั้งค่าบัญชีผู้ใช้ (Edit Profile)</h4>
            <p class="card-category">อัปเดตข้อมูลส่วนตัวและเปลี่ยนรหัสผ่าน</p>
          </div>
          <div class="card-body">
            <form action="" method="post">
              
              <div class="row mt-3">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">รหัสพนักงาน (ID)</label>
                    <input type="text" class="form-control" value="EMP-<?php echo sprintf('%03d', $user['employee_id']); ?>" disabled>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">สิทธิ์การใช้งาน (Role)</label>
                    <input type="text" class="form-control" value="<?php echo $user['role']; ?>" disabled>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อเข้าใช้งาน (Username)</label>
                    <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อ-นามสกุล (Full Name)</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="bmd-label-floating">รหัสผ่านใหม่ (ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                    <input type="password" name="password" class="form-control" placeholder="*** พิมพ์รหัสผ่านใหม่ที่นี่ ***">
                  </div>
                </div>
              </div>

              <button type="submit" name="btn_update_profile" class="btn btn-primary pull-right mt-4">อัปเดตโปรไฟล์</button>
              <div class="clearfix"></div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php include "footer.php"; ?>