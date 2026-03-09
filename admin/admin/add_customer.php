<?php
session_start();
include("../../db.php");

// เมื่อกดปุ่มบันทึก
if(isset($_POST['btn_save'])) {
    $customer_name = $_POST['customer_name'];
    $customer_type = $_POST['customer_type']; // รับค่าประเภทลูกค้า
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "INSERT INTO customers (customer_name, customer_type, phone, address) 
            VALUES ('$customer_name', '$customer_type', '$phone', '$address')";
    
    if(mysqli_query($con, $sql)) {
        echo "<script>alert('เพิ่มข้อมูลลูกค้าใหม่สำเร็จ!'); window.location.href='customers_list.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . mysqli_error($con) . "');</script>";
    }
}

include "sidenav.php";
include "topheader.php";
?>

<style>
    select.form-control option { color: #000000 !important; background-color: #ffffff !important; }
    select.form-control { color: #ffffff !important; }
</style>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="card">
          <div class="card-header card-header-info">
            <h4 class="card-title">เพิ่มประวัติลูกค้า / ผู้รับเหมา</h4>
            <p class="card-category">กรอกรายละเอียดเพื่อใช้ในการออกบิลและการจัดส่ง</p>
          </div>
          <div class="card-body">
            <form action="" method="post">
              
              <div class="row mt-3">
                <div class="col-md-8">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อ-นามสกุล / ชื่อบริษัท หรือ ชื่อช่าง</label>
                    <input type="text" name="customer_name" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>ประเภทลูกค้า</label>
                    <select name="customer_type" class="form-control" required style="background-color: transparent;">
                        <option value="ลูกค้าทั่วไป" selected>ลูกค้าทั่วไป (General)</option>
                        <option value="ช่างประจำ">ช่างประจำ (Technician)</option>
                        <option value="ผู้รับเหมา">ผู้รับเหมา (Contractor)</option>
                        <option value="บริษัท/นิติบุคคล">บริษัท/นิติบุคคล (Corporate)</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-5">
                  <div class="form-group">
                    <label class="bmd-label-floating">เบอร์โทรศัพท์ติดต่อ</label>
                    <input type="text" name="phone" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-7">
                  <div class="form-group">
                    <label class="bmd-label-floating">ที่อยู่จัดส่ง / ข้อมูลสำหรับออกใบกำกับภาษี</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                  </div>
                </div>
              </div>

              <button type="submit" name="btn_save" class="btn btn-info pull-right mt-4">บันทึกข้อมูลลูกค้า</button>
              <div class="clearfix"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>