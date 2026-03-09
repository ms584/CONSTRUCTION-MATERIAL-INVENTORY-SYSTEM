<?php
session_start();
include("../../db.php");

if(!isset($_GET['id'])) {
    echo "<script>window.location.href='receiving_history.php';</script>";
    exit();
}
$receive_id = $_GET['id'];

// ดึงหัวบิล
$sql_header = "SELECT r.*, s.supplier_name, s.phone, e.full_name 
               FROM receiving r 
               LEFT JOIN suppliers s ON r.supplier_id = s.supplier_id 
               LEFT JOIN employees e ON r.employee_id = e.employee_id 
               WHERE r.receive_id = '$receive_id'";
$query_header = mysqli_query($con, $sql_header);
$bill = mysqli_fetch_array($query_header);

include "sidenav.php";
include "topheader.php";
?>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-10 offset-md-1">
        <div class="card">
          <div class="card-header card-header-success" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 class="card-title">รายละเอียดการรับของเข้า</h4>
                <p class="card-category">รหัสอ้างอิง: <b>REC-IN<?php echo sprintf('%04d', $bill['receive_id']); ?></b></p>
            </div>
            <a href="receiving_history.php" class="btn btn-default btn-sm d-print-none">กลับหน้ารายการ</a>
          </div>
          <div class="card-body">
            
            <div class="row mb-4 mt-2" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
                <div class="col-md-6">
                    <h5 class="text-success">ข้อมูลบริษัท / ผู้ส่ง (Supplier)</h5>
                    <b>ชื่อบริษัท:</b> <?php echo $bill['supplier_name']; ?><br>
                    <b>เบอร์ติดต่อ:</b> <?php echo $bill['phone']; ?><br>
                    <b>เลขที่บิลโรงงาน:</b> <?php echo ($bill['invoice_no']) ? $bill['invoice_no'] : '-'; ?>
                </div>
                <div class="col-md-6 text-right">
                    <h5 class="text-success">ข้อมูลเอกสารรับเข้า</h5>
                    <b>วันที่รับเข้า:</b> <?php echo date('d/m/Y H:i:s', strtotime($bill['receive_date'])); ?><br>
                    <b>พนักงานผู้รับของ:</b> <?php echo ($bill['full_name']) ? $bill['full_name'] : 'Admin'; ?><br>
                    <b>หมายเหตุ:</b> <?php echo ($bill['note']) ? $bill['note'] : '-'; ?>
                </div>
            </div>

            <h5 class="text-primary mt-4">รายการสินค้าที่รับเข้าโกดัง</h5>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="text-success">
                  <tr>
                    <th>ลำดับ</th>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อรายการสินค้า</th>
                    <th class="text-center">จำนวนที่รับ</th>
                    <th class="text-right">ต้นทุนต่อหน่วย (฿)</th>
                    <th class="text-right">รวมเป็นเงิน (฿)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql_detail = "SELECT rd.*, p.product_code, p.product_name, u.unit_name 
                                   FROM receiving_detail rd 
                                   LEFT JOIN products p ON rd.product_id = p.product_id 
                                   LEFT JOIN units u ON p.unit_id = u.unit_id
                                   WHERE rd.receive_id = '$receive_id'";
                    
                    $result_detail = mysqli_query($con, $sql_detail);
                    $i = 1;
                    $sum_total = 0;

                    if(mysqli_num_rows($result_detail) > 0) {
                        while($item = mysqli_fetch_array($result_detail)) {
                            $subtotal = $item['qty'] * $item['cost_price'];
                            $sum_total += $subtotal;

                            echo "<tr>";
                            echo "<td>".$i++."</td>";
                            echo "<td>".$item['product_code']."</td>";
                            echo "<td>".$item['product_name']."</td>";
                            echo "<td class='text-center'>".$item['qty']." ".$item['unit_name']."</td>";
                            echo "<td class='text-right'>".number_format($item['cost_price'], 2)."</td>";
                            echo "<td class='text-right'>".number_format($subtotal, 2)."</td>";
                            echo "</tr>";
                        }
                    }
                  ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right text-success" style="font-size: 18px;">รวมต้นทุนลอตนี้ทั้งสิ้น:</th>
                        <th class="text-right text-danger" style="font-size: 18px;"><b><?php echo number_format($sum_total, 2); ?> ฿</b></th>
                    </tr>
                </tfoot>
              </table>
            </div>

            <div class="mt-4 text-center d-print-none">
                <button onclick="window.print()" class="btn btn-warning"><i class="material-icons">print</i> พิมพ์เอกสาร</button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@media print {
    .d-print-none, .sidebar, .navbar { display: none !important; }
    .main-panel { width: 100% !important; margin: 0 !important; }
    .card-header { background-color: #4caf50 !important; color: white !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php include "footer.php"; ?>