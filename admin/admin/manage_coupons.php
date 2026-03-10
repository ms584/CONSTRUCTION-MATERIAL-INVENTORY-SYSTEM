<?php
session_start();
include("../../db.php");

// เฉพาะ Admin และ Manager เท่านั้น
if(!isset($_SESSION['role']) || ($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Manager')) {
    echo "<script>alert('ไม่มีสิทธิ์เข้าถึงหน้านี้'); window.location.href='index.php';</script>";
    exit();
}

// ===== เพิ่มคูปอง =====
if(isset($_POST['btn_add_coupon'])) {
    $code  = strtoupper(trim(mysqli_real_escape_string($con, $_POST['coupon_code'])));
    $type  = mysqli_real_escape_string($con, $_POST['coupon_type']);
    $value = (float)$_POST['discount_value'];
    $cap   = ($type == 'percent' && !empty($_POST['max_discount'])) ? (float)$_POST['max_discount'] : 'NULL';
    $min   = (!empty($_POST['min_order'])) ? (float)$_POST['min_order'] : 0;

    // Validate
    $err = "";
    if(empty($code)) $err = "กรุณากรอกรหัสคูปอง";
    elseif($type == 'cash' && ($value < 100 || $value > 1000)) $err = "คูปองเงินสดต้องอยู่ระหว่าง 100–1,000 บาท";
    elseif($type == 'percent' && ($value < 1 || $value > 10)) $err = "คูปองเปอร์เซ็นต์ต้องอยู่ระหว่าง 1–10%";

    if($err) {
        echo "<script>alert('$err');</script>";
    } else {
        $cap_val = ($cap === 'NULL') ? 'NULL' : "'$cap'";
        $sql_add = "INSERT INTO coupons (coupon_code, coupon_type, discount_value, max_discount, min_order)
                    VALUES ('$code', '$type', '$value', $cap_val, '$min')";
        if(mysqli_query($con, $sql_add)) {
            echo "<script>alert('เพิ่มคูปอง $code สำเร็จ!'); window.location.href='manage_coupons.php';</script>";
        } else {
            echo "<script>alert('Error: รหัสคูปองซ้ำ หรือ " . addslashes(mysqli_error($con)) . "');</script>";
        }
    }
}

// ===== ลบคูปอง =====
if(isset($_GET['delete_id'])) {
    $del = (int)$_GET['delete_id'];
    $chk = mysqli_query($con, "SELECT is_used FROM coupons WHERE coupon_id = '$del'");
    $crow = mysqli_fetch_array($chk);
    if($crow['is_used'] == 1) {
        echo "<script>alert('คูปองนี้ถูกใช้ไปแล้ว ไม่สามารถลบได้'); window.location.href='manage_coupons.php';</script>";
    } else {
        mysqli_query($con, "DELETE FROM coupons WHERE coupon_id = '$del'");
        echo "<script>window.location.href='manage_coupons.php';</script>";
    }
}

include "sidenav.php";
include "topheader.php";
?>

<style>
    /* ===== แก้ตัวหนังสือมองไม่เห็นบน dark theme ===== */
    .card-body .form-control {
        background-color: #1a1a2e !important;
        color: #ffffff !important;
        border: 1px solid #444 !important;
    }
    .card-body .form-control::placeholder {
        color: #888 !important;
    }
    .card-body select.form-control {
        color: #ffffff !important;
        background-color: #1a1a2e !important;
    }
    .card-body select.form-control option {
        color: #000000 !important;
        background-color: #ffffff !important;
    }
</style>

<div class="content">
  <div class="container-fluid">
    <div class="row">

      <!-- ฟอร์มเพิ่มคูปอง -->
      <div class="col-md-4">
        <div class="card">
          <div class="card-header card-header-success">
            <h4 class="card-title">🎟 เพิ่มคูปองใหม่</h4>
          </div>
          <div class="card-body">
            <form action="" method="post" id="coupon-form" onsubmit="return validateCouponForm()">
              <div class="form-group">
                <label><b>รหัสคูปอง</b></label>
                <input type="text" name="coupon_code" class="form-control" placeholder="เช่น CASH500, PCT10" required style="text-transform:uppercase">
              </div>
              <div class="form-group">
                <label><b>ประเภทส่วนลด</b></label>
                <select name="coupon_type" class="form-control" id="coupon-type-select" onchange="toggleCouponFields()">
                  <option value="cash">เงินสด (100–1,000 บาท)</option>
                  <option value="percent">เปอร์เซ็นต์ (1–10%, cap ≤ 10,000 บาท)</option>
                </select>
              </div>
              <div class="form-group" id="field-cash">
                <label><b>มูลค่าส่วนลด (บาท)</b> <small class="text-muted">100–1,000</small></label>
                <input type="number" name="discount_value" class="form-control" min="100" max="1000" step="100" value="100">
              </div>
              <div class="form-group d-none" id="field-percent">
                <label><b>เปอร์เซ็นต์ส่วนลด (1–10%)</b></label>
                <input type="number" name="discount_value_pct" class="form-control" value="10" min="1" max="10" step="1">
                <label class="mt-2"><b>ส่วนลดสูงสุด (บาท)</b> <small class="text-muted">สูงสุด 10,000</small></label>
                <input type="number" name="max_discount" class="form-control" value="10000" min="1" max="10000">
              </div>
              <div class="form-group">
                <label><b>ยอดสั่งซื้อขั้นต่ำ (บาท)</b> <small class="text-muted">0 = ไม่จำกัด</small></label>
                <input type="number" name="min_order" class="form-control" value="0" min="0">
              </div>
              <button type="submit" name="btn_add_coupon" class="btn btn-success btn-block">+ เพิ่มคูปอง</button>
            </form>
          </div>
        </div>
      </div>

      <!-- รายการคูปองทั้งหมด -->
      <div class="col-md-8">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title">รายการคูปองทั้งหมด</h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="text-primary">
                  <th>รหัสคูปอง</th>
                  <th>ประเภท</th>
                  <th>มูลค่า</th>
                  <th>Cap</th>
                  <th>ยอดขั้นต่ำ</th>
                  <th>สถานะ</th>
                  <th>จัดการ</th>
                </thead>
                <tbody>
                  <?php
                  $coupons = mysqli_query($con, "SELECT c.*, s.receipt_no FROM coupons c LEFT JOIN sales s ON c.used_by_sale_id = s.sale_id ORDER BY c.coupon_id DESC");
                  if(mysqli_num_rows($coupons) > 0) {
                      while($cp = mysqli_fetch_array($coupons)) {
                          $type_label = $cp['coupon_type'] == 'cash' 
                              ? '<span class="badge" style="background:#16a34a; color:#fff; padding:4px 10px;">เงินสด</span>'
                              : '<span class="badge" style="background:#2563eb; color:#fff; padding:4px 10px;">เปอร์เซ็นต์</span>';
                          
                          $value_display = $cp['coupon_type'] == 'cash'
                              ? '฿' . number_format($cp['discount_value'], 0)
                              : $cp['discount_value'] . '%';

                          $cap_display = $cp['max_discount'] ? '฿' . number_format($cp['max_discount'], 0) : '-';
                          $min_display = $cp['min_order'] > 0 ? '฿' . number_format($cp['min_order'], 0) : 'ไม่จำกัด';

                          if($cp['is_used']) {
                              $status = '<span class="badge" style="background:#dc2626; color:#fff; padding:4px 10px;">ใช้แล้ว (บิล: '.$cp['receipt_no'].')</span>';
                              $btn = '';
                          } else {
                              $status = '<span class="badge" style="background:#16a34a; color:#fff; padding:4px 10px;">ใช้ได้</span>';
                              $btn = "<a href='manage_coupons.php?delete_id=".$cp['coupon_id']."' class='btn btn-danger btn-sm' onclick='return confirm(\"ยืนยันลบคูปอง ".$cp['coupon_code']."?\")'><i class='material-icons'>delete</i></a>";
                          }

                          echo "<tr>";
                          echo "<td><b style='letter-spacing:1px'>".$cp['coupon_code']."</b></td>";
                          echo "<td>$type_label</td>";
                          echo "<td><b>$value_display</b></td>";
                          echo "<td>$cap_display</td>";
                          echo "<td>$min_display</td>";
                          echo "<td>$status</td>";
                          echo "<td>$btn</td>";
                          echo "</tr>";
                      }
                  } else {
                      echo "<tr><td colspan='7' class='text-center'>ยังไม่มีคูปองในระบบ</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
function toggleCouponFields() {
    var type = document.getElementById('coupon-type-select').value;
    var cashInput = document.querySelector('[name="discount_value"]');
    if(type === 'percent') {
        document.getElementById('field-cash').classList.add('d-none');
        document.getElementById('field-percent').classList.remove('d-none');
        // ปิด validation ของช่อง cash ที่ซ่อนอยู่
        cashInput.removeAttribute('min');
        cashInput.removeAttribute('max');
        cashInput.removeAttribute('step');
        cashInput.value = 10;
    } else {
        document.getElementById('field-cash').classList.remove('d-none');
        document.getElementById('field-percent').classList.add('d-none');
        // คืน validation ของช่อง cash
        cashInput.setAttribute('min', '100');
        cashInput.setAttribute('max', '1000');
        cashInput.setAttribute('step', '100');
        cashInput.value = 100;
    }
}
// sync discount_value_pct → discount_value
document.querySelector('[name="discount_value_pct"]').addEventListener('change', function(){
    document.querySelector('[name="discount_value"]').value = this.value;
});

// ===== Popup แจ้งเตือนเมื่อกรอกเกิน =====
function validateCouponForm() {
    var type = document.getElementById('coupon-type-select').value;
    var errors = [];

    if(type === 'cash') {
        var val = parseFloat(document.querySelector('[name="discount_value"]').value);
        if(val < 100 || val > 1000) errors.push('⚠️ มูลค่าส่วนลดเงินสดต้องอยู่ระหว่าง 100–1,000 บาท (กรอก: ' + val + ')');
    } else {
        var pct = parseFloat(document.querySelector('[name="discount_value_pct"]').value);
        var cap = parseFloat(document.querySelector('[name="max_discount"]').value);
        if(pct < 1 || pct > 10) errors.push('⚠️ เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 1–10% (กรอก: ' + pct + '%)');
        if(cap > 10000) errors.push('⚠️ ส่วนลดสูงสุดต้องไม่เกิน 10,000 บาท (กรอก: ' + cap.toLocaleString() + ')');
    }

    if(errors.length > 0) {
        alert(errors.join('\n\n'));
        return false;
    }
    return true;
}
</script>

<?php include "footer.php"; ?>
