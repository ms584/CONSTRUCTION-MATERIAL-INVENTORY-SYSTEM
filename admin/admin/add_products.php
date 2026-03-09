<?php
session_start();
include("../../db.php"); // ดึงไฟล์เชื่อมต่อ DB

// โค้ดสำหรับบันทึกข้อมูลเมื่อกดปุ่ม Submit
if(isset($_POST['btn_save'])) {
    $product_code = $_POST['product_code'];
    $product_name = $_POST['product_name'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $stock_qty = $_POST['stock_qty'];
    $min_stock = $_POST['min_stock'];

    // 1. รับค่าหมวดหมู่ 
    $category_id = $_POST['category_id'];

    // 2. รับค่าที่แอดมินพิมพ์เข้ามาเอง 
    $brand_name = trim($_POST['brand_name']);
    $unit_name = trim($_POST['unit_name']);

    //  รับค่า Location 
    $location = trim($_POST['location']);

    // --- ระบบเช็คและสร้าง ยี่ห้อ อัตโนมัติ 
    $brand_id = 0;
    $chk_brand = mysqli_query($con, "SELECT brand_id FROM brands WHERE brand_name = '$brand_name'");
    if(mysqli_num_rows($chk_brand) > 0) {
        $r_brand = mysqli_fetch_array($chk_brand);
        $brand_id = $r_brand['brand_id'];
    } else {
        mysqli_query($con, "INSERT INTO brands (brand_name) VALUES ('$brand_name')");
        $brand_id = mysqli_insert_id($con);
    }

    // --- ระบบเช็คและสร้าง หน่วยนับ อัตโนมัติ 
    $unit_id = 0;
    $chk_unit = mysqli_query($con, "SELECT unit_id FROM units WHERE unit_name = '$unit_name'");
    if(mysqli_num_rows($chk_unit) > 0) {
        $r_unit = mysqli_fetch_array($chk_unit);
        $unit_id = $r_unit['unit_id'];
    } else {
        mysqli_query($con, "INSERT INTO units (unit_name) VALUES ('$unit_name')");
        $unit_id = mysqli_insert_id($con);
    }

    // 3. จัดการรูปภาพ
    $picture_name = $_FILES['picture']['name'];
    $picture_type = $_FILES['picture']['type'];
    $picture_tmp_name = $_FILES['picture']['tmp_name'];
    $picture_size = $_FILES['picture']['size'];

    if($picture_name != "") {
        $pic_name = time() . "_" . $picture_name;
        move_uploaded_file($picture_tmp_name, "../../product_images/" . $pic_name);
    } else {
        $pic_name = "default.jpg";
    }

    // 4. บันทึกข้อมูลลงฐานข้อมูล 
    $sql = "INSERT INTO products (product_code, product_name, category_id, brand_id, unit_id, cost_price, selling_price, stock_qty, min_stock, location, product_image) 
            VALUES ('$product_code', '$product_name', '$category_id', '$brand_id', '$unit_id', '$cost_price', '$selling_price', '$stock_qty', '$min_stock', '$location', '$pic_name')";
    
    if(mysqli_query($con, $sql)) {
        echo "<script>alert('เพิ่มรายการวัสดุก่อสร้างและสถานที่เก็บสำเร็จ!'); window.location.href='products_list.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($con);
    }
}
include "sidenav.php";
include "topheader.php";
?>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title">เพิ่มรายการวัสดุก่อสร้างใหม่</h4>
            <p class="card-category">กรอกรายละเอียดสินค้าเพื่อนำเข้าสต๊อก</p>
          </div>
          <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
              
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">รหัสสินค้า / Barcode</label>
                    <input type="text" name="product_code" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อวัสดุก่อสร้าง (เช่น ปูนเสือ 50กก.)</label>
                    <input type="text" name="product_name" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>หมวดหมู่</label> 
                    <select name="category_id" class="form-control" required style="background-color: transparent;">
                      <option value="" disabled selected>-- เลือกหมวดหมู่ --</option>
                      <?php 
                        // ดึงหมวดหมู่จากฐานข้อมูลมาแสดงเป็นตัวเลือก
                        $cat_query = mysqli_query($con, "SELECT * FROM categories");
                        while($row = mysqli_fetch_array($cat_query)){
                          echo "<option value='".$row['category_id']."' style='color: black;'>".$row['category_name']."</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">ยี่ห้อ (Brand)</label>
                    <input type="text" name="brand_name" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">หน่วยนับ </label>
                    <input type="text" name="unit_name" class="form-control" required>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="bmd-label-floating">ราคาต้นทุน (บาท)</label>
                    <input type="number" step="0.01" name="cost_price" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="bmd-label-floating">ราคาขาย (บาท)</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="bmd-label-floating">จำนวนเริ่มต้นในโกดัง</label>
                    <input type="number" name="stock_qty" class="form-control" value="0" required>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="bmd-label-floating">จุดแจ้งเตือนของหมด (ชิ้น)</label>
                    <input type="number" name="min_stock" class="form-control" value="10" required>
                  </div>
                </div>
              </div>
              
              <div class="row mt-4">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>สถานที่เก็บ </label>
                    <input type="text" name="location" class="form-control" placeholder="เช่น โกดัง A, เชลฟ์ 2">
                  </div>
                </div>
                <div class="col-md-6">
                  <label>รูปภาพสินค้า</label>
                  <input type="file" name="picture" class="btn btn-fill btn-secondary" accept="image/*">
                </div>
              </div>

              <button type="submit" name="btn_save" class="btn btn-primary pull-right">บันทึกสินค้าใหม่</button>
              <div class="clearfix"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>