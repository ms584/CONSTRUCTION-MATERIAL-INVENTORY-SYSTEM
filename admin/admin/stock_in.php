<?php
session_start();
include("../../db.php");

if(isset($_POST['btn_stock_in'])) {
    $supplier_id = $_POST['supplier_id'];
    $invoice_no = $_POST['invoice_no'];
    $note = $_POST['note'];
    $employee_id = 1;

    // รับข้อมูล Array
    $product_ids = $_POST['product_id'];
    $qtys = $_POST['qty'];
    $cost_prices = $_POST['cost_price'];

    $total_amount = 0;
    for($i = 0; $i < count($product_ids); $i++) {
        $total_amount += ($qtys[$i] * $cost_prices[$i]);
    }

    // บันทึกหัวบิลรับเข้า
    $sql_receive = "INSERT INTO receiving (supplier_id, invoice_no, employee_id, total_amount, note) 
                    VALUES ('$supplier_id', '$invoice_no', '$employee_id', '$total_amount', '$note')";
    
    if(mysqli_query($con, $sql_receive)) {
        $receive_id = mysqli_insert_id($con);

        // วนลูปบันทึกรายละเอียด และบวกสต๊อก
        for($i = 0; $i < count($product_ids); $i++) {
            $p_id = $product_ids[$i];
            $q = $qtys[$i];
            $cp = $cost_prices[$i];

            mysqli_query($con, "INSERT INTO receiving_detail (receive_id, product_id, qty, cost_price) 
                                VALUES ('$receive_id', '$p_id', '$q', '$cp')");
            
            // บวกสต๊อก และอัปเดตต้นทุนล่าสุด
            mysqli_query($con, "UPDATE products SET stock_qty = stock_qty + $q, cost_price = '$cp' WHERE product_id = '$p_id'");
        }

        echo "<script>alert('บันทึกรับสินค้าเข้าโกดังสำเร็จ!'); window.location.href='products_list.php';</script>";
    } else {
        echo "<script>alert('Error: " . mysqli_error($con) . "');</script>";
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
    <div class="card">
      <div class="card-header card-header-success">
        <h4 class="card-title">รับสินค้าเข้าโกดัง </h4>
      </div>
      <div class="card-body">
        <form action="" method="post">
          <div class="row mb-3">
            <div class="col-md-4">
              <label>ผู้จัดจำหน่าย (ซัพพลายเออร์)</label>
              <select name="supplier_id" class="form-control" required>
                <option value="">-- เลือกบริษัท --</option>
                <?php 
                  $sup_q = mysqli_query($con, "SELECT * FROM suppliers");
                  while($s = mysqli_fetch_array($sup_q)){ echo "<option value='".$s['supplier_id']."'>".$s['supplier_name']."</option>"; }
                ?>
              </select>
            </div>
            <div class="col-md-4">
              <label>เลขที่บิลโรงงาน</label>
              <input type="text" name="invoice_no" class="form-control">
            </div>
            <div class="col-md-4">
              <label>หมายเหตุ</label>
              <input type="text" name="note" class="form-control">
            </div>
          </div>

          <table class="table table-bordered">
            <thead class="text-success">
              <tr>
                <th width="50%">เลือกสินค้า</th>
                <th width="20%">จำนวนที่รับเข้า</th>
                <th width="20%">ต้นทุนต่อหน่วย</th>
                <th width="10%">ลบ</th>
              </tr>
            </thead>
            <tbody id="item-tbody">
              <tr>
                <td>
                  <select name="product_id[]" class="form-control prod-select" required>
                    <option value="" data-cost="0">-- เลือกสินค้า --</option>
                    <?php 
                      $prod_query = mysqli_query($con, "SELECT * FROM products ORDER BY product_name ASC");
                      while($p = mysqli_fetch_array($prod_query)){
                        echo "<option value='".$p['product_id']."' data-cost='".$p['cost_price']."'>[".$p['product_code']."] ".$p['product_name']."</option>";
                      }
                    ?>
                  </select>
                </td>
                <td><input type="number" name="qty[]" class="form-control" min="1" required></td>
                <td><input type="number" step="0.01" name="cost_price[]" class="form-control cost-input" required></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="material-icons">close</i></button></td>
              </tr>
            </tbody>
          </table>

          <button type="button" class="btn btn-info btn-sm" id="add-row"><i class="material-icons">add</i> เพิ่มรายการสินค้า</button>
          
          <button type="submit" name="btn_stock_in" class="btn btn-success pull-right">บันทึกสต๊อกรับเข้า</button>
          <div class="clearfix"></div>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="assets/js/core/jquery.min.js"></script>
<script>
$(document).ready(function(){
    $("#add-row").click(function(){
        var newRow = $("#item-tbody tr:first").clone();
        newRow.find("input").val(""); 
        newRow.find("select").val(""); 
        $("#item-tbody").append(newRow);
    });

    $("body").on("click", ".remove-row", function(){
        if($("#item-tbody tr").length > 1){
            $(this).closest("tr").remove();
        } else {
            alert("ต้องมีอย่างน้อย 1 รายการ");
        }
    });

    // ดึงต้นทุนเดิมมาแสดงให้ดูเป็นไกด์ไลน์ 
    $("body").on("change", ".prod-select", function(){
        var cost = $(this).find(":selected").data("cost");
        $(this).closest("tr").find(".cost-input").val(cost);
    });
});
</script>

<?php include "footer.php"; ?>