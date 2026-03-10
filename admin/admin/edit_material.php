<?php
session_start();
include("../../db.php");

// ===== Admin, Manager, Stock เท่านั้น =====
$_r = $_SESSION['role'] ?? '';
if(!in_array($_r, ['Admin','Manager','Stock'])) {
    echo "<script>alert('ขออภัย! ไม่มีสิทธิ์แก้ไขสินค้า'); window.location.href='index.php';</script>";
    exit();
}

// ===== AJAX: จัดการ Packaging =====
if(isset($_POST['pkg_action'])) {
    header('Content-Type: application/json');
    $pid = (int)($_POST['product_id'] ?? 0);

    if($_POST['pkg_action'] === 'add') {
        $unit = mysqli_real_escape_string($con, trim($_POST['package_unit'] ?? ''));
        $rate = (float)($_POST['units_per_package'] ?? 0);
        if($pid && $unit && $rate > 0) {
            $q = "INSERT INTO product_packaging (product_id, package_unit, units_per_package)
                  VALUES ('$pid','$unit','$rate')
                  ON DUPLICATE KEY UPDATE units_per_package = '$rate'";
            if(mysqli_query($con, $q)) {
                $new_id = mysqli_insert_id($con);
                echo json_encode(['ok'=>true,'id'=>$new_id,'unit'=>$unit,'rate'=>$rate]);
            } else {
                echo json_encode(['ok'=>false,'msg'=>mysqli_error($con)]);
            }
        } else {
            echo json_encode(['ok'=>false,'msg'=>'ข้อมูลไม่ครบ']);
        }
    }

    if($_POST['pkg_action'] === 'delete') {
        $pkg_id = (int)($_POST['pkg_id'] ?? 0);
        if(mysqli_query($con, "DELETE FROM product_packaging WHERE id = '$pkg_id' AND product_id = '$pid'")) {
            echo json_encode(['ok'=>true]);
        } else {
            echo json_encode(['ok'=>false,'msg'=>mysqli_error($con)]);
        }
    }
    exit();
}

// ตรวจสอบว่ามี ID ส่งมาหรือไม่
if(!isset($_GET['id'])){
    echo "<script>window.location.href='products_list.php';</script>";
    exit();
}
$edit_id = $_GET['id'];

// ดึงข้อมูลเดิมของสินค้านั้นมาแสดงในฟอร์ม
$query = mysqli_query($con, "SELECT * FROM products WHERE product_id = '$edit_id'");
$p_row = mysqli_fetch_array($query);

// เมื่อกดปุ่ม "อัปเดตข้อมูล"
if(isset($_POST['btn_update'])) {
    $product_code = $_POST['product_code'];
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category_id'];
    $brand_id = $_POST['brand_id'];
    $unit_id = $_POST['unit_id'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $min_stock = $_POST['min_stock'];
    $location = trim($_POST['location']);

    // จัดการรูปภาพ (ถ้ามีการอัปโหลดใหม่)
    $product_image = $p_row['product_image']; // ใช้รูปเดิมก่อน
    if(isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
        $file_type = $_FILES['product_image']['type'];
        $file_size = $_FILES['product_image']['size'];

        if(!in_array($file_type, $allowed_types)) {
            echo "<script>alert('ไฟล์ที่รองรับ: JPG, PNG, WEBP, GIF เท่านั้น!');</script>";
        } elseif($file_size > 5 * 1024 * 1024) {
            echo "<script>alert('ขนาดไฟล์ต้องไม่เกิน 5 MB!');</script>";
        } else {
            $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
            $upload_path = '../../product_images/' . $new_filename;
            if(move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                // ลบรูปเก่าออก (ถ้าไม่ใช่ default.jpg)
                if($p_row['product_image'] != 'default.jpg' && file_exists('../../product_images/' . $p_row['product_image'])) {
                    unlink('../../product_images/' . $p_row['product_image']);
                }
                $product_image = $new_filename;
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์!');</script>";
            }
        }
    }

    $sql_update = "UPDATE products SET 
                    product_code = '$product_code',
                    product_name = '$product_name',
                    category_id = '$category_id',
                    brand_id = '$brand_id',
                    unit_id = '$unit_id',
                    cost_price = '$cost_price',
                    selling_price = '$selling_price',
                    min_stock = '$min_stock',
                    location = '$location',
                    product_image = '$product_image'
                   WHERE product_id = '$edit_id'";
    
    if(mysqli_query($con, $sql_update)) {
        echo "<script>alert('อัปเดตข้อมูลสินค้าสำเร็จ!'); window.location.href='products_list.php';</script>";
    } else {
        echo "Error: " . mysqli_error($con);
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
          <div class="card-header card-header-warning">
            <h4 class="card-title">แก้ไขรายการสินค้า</h4>
          </div>
          <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>รหัสสินค้า</label>
                    <input type="text" name="product_code" class="form-control" value="<?php echo $p_row['product_code']; ?>" required>
                  </div>
                </div>
                <div class="col-md-8">
                  <div class="form-group">
                    <label>ชื่อวัสดุก่อสร้าง</label>
                    <input type="text" name="product_name" class="form-control" value="<?php echo $p_row['product_name']; ?>" required>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>หมวดหมู่</label>
                    <select name="category_id" class="form-control" required>
                      <?php 
                        $cat_q = mysqli_query($con, "SELECT * FROM categories");
                        while($c = mysqli_fetch_array($cat_q)){
                          $selected = ($c['category_id'] == $p_row['category_id']) ? "selected" : "";
                          echo "<option value='".$c['category_id']."' $selected>".$c['category_name']."</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>ยี่ห้อ</label>
                    <select name="brand_id" class="form-control" required>
                      <?php 
                        $brand_q = mysqli_query($con, "SELECT * FROM brands");
                        while($b = mysqli_fetch_array($brand_q)){
                          $selected = ($b['brand_id'] == $p_row['brand_id']) ? "selected" : "";
                          echo "<option value='".$b['brand_id']."' $selected>".$b['brand_name']."</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>หน่วยนับ</label>
                    <select name="unit_id" class="form-control" required>
                      <?php 
                        $unit_q = mysqli_query($con, "SELECT * FROM units");
                        while($u = mysqli_fetch_array($unit_q)){
                          $selected = ($u['unit_id'] == $p_row['unit_id']) ? "selected" : "";
                          echo "<option value='".$u['unit_id']."' $selected>".$u['unit_name']."</option>";
                        }
                      ?>
                    </select>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>ราคาต้นทุน</label>
                    <input type="number" step="0.01" name="cost_price" class="form-control" value="<?php echo $p_row['cost_price']; ?>" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>ราคาขาย</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" value="<?php echo $p_row['selling_price']; ?>" required>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>จุดแจ้งเตือนของหมด</label>
                    <input type="number" name="min_stock" class="form-control" value="<?php echo $p_row['min_stock']; ?>" required>
                  </div>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>สถานที่เก็บ (Location)</label>
                    <input type="text" name="location" class="form-control" value="<?php echo $p_row['location']; ?>" required>
                  </div>
                </div>
              </div>

              <!-- ส่วนรูปภาพสินค้า -->
              <div class="row mt-3">
                <div class="col-md-12">
                  <div class="form-group">
                    <label><b>รูปภาพสินค้า</b></label>
                    <div class="d-flex align-items-center" style="gap: 20px; flex-wrap: wrap;">
                      <!-- Preview รูปปัจจุบัน -->
                      <div style="text-align:center;">
                        <p style="margin-bottom:5px; font-size:12px; color:#aaa;">รูปปัจจุบัน</p>
                        <?php 
                          $img_src = '../../product_images/' . $p_row['product_image'];
                          if(!file_exists($img_src) || $p_row['product_image'] == 'default.jpg') {
                            echo '<div style="width:100px;height:100px;background:#333;border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="material-icons" style="color:#888;font-size:40px;">image</i></div>';
                          } else {
                            echo '<img src="'.$img_src.'" id="current_img" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:2px solid #555;" />';
                          }
                        ?>
                      </div>
                      <!-- ปุ่มและ Preview ใหม่ -->
                      <div style="flex:1; min-width:200px;">
                        <!-- ซ่อน input จริง ใช้ปุ่มสวยงามแทน -->
                        <input type="file" name="product_image" id="img_upload" accept="image/*" onchange="previewImage(this)" style="display:none;">
                        <button type="button" onclick="document.getElementById('img_upload').click()" 
                                class="btn btn-warning" style="border-radius:8px; font-weight:bold;">
                          <i class="material-icons" style="vertical-align:middle; margin-right:5px; font-size:18px;">add_photo_alternate</i>
                          เลือกรูปภาพใหม่
                        </button>
                        <br>
                        <small id="file_name_label" class="text-muted" style="margin-top:6px; display:inline-block;">ยังไม่ได้เลือกไฟล์</small>
                        <br>
                        <small class="text-muted">รูปแบบที่รองรับ: JPG, PNG, WEBP, GIF (ไม่เกิน 5MB)</small>
                        <div id="new_preview" style="margin-top:10px; display:none;">
                          <p style="margin-bottom:5px; font-size:12px; color:#aaa;">ตัวอย่างรูปใหม่</p>
                          <img id="preview_img" src="" style="width:100px;height:100px;object-fit:cover;border-radius:8px;border:2px solid #f4a738;" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <br>
              <small class="text-danger">* หากต้องการแก้ไขจำนวนสต๊อกคงเหลือ กรุณาทำผ่านเมนู 'รับของเข้า' หรือ 'เบิกสินค้า' เพื่อเก็บประวัติ</small>

              <button type="submit" name="btn_update" class="btn btn-warning pull-right mt-4">อัปเดตข้อมูล</button>
              <div class="clearfix"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// ดึง packaging ที่มีอยู่แล้ว
$pkg_list = [];
$pkg_q = mysqli_query($con, "SELECT * FROM product_packaging WHERE product_id = '$edit_id' ORDER BY id");
while($pk = mysqli_fetch_assoc($pkg_q)) $pkg_list[] = $pk;
$base = $p_row['base_unit'] ?? '';
?>

<!-- ===== Packaging Section ===== -->
<div class="content" style="padding-top:0;">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-info" style="display:flex;align-items:center;justify-content:space-between;">
            <div>
              <h4 class="card-title"><i class="material-icons" style="vertical-align:middle;margin-right:6px;">inventory_2</i>สูตร Packaging</h4>
              <p class="card-category">หน่วยหลัก (Base Unit): <b><?php echo $base ?: '<span style="color:#f4a738">ยังไม่กำหนด</span>'; ?></b></p>
            </div>
          </div>
          <div class="card-body">

            <!-- ตารางแสดง packaging ที่มีอยู่ -->
            <table class="table table-hover" id="pkg-table">
              <thead class="text-info">
                <th>หน่วย Packaging</th>
                <th>Conversion Rate</th>
                <th>ตัวอย่าง</th>
                <th></th>
              </thead>
              <tbody id="pkg-tbody">
                <?php foreach($pkg_list as $pk): ?>
                <tr id="pkg-row-<?php echo $pk['id']; ?>">
                  <td><b><?php echo htmlspecialchars($pk['package_unit']); ?></b></td>
                  <td>× <?php echo $pk['units_per_package']; ?></td>
                  <td><small class="text-success">1 <?php echo $pk['package_unit']; ?> = <?php echo $pk['units_per_package']; ?> <?php echo $base; ?></small></td>
                  <td>
                    <button class="btn btn-sm btn-danger" onclick="deletePkg(<?php echo $pk['id']; ?>)">
                      <i class="material-icons" style="font-size:16px;vertical-align:middle;">delete</i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($pkg_list)): ?>
                <tr id="pkg-empty"><td colspan="4" class="text-center text-muted">ยังไม่มีสูตร Packaging</td></tr>
                <?php endif; ?>
              </tbody>
            </table>

            <!-- ฟอร์มเพิ่ม Packaging ใหม่ -->
            <div style="background:#1e2235;border-radius:10px;padding:18px 20px;margin-top:10px;">
              <h6 style="color:#00bcd4;margin-bottom:14px;"><i class="material-icons" style="vertical-align:middle;font-size:18px;">add_circle</i> เพิ่มสูตร Packaging</h6>
              <div class="row align-items-end">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>ชื่อหน่วย Packaging</label>
                    <input type="text" id="new-pkg-unit" class="form-control" placeholder="เช่น พาเหรด, ลัง, กล่อง">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>จำนวน <?php echo $base ?: 'Base Unit'; ?> ต่อ 1 หน่วย</label>
                    <input type="number" id="new-pkg-rate" class="form-control" placeholder="เช่น 40" min="0.01" step="0.01" oninput="updatePkgPreview()">
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label>ตัวอย่าง</label>
                    <div id="pkg-preview" style="color:#4caf50;font-size:13px;padding-top:8px;">—</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <button type="button" class="btn btn-info btn-block mb-3" onclick="addPkg()">
                    <i class="material-icons" style="vertical-align:middle;font-size:16px;">save</i> บันทึก
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
var PRODUCT_ID = <?php echo (int)$edit_id; ?>;
var BASE_UNIT  = '<?php echo addslashes($base); ?>';

function updatePkgPreview() {
    var unit = document.getElementById('new-pkg-unit').value.trim();
    var rate = parseFloat(document.getElementById('new-pkg-rate').value);
    var p = document.getElementById('pkg-preview');
    if(unit && rate > 0) {
        p.textContent = '1 ' + unit + ' = ' + rate + ' ' + (BASE_UNIT || 'Base Unit');
    } else {
        p.textContent = '—';
    }
}
document.getElementById('new-pkg-unit').addEventListener('input', updatePkgPreview);

function addPkg() {
    var unit = document.getElementById('new-pkg-unit').value.trim();
    var rate = parseFloat(document.getElementById('new-pkg-rate').value);
    if(!unit || !rate || rate <= 0) { alert('กรุณากรอกชื่อหน่วยและค่า Rate ให้ครบ'); return; }
    var fd = new FormData();
    fd.append('pkg_action','add');
    fd.append('product_id', PRODUCT_ID);
    fd.append('package_unit', unit);
    fd.append('units_per_package', rate);
    fetch('edit_material.php?id=' + PRODUCT_ID, {method:'POST', body:fd})
    .then(r=>r.json()).then(function(d) {
        if(d.ok) {
            // ลบแถว empty ถ้ามี
            var em = document.getElementById('pkg-empty');
            if(em) em.remove();
            // เพิ่มแถวใหม่
            var tr = document.createElement('tr');
            tr.id = 'pkg-row-' + d.id;
            tr.innerHTML = '<td><b>' + d.unit + '</b></td>'
                + '<td>× ' + d.rate + '</td>'
                + '<td><small style="color:#4caf50;">1 ' + d.unit + ' = ' + d.rate + ' ' + BASE_UNIT + '</small></td>'
                + '<td><button class="btn btn-sm btn-danger" onclick="deletePkg('+d.id+')"><i class="material-icons" style="font-size:16px;vertical-align:middle;">delete</i></button></td>';
            document.getElementById('pkg-tbody').appendChild(tr);
            document.getElementById('new-pkg-unit').value = '';
            document.getElementById('new-pkg-rate').value = '';
            document.getElementById('pkg-preview').textContent = '—';
        } else { alert('Error: ' + d.msg); }
    });
}

function deletePkg(pkg_id) {
    if(!confirm('ลบสูตร Packaging นี้ใช่ไหม?')) return;
    var fd = new FormData();
    fd.append('pkg_action','delete');
    fd.append('product_id', PRODUCT_ID);
    fd.append('pkg_id', pkg_id);
    fetch('edit_material.php?id=' + PRODUCT_ID, {method:'POST', body:fd})
    .then(r=>r.json()).then(function(d) {
        if(d.ok) {
            var row = document.getElementById('pkg-row-' + pkg_id);
            if(row) row.remove();
            // ถ้าไม่มีแถวเหลือ แสดง empty
            if(document.querySelectorAll('#pkg-tbody tr').length === 0) {
                var em = document.createElement('tr');
                em.id = 'pkg-empty';
                em.innerHTML = '<td colspan="4" class="text-center text-muted">ยังไม่มีสูตร Packaging</td>';
                document.getElementById('pkg-tbody').appendChild(em);
            }
        } else { alert('Error: ' + d.msg); }
    });
}
</script>

<?php include "footer.php"; ?>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        // แสดงชื่อไฟล์
        document.getElementById('file_name_label').textContent = '📎 ' + input.files[0].name;
        // แสดง preview รูป
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview_img').src = e.target.result;
            document.getElementById('new_preview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>