<?php
session_start();
include("../../db.php");

// เมื่อกดปุ่มบันทึก
if(isset($_POST['btn_save'])) {
    $supplier_name = $_POST['supplier_name'];
    $contact_person = $_POST['contact_person'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "INSERT INTO suppliers (supplier_name, contact_person, phone, address) 
            VALUES ('$supplier_name', '$contact_person', '$phone', '$address')";
    
    if(mysqli_query($con, $sql)) {
        echo "<script>alert('เพิ่มข้อมูลบริษัทคู่ค้าสำเร็จ!'); window.location.href='suppliers_list.php';</script>";
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
          <div class="card-header card-header-info">
            <h4 class="card-title">เพิ่มข้อมูลบริษัทคู่ค้าใหม่</h4>
            <p class="card-category">กรอกรายละเอียดสำหรับการติดต่อสั่งซื้อสินค้า</p>
          </div>
          <div class="card-body">
            <form action="" method="post">
              
              <div class="row mt-2">
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อบริษัท / ชื่อร้านส่ง</label>
                    <input type="text" name="supplier_name" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อผู้ติดต่อ (เซลส์)</label>
                    <input type="text" name="contact_person" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="bmd-label-floating">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="bmd-label-floating">ที่อยู่บริษัท / รายละเอียดเพิ่มเติม</label>
                    <textarea name="address" class="form-control" rows="4"></textarea>
                  </div>
                </div>
              </div>

              <button type="submit" name="btn_save" class="btn btn-info pull-right mt-4">บันทึกข้อมูล</button>
              <div class="clearfix"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>