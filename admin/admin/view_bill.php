<?php
session_start();
include("../../db.php");

if(!isset($_GET['sale_id'])) {
    echo "<script>window.location.href='salesofday.php';</script>";
    exit();
}
$sale_id = $_GET['sale_id'];

// 1. ดึงข้อมูลหัวบิล พร้อม JOIN ชื่อพนักงาน และ ชื่อลูกค้า
$sql_header = "SELECT s.*, e.full_name, c.customer_name, c.address, c.phone 
               FROM sales s 
               LEFT JOIN employees e ON s.employee_id = e.employee_id 
               LEFT JOIN customers c ON s.customer_id = c.customer_id
               WHERE s.sale_id = '$sale_id'";
$query_header = mysqli_query($con, $sql_header);
$bill = mysqli_fetch_array($query_header);

// กำหนดป้ายสีการชำระเงิน
$pay_method = "";
if($bill['payment_method'] == 'Cash') { $pay_method = "<span class='badge badge-success'>เงินสด</span>"; }
elseif($bill['payment_method'] == 'Transfer') { $pay_method = "<span class='badge badge-info'>โอนเงิน</span>"; }
else { $pay_method = "<span class='badge badge-warning'>เครดิต </span>"; }

include "sidenav.php";
include "topheader.php";
?>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-10 offset-md-1">
        <div class="card">
          <div class="card-header card-header-info" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 class="card-title">ใบเสร็จรับเงิน / ใบส่งสินค้า</h4>
                <p class="card-category">เลขที่เอกสาร: <b><?php echo $bill['receipt_no']; ?></b></p>
            </div>
            <a href="salesofday.php" class="btn btn-default btn-sm d-print-none">กลับหน้ารายการ</a>
          </div>
          <div class="card-body">
            
            <div class="row mb-4 mt-2" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px;">
                <div class="col-md-6">
                    <h5 class="text-info">ข้อมูลลูกค้า (Customer)</h5>
                    <b>ชื่อ:</b> <?php echo ($bill['customer_name']) ? $bill['customer_name'] : 'ลูกค้าทั่วไป (Walk-in)'; ?><br>
                    <b>เบอร์โทร:</b> <?php echo ($bill['phone']) ? $bill['phone'] : '-'; ?><br>
                    <b>ที่อยู่จัดส่ง:</b> <?php echo ($bill['address']) ? $bill['address'] : '-'; ?>
                </div>
                <div class="col-md-6 text-right">
                    <h5 class="text-info">ข้อมูลเอกสาร (Document)</h5>
                    <b>วันที่:</b> <?php echo date('d/m/Y H:i:s', strtotime($bill['sale_date'])); ?><br>
                    <b>ผู้เปิดบิล:</b> <?php echo ($bill['full_name']) ? $bill['full_name'] : 'Admin'; ?><br>
                    <b>วิธีชำระเงิน:</b> <?php echo $pay_method; ?>
                </div>
            </div>

            <h5 class="text-primary mt-4">รายการวัสดุก่อสร้าง</h5>
            <div class="table-responsive">
              <table class="table table-bordered">
                <thead class="text-info">
                  <tr>
                    <th>ลำดับ</th>
                    <th>รหัสสินค้า</th>
                    <th>ชื่อรายการสินค้า</th>
                    <th class="text-center">จำนวน</th>
                    <th class="text-right">ราคาต่อหน่วย (฿)</th>
                    <th class="text-right">รวมเป็นเงิน (฿)</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $sql_detail = "SELECT sd.*, p.product_code, p.product_name, u.unit_name 
                                   FROM sales_detail sd 
                                   LEFT JOIN products p ON sd.product_id = p.product_id 
                                   LEFT JOIN units u ON p.unit_id = u.unit_id
                                   WHERE sd.sale_id = '$sale_id'";
                    
                    $result_detail = mysqli_query($con, $sql_detail);
                    $i = 1;
                    $sum_total = 0;

                    if(mysqli_num_rows($result_detail) > 0) {
                        while($item = mysqli_fetch_array($result_detail)) {
                            $subtotal = $item['qty'] * $item['selling_price'];
                            $sum_total += $subtotal;

                            echo "<tr>";
                            echo "<td>".$i++."</td>";
                            echo "<td>".$item['product_code']."</td>";
                            echo "<td>".$item['product_name']."</td>";
                            echo "<td class='text-center'>".$item['qty']." ".$item['unit_name']."</td>";
                            echo "<td class='text-right'>".number_format($item['selling_price'], 2)."</td>";
                            echo "<td class='text-right'>".number_format($subtotal, 2)."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-danger'>ไม่พบรายการสินค้า</td></tr>";
                    }
                  ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right text-primary" style="font-size: 18px;">ยอดรวมทั้งสิ้น:</th>
                        <th class="text-right text-primary" style="font-size: 18px;"><b><?php echo number_format($sum_total, 2); ?> ฿</b></th>
                    </tr>
                </tfoot>
              </table>
            </div>

            <div class="mt-4 text-center d-print-none">
                <button onclick="window.print()" class="btn btn-warning"><i class="material-icons">print</i> พิมพ์ใบเสร็จ</button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
/* ซ่อนปุ่มต่างๆ เวลากดสั่งพิมพ์ */
@media print {
    .d-print-none, .sidebar, .navbar { display: none !important; }
    .main-panel { width: 100% !important; margin: 0 !important; }
    .card-header { background-color: #00bcd4 !important; color: white !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php include "footer.php"; ?>