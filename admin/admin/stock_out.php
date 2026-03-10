<?php
session_start();
include("../../db.php");

$role = $_SESSION['role'] ?? 'Cashier';

// ===== COUPON CHECK AJAX =====
if(isset($_POST['ajax_check_coupon'])) {
    $code  = strtoupper(trim(mysqli_real_escape_string($con, $_POST['coupon_code'])));
    $total = (float)$_POST['subtotal'];

    $q = mysqli_query($con, "SELECT * FROM coupons WHERE coupon_code='$code' AND is_used=0");
    if(mysqli_num_rows($q) == 0) {
        echo json_encode(['ok'=>false, 'msg'=>'ไม่พบคูปองหรือถูกใช้แล้ว']);
    } else {
        $cp = mysqli_fetch_array($q);
        if($total < $cp['min_order'] && $cp['min_order'] > 0) {
            echo json_encode(['ok'=>false, 'msg'=>'ยอดสั่งซื้อขั้นต่ำ ฿'.number_format($cp['min_order'],0)]);
        } else {
            $discount = 0;
            $label    = '';
            if($cp['coupon_type'] == 'cash') {
                $discount = $cp['discount_value'];
                $label    = 'คูปองเงินสด ฿'.number_format($discount,0);
            } else {
                $raw = $total * ($cp['discount_value'] / 100);
                $discount = min($raw, $cp['max_discount'] ?? $raw);
                $label    = 'คูปอง '.$cp['discount_value'].'% (cap ฿'.number_format($cp['max_discount'],0).')';
            }
            echo json_encode([
                'ok'          => true,
                'coupon_id'   => $cp['coupon_id'],
                'discount'    => $discount,
                'label'       => $label,
                'msg'         => 'ใช้คูปอง '.$cp['coupon_code'].': ลด ฿'.number_format($discount,2)
            ]);
        }
    }
    exit();
}

// ===== SUBMIT BILL =====
if(isset($_POST['btn_stock_out'])) {
    $payment_method = $_POST['payment_method'];
    $payment_status = $_POST['payment_status'];
    $customer_id    = !empty($_POST['customer_id']) ? "'".(int)$_POST['customer_id']."'" : "NULL";
    $employee_id    = $_SESSION['employee_id'] ?? 1;
    $receipt_no     = "REC-" . date('YmdHis');

    $product_ids = $_POST['product_id'];
    $qtys        = $_POST['qty'];
    $prices      = $_POST['price'];

    // คำนวณ subtotal
    $subtotal = 0;
    for($i = 0; $i < count($product_ids); $i++) {
        if(empty($product_ids[$i]) || empty($qtys[$i])) continue;
        $subtotal += (float)$qtys[$i] * (float)$prices[$i];
    }

    // ส่วนลด coupon
    $discount_amount = 0;
    $discount_type   = '';
    $coupon_id_used  = 'NULL';

    $coupon_code_post = strtoupper(trim($_POST['coupon_code'] ?? ''));
    if(!empty($coupon_code_post)) {
        $cpq = mysqli_query($con, "SELECT * FROM coupons WHERE coupon_code='$coupon_code_post' AND is_used=0");
        if(mysqli_num_rows($cpq) > 0) {
            $cp = mysqli_fetch_array($cpq);
            if($cp['coupon_type'] == 'cash') {
                $discount_amount = (float)$cp['discount_value'];
                $discount_type   = "คูปองเงินสด ฿" . number_format($discount_amount, 0);
            } else {
                $raw = $subtotal * ($cp['discount_value'] / 100);
                $discount_amount = min($raw, (float)($cp['max_discount'] ?? $raw));
                $discount_type   = "คูปอง " . $cp['discount_value'] . "% (cap ฿" . number_format($cp['max_discount'], 0) . ")";
            }
            $coupon_id_used = $cp['coupon_id'];
        }
    }

    // ส่วนลดมือ (Manager/Admin เท่านั้น)
    if(($role == 'Manager' || $role == 'Admin') && !empty($_POST['manual_discount'])) {
        $manual = (float)$_POST['manual_discount'];
        $manual_max_pct = $subtotal * 0.10;          // 10% ของ subtotal
        $manual_cap     = 50000;
        $manual = min($manual, $manual_max_pct, $manual_cap);
        if($manual > 0) {
            $discount_amount += $manual;
            $pct = round(($manual / $subtotal) * 100, 2);
            $discount_type = ($discount_type ? $discount_type . ' + ' : '') 
                           . "ส่วนลดผู้จัดการ ฿" . number_format($manual, 2) . " ($pct%)";
        }
    }

    $discount_amount = min($discount_amount, $subtotal); // ไม่ลดเกินยอด
    $total_amount    = $subtotal - $discount_amount;
    $dt_escaped      = mysqli_real_escape_string($con, $discount_type);

    // INSERT หัวบิล
    $sql_sale = "INSERT INTO sales (receipt_no, employee_id, customer_id, payment_method, payment_status, 
                                    total_amount, subtotal_amount, discount_amount, discount_type, coupon_id)
                 VALUES ('$receipt_no', '$employee_id', $customer_id, '$payment_method', '$payment_status',
                         '$total_amount', '$subtotal', '$discount_amount', '$dt_escaped', $coupon_id_used)";

    if(mysqli_query($con, $sql_sale)) {
        $sale_id    = mysqli_insert_id($con);
        $stock_error = "";

        // ตรวจสอบ stock
        for($i = 0; $i < count($product_ids); $i++) {
            if(empty($product_ids[$i]) || empty($qtys[$i])) continue;
            $p_id = mysqli_real_escape_string($con, $product_ids[$i]);
            $q    = (float)$qtys[$i];
            $sr   = mysqli_fetch_array(mysqli_query($con, "SELECT product_name, stock_qty FROM products WHERE product_id='$p_id'"));
            if($q > (float)$sr['stock_qty']) {
                $stock_error = "สินค้า \"{$sr['product_name']}\" มีในสต็อกเพียง " . (int)$sr['stock_qty'] . " ชิ้น แต่ขอเบิก " . (int)$q . " ชิ้น!";
                break;
            }
        }

        if($stock_error != "") {
            mysqli_query($con, "DELETE FROM sales WHERE sale_id='$sale_id'");
            echo "<script>alert('ไม่สามารถบันทึกได้!\\n\\n" . addslashes($stock_error) . "\\n\\nกรุณาตรวจสอบจำนวนสินค้าใหม่อีกครั้ง');</script>";
        } else {
            for($i = 0; $i < count($product_ids); $i++) {
                if(empty($product_ids[$i]) || empty($qtys[$i])) continue;
                $p_id    = mysqli_real_escape_string($con, $product_ids[$i]);
                $q       = (float)$qtys[$i];
                $p_price = (float)$prices[$i];
                mysqli_query($con, "INSERT INTO sales_detail (sale_id, product_id, qty, selling_price) VALUES ('$sale_id','$p_id','$q','$p_price')");
                mysqli_query($con, "UPDATE products SET stock_qty = stock_qty - $q WHERE product_id='$p_id'");
            }
            // Mark coupon used
            if($coupon_id_used !== 'NULL') {
                mysqli_query($con, "UPDATE coupons SET is_used=1, used_by_sale_id='$sale_id' WHERE coupon_id='$coupon_id_used'");
            }
            echo "<script>alert('ทำรายการเบิก/ขายสำเร็จ ออกบิลเรียบร้อย!'); window.location.href='salesofday.php';</script>";
        }
    } else {
        echo "<script>alert('Error: " . addslashes(mysqli_error($con)) . "');</script>";
    }
}

include "sidenav.php";
include "topheader.php";
?>
<style>
    select.form-control option { color: #000 !important; background-color: #fff !important; }
    select.form-control { color: #fff !important; }
    input.form-control.price-input { background-color: #1a1a2e !important; color: #fff !important; border: 1px solid #444 !important; }
    input.form-control.price-input::placeholder { color: #888 !important; }
    .discount-box { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 10px; padding: 18px 20px; margin-top: 18px; }
    .discount-box label { color: #aaa; font-size: 13px; margin-bottom:4px; }
    .coupon-result { padding: 8px 14px; border-radius: 6px; font-size: 13px; margin-top: 8px; display:none; }
    .total-summary { background: rgba(255,255,255,0.04); border-radius: 8px; padding: 14px 18px; margin-top: 14px; }
    .total-summary .row-line { display:flex; justify-content:space-between; padding: 4px 0; font-size:14px; }
    .total-summary .net-line { font-size: 18px; font-weight: 700; color: #D10024; border-top: 1px solid rgba(255,255,255,0.15); padding-top: 8px; margin-top: 4px; }
</style>

<div class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header card-header-warning">
        <h4 class="card-title">เบิกสินค้า / ขายหน้าร้าน (ออกบิล)</h4>
      </div>
      <div class="card-body">
        <form action="" method="post" id="sale-form">

          <!-- ข้อมูลลูกค้า + ชำระเงิน -->
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="text-primary"><b>เลือกลูกค้า / ผู้รับเหมา</b></label>
              <select name="customer_id" class="form-control">
                <option value="">-- ลูกค้าทั่วไป (Walk-in) --</option>
                <?php 
                  $cq = mysqli_query($con, "SELECT * FROM customers ORDER BY customer_name ASC");
                  while($c = mysqli_fetch_array($cq)){
                      echo "<option value='{$c['customer_id']}'>{$c['customer_name']} (โทร: {$c['phone']})</option>";
                  }
                ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="text-primary"><b>สถานะการชำระเงิน</b></label>
              <select name="payment_status" class="form-control" required>
                <option value="ชำระแล้ว">ชำระแล้ว (Paid)</option>
                <option value="ค้างชำระ">ค้างชำระ (Unpaid)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="text-primary"><b>รูปแบบการชำระเงิน</b></label>
              <select name="payment_method" class="form-control" required>
                <option value="Cash">เงินสด (Cash)</option>
                <option value="Transfer">โอนเงิน (Transfer)</option>
                <option value="Credit">เครดิต (Credit)</option>
              </select>
            </div>
          </div>

          <!-- ตารางสินค้า -->
          <table class="table table-bordered mt-3">
            <thead class="text-primary">
              <tr>
                <th width="50%">เลือกสินค้า</th>
                <th width="15%">จำนวน</th>
                <th width="20%">ราคาต่อหน่วย</th>
                <th width="10%">รวม</th>
                <th width="5%">ลบ</th>
              </tr>
            </thead>
            <tbody id="item-tbody">
              <tr>
                <td>
                  <select name="product_id[]" class="form-control prod-select" required>
                    <option value="" data-price="0">-- เลือกสินค้า --</option>
                    <?php
                      $pq = mysqli_query($con, "SELECT * FROM products WHERE stock_qty > 0 ORDER BY product_name ASC");
                      while($p = mysqli_fetch_array($pq)){
                          echo "<option value='{$p['product_id']}' data-price='{$p['selling_price']}'>[{$p['product_code']}] {$p['product_name']} (เหลือ: {$p['stock_qty']})</option>";
                      }
                    ?>
                  </select>
                </td>
                <td><input type="number" name="qty[]" class="form-control qty-input" min="1" placeholder="จำนวน" required></td>
                <td><input type="number" step="0.01" name="price[]" class="form-control price-input" placeholder="ราคา" readonly required></td>
                <td><span class="row-subtotal text-warning" style="font-weight:700">0.00</span></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="material-icons">close</i></button></td>
              </tr>
            </tbody>
          </table>

          <button type="button" class="btn btn-info btn-sm" id="add-row"><i class="material-icons">add</i> เพิ่มรายการสินค้า</button>

          <!-- ===== DISCOUNT BOX ===== -->
          <div class="discount-box">
            <h5 style="color:#ffd740; margin-bottom:14px;">💰 ส่วนลด</h5>

            <!-- สรุปยอด -->
            <div class="total-summary" id="total-summary">
              <div class="row-line"><span>ยอดรวม (ก่อนลด)</span><span id="display-subtotal">฿ 0.00</span></div>
              <div class="row-line" id="display-discount-row" style="display:none; color:#22c55e;">
                <span id="display-discount-label">ส่วนลด</span>
                <span id="display-discount-val">- ฿ 0.00</span>
              </div>
              <div class="row-line net-line"><span>ยอดชำระสุทธิ</span><span id="display-net">฿ 0.00</span></div>
            </div>

            <!-- คูปอง (ทุก role) -->
            <div class="row mt-3">
              <div class="col-md-6">
                <label>🎟 รหัสคูปอง <small class="text-muted">(Cashier และ Manager ใช้ได้)</small></label>
                <div class="input-group">
                  <input type="text" name="coupon_code" id="coupon-code-input" class="form-control" placeholder="กรอกรหัสคูปอง" style="text-transform:uppercase">
                  <div class="input-group-append">
                    <button type="button" class="btn btn-info" id="btn-check-coupon">ตรวจสอบ</button>
                  </div>
                </div>
                <div class="coupon-result" id="coupon-result"></div>
                <!-- hidden fields -->
                <input type="hidden" name="coupon_id_validated" id="coupon-id-hidden" value="">
                <input type="hidden" name="coupon_discount" id="coupon-discount-hidden" value="0">
              </div>

              <?php if($role == 'Manager' || $role == 'Admin'): ?>
              <!-- ส่วนลดมือ (Manager/Admin) -->
              <div class="col-md-6">
                <label>✏️ ส่วนลดจากผู้จัดการ (กรอกเองได้สูงสุด 10%, ไม่เกิน ฿50,000)</label>
                <div class="input-group">
                  <input type="number" name="manual_discount" id="manual-discount-input" class="form-control"
                         placeholder="บาท" min="0" step="0.01" value="0">
                  <div class="input-group-append">
                    <span class="input-group-text" style="color:#333;" id="manual-pct-display">0%</span>
                  </div>
                </div>
                <small class="text-muted">10% ของยอดรวม = <span id="max-manual-val">฿ 0.00</span></small>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <div class="clearfix mt-3">
            <button type="submit" name="btn_stock_out" class="btn btn-warning pull-right btn-lg">
              บันทึกบิลขาย &amp; ตัดสต๊อก
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/core/jquery.min.js"></script>
<script>
$(document).ready(function(){

    // ===== คำนวณยอดรวม =====
    function recalc() {
        var subtotal = 0;
        $('#item-tbody tr').each(function(){
            var q = parseFloat($(this).find('.qty-input').val()) || 0;
            var p = parseFloat($(this).find('.price-input').val()) || 0;
            var s = q * p;
            $(this).find('.row-subtotal').text(s.toFixed(2));
            subtotal += s;
        });

        var couponDisc  = parseFloat($('#coupon-discount-hidden').val()) || 0;
        var manualDisc  = parseFloat($('#manual-discount-input').val()) || 0;
        var maxManual   = subtotal * 0.10;
        var maxCap      = 50000;
        manualDisc = Math.min(manualDisc, maxManual, maxCap);

        var totalDisc = couponDisc + manualDisc;
        totalDisc = Math.min(totalDisc, subtotal);
        var net = subtotal - totalDisc;

        $('#display-subtotal').text('฿ ' + subtotal.toFixed(2));
        $('#display-net').text('฿ ' + net.toFixed(2));
        $('#max-manual-val').text('฿ ' + maxManual.toFixed(2));

        // % display
        if(subtotal > 0 && manualDisc > 0)
            $('#manual-pct-display').text((manualDisc/subtotal*100).toFixed(1) + '%');
        else
            $('#manual-pct-display').text('0%');

        if(totalDisc > 0) {
            var label = '';
            if(couponDisc > 0 && manualDisc > 0)      label = 'ส่วนลดรวม';
            else if(couponDisc > 0)                    label = 'ส่วนลดคูปอง';
            else                                       label = 'ส่วนลดผู้จัดการ';
            $('#display-discount-label').text(label);
            $('#display-discount-val').text('- ฿ ' + totalDisc.toFixed(2));
            $('#display-discount-row').show();
        } else {
            $('#display-discount-row').hide();
        }
    }

    // ===== เพิ่ม / ลบ แถว =====
    $("#add-row").click(function(){
        var newRow = $("#item-tbody tr:first").clone();
        newRow.find("input").val("");
        newRow.find("select").val("");
        newRow.find(".row-subtotal").text("0.00");
        $("#item-tbody").append(newRow);
    });
    $("body").on("click", ".remove-row", function(){
        if($("#item-tbody tr").length > 1){ $(this).closest("tr").remove(); recalc(); }
        else alert("ต้องมีสินค้าอย่างน้อย 1 รายการในบิล");
    });

    // ===== เลือกสินค้า → ใส่ราคา =====
    $("body").on("change", ".prod-select", function(){
        var price = $(this).find(":selected").data("price");
        $(this).closest("tr").find(".price-input").val(price);
        recalc();
    });
    $("body").on("input", ".qty-input", recalc);
    $("body").on("input", "#manual-discount-input", recalc);

    // ===== ตรวจสอบคูปอง =====
    $("#btn-check-coupon").click(function(){
        var code     = $("#coupon-code-input").val().trim().toUpperCase();
        var subtotal = 0;
        $('#item-tbody tr').each(function(){
            subtotal += (parseFloat($(this).find('.qty-input').val())||0) * (parseFloat($(this).find('.price-input').val())||0);
        });
        if(!code){ alert("กรุณากรอกรหัสคูปอง"); return; }

        $.post("", { ajax_check_coupon:1, coupon_code:code, subtotal:subtotal }, function(data){
            var res = JSON.parse(data);
            var box = $("#coupon-result");
            if(res.ok) {
                box.css({ display:'block', background:'rgba(22,163,74,0.15)', border:'1px solid #16a34a', color:'#4ade80' });
                box.html('✅ ' + res.msg);
                $("#coupon-id-hidden").val(res.coupon_id);
                $("#coupon-discount-hidden").val(res.discount);
            } else {
                box.css({ display:'block', background:'rgba(220,38,38,0.1)', border:'1px solid #dc2626', color:'#f87171' });
                box.html('❌ ' + res.msg);
                $("#coupon-id-hidden").val("");
                $("#coupon-discount-hidden").val(0);
            }
            recalc();
        });
    });

    // ล้างคูปองถ้าแก้ไข code
    $("#coupon-code-input").on("input", function(){
        $("#coupon-id-hidden").val("");
        $("#coupon-discount-hidden").val(0);
        $("#coupon-result").hide();
        recalc();
    });

    recalc();
});
</script>

<?php include "footer.php"; ?>