
   <?php
session_start();
include("../../db.php");

// ดึงข้อมูลสรุปสำหรับ Dashboard
$q_products = mysqli_query($con, "SELECT COUNT(*) as total FROM products");
$total_products = mysqli_fetch_assoc($q_products)['total'];

$q_lowstock = mysqli_query($con, "SELECT COUNT(*) as total FROM products WHERE stock_qty <= min_stock");
$low_stock = mysqli_fetch_assoc($q_lowstock)['total'];

$q_categories = mysqli_query($con, "SELECT COUNT(*) as total FROM categories");
$total_categories = mysqli_fetch_assoc($q_categories)['total'];

$q_suppliers = mysqli_query($con, "SELECT COUNT(*) as total FROM suppliers");
$total_suppliers = mysqli_fetch_assoc($q_suppliers)['total'];

include "sidenav.php";
include "topheader.php";
?>

<div class="content">
  <div class="container-fluid">
    
    <div class="row">
        <div class="col-md-12">
            <h3 class="text-white">ภาพรวมระบบคลังสินค้า (Dashboard)</h3>
        </div>
    </div>

    <div class="row mt-3">
      
      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
          <div class="card-header card-header-success card-header-icon">
            <div class="card-icon">
              <i class="material-icons">inventory_2</i>
            </div>
            <p class="card-category">รายการสินค้าทั้งหมด</p>
            <h3 class="card-title"><?php echo $total_products; ?> <small>รายการ</small></h3>
          </div>
          <div class="card-footer">
            <div class="stats">
              <i class="material-icons text-success">update</i> อัปเดตล่าสุด
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
          <div class="card-header card-header-danger card-header-icon">
            <div class="card-icon">
              <i class="material-icons">warning</i>
            </div>
            <p class="card-category">สินค้าใกล้หมดสต๊อก!</p>
            <h3 class="card-title"><?php echo $low_stock; ?> <small>รายการ</small></h3>
          </div>
          <div class="card-footer">
            <div class="stats">
              <i class="material-icons">local_offer</i> ต้องสั่งซื้อเพิ่มด่วน
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
          <div class="card-header card-header-warning card-header-icon">
            <div class="card-icon">
              <i class="material-icons">category</i>
            </div>
            <p class="card-category">หมวดหมู่วัสดุก่อสร้าง</p>
            <h3 class="card-title"><?php echo $total_categories; ?> <small>หมวดหมู่</small></h3>
          </div>
          <div class="card-footer">
            <div class="stats">
              <i class="material-icons">folder</i> จัดการหมวดหมู่สินค้า
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-6">
        <div class="card card-stats">
          <div class="card-header card-header-info card-header-icon">
            <div class="card-icon">
              <i class="material-icons">local_shipping</i>
            </div>
            <p class="card-category">คู่ค้า / ร้านส่ง</p>
            <h3 class="card-title"><?php echo $total_suppliers; ?> <small>บริษัท</small></h3>
          </div>
          <div class="card-footer">
            <div class="stats">
              <i class="material-icons">contacts</i> รายชื่อผู้จัดจำหน่าย
            </div>
          </div>
        </div>
      </div>
      
    </div> </div>
</div>

<?php include "footer.php"; ?>