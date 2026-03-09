 <?php
session_start();
include("../../db.php");

// เมื่อกดปุ่ม "บันทึกข้อมูลพนักงาน"
if(isset($_POST['btn_save'])) {
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    $username = $_POST['username'];
    $password = $_POST['password']; 

    // เช็คก่อนว่า Username นี้มีคนใช้ไปไหม
    $check_query = mysqli_query($con, "SELECT * FROM employees WHERE username = '$username'");
    if(mysqli_num_rows($check_query) > 0) {
        echo "<script>alert('ข้อผิดพลาด: ชื่อผู้ใช้งาน (Username) นี้มีคนใช้แล้ว กรุณาตั้งชื่ออื่น!');</script>";
    } else {
        // บันทึกลงตาราง employees
        $sql = "INSERT INTO employees (username, password, full_name, role) 
                VALUES ('$username', '$password', '$full_name', '$role')";
        
        if(mysqli_query($con, $sql)) {
            echo "<script>alert('เพิ่มพนักงานใหม่สำเร็จ!'); window.location.href='manageuser.php';</script>";
        } else {
            echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
        }
    }
}

include "sidenav.php";
include "topheader.php";
?>
<style>
    
    select.form-control option { 
        color: #000000 !important; 
        background-color: #ffffff !important; 
    }
    

    select.form-control { 
        color: #ffffff !important; 
    }
</style>
<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title">เพิ่มพนักงานใหม่ (Add Employee)</h4>
            <p class="card-category">กำหนดสิทธิ์และสร้างรหัสผ่านเพื่อเข้าใช้งานระบบคลังสินค้า</p>
          </div>
          <div class="card-body">
            <form action="" method="post">
              
              <div class="row mt-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>ชื่อ-นามสกุล (Full Name)</label>
                    <input type="text" name="full_name" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>ตำแหน่ง / สิทธิ์การใช้งาน (Role)</label>
                    <select name="role" class="form-control" required>
                      <option value="">-- เลือกตำแหน่ง --</option>
                      <option value="Admin">Admin (ผู้ดูแลระบบ)</option>
                      <option value="Manager">Manager (ผู้จัดการ)</option>
                      <option value="Cashier">Cashier (พนักงานแคชเชียร์)</option>
                      <option value="Stock">Stock (พนักงานคลังสินค้า)</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>ชื่อเข้าใช้งานระบบ (Username)</label>
                    <input type="text" name="username" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>รหัสผ่าน (Password)</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-12">
                   <p class="text-muted"><small><b>หมายเหตุ:</b><br>
                   - <b>ผู้ดูแลระบบ (Admin) & ผู้จัดการ:</b> สามารถเข้าถึงได้ทุกเมนู (รวมถึงการลบข้อมูลและดูยอดขาย)<br>
                   - <b>พนักงานคลังสินค้า (Stock):</b> สามารถรับของเข้า และเช็คสต๊อกได้<br>
                   - <b>พนักงานขาย (Cashier):</b> สามารถออกบิลเบิกสินค้า และเช็คสต๊อกได้
                   </small></p>
                </div>
              </div>

              <button type="submit" name="btn_save" class="btn btn-primary pull-right">บันทึกข้อมูลพนักงาน</button>
              <div class="clearfix"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>