<?php
include "header.php";

$bill_found = false;
$error_msg = "";

if(isset($_POST['btn_check'])) {
    $receipt_no = mysqli_real_escape_string($con, $_POST['receipt_no']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);

    // ค้นหาบิล โดยเช็คว่าเลขที่บิลตรง และเบอร์โทรลูกค้าตรงกัน
    $sql_check = "SELECT s.*, c.customer_name, c.phone, c.address, e.full_name as emp_name 
                  FROM sales s 
                  LEFT JOIN customers c ON s.customer_id = c.customer_id 
                  LEFT JOIN employees e ON s.employee_id = e.employee_id
                  WHERE s.receipt_no = '$receipt_no' AND c.phone = '$phone'";
    
    $query_check = mysqli_query($con, $sql_check);

    if(mysqli_num_rows($query_check) > 0) {
        $bill_found = true;
        $bill = mysqli_fetch_array($query_check);
        $sale_id = $bill['sale_id'];

        // ป้ายสีการชำระเงิน
        $pay_method = "";
        if($bill['payment_method'] == 'Cash') { $pay_method = "<span style='color:green; font-weight:bold;'>เงินสด</span>"; }
        elseif($bill['payment_method'] == 'Transfer') { $pay_method = "<span style='color:blue; font-weight:bold;'>โอนเงิน</span>"; }
        else { $pay_method = "<span style='color:orange; font-weight:bold;'>เครดิต (ค้างชำระ)</span>"; }
    } else {
        $error_msg = "ไม่พบบิลเลขที่นี้ หรือเบอร์โทรศัพท์ไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง";
    }
}
?>

<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center" style="margin-bottom: 40px;">
                <h2 style="color: #D10024;">ตรวจสอบบิล / ใบเสร็จรับเงิน</h2>
                <p>กรุณากรอกเลขที่บิลและเบอร์โทรศัพท์ของคุณเพื่อดูรายละเอียดรายการสินค้า</p>
                
                <form action="" method="post" class="form-inline" style="margin-top: 20px;">
                    <input type="text" name="receipt_no" class="input" placeholder="เลขที่บิล (เช่น REC-...)" required style="width: 250px; margin-right: 10px;">
                    <input type="text" name="phone" class="input" placeholder="เบอร์โทรศัพท์ที่แจ้งไว้" required style="width: 200px; margin-right: 10px;">
                    <button type="submit" name="btn_check" class="primary-btn" style="border:none; padding: 10px 20px;">ค้นหาบิล</button>
                </form>

                <?php if($error_msg != "") { ?>
                    <div style="color: red; margin-top: 15px; font-weight: bold;"><?php echo $error_msg; ?></div>
                <?php } ?>
            </div>

            <?php if($bill_found) { ?>
            <div class="col-md-10 col-md-offset-1" style="background-color: #fff; padding: 30px; border: 1px solid #e4e7ed; box-shadow: 0px 0px 10px rgba(0,0,0,0.05);">
                
                <div class="row" style="border-bottom: 2px solid #D10024; padding-bottom: 20px; margin-bottom: 20px;">
                    <div class="col-md-6">
                        <h4>ข้อมูลลูกค้า</h4>
                        <b>ชื่อ-นามสกุล/บริษัท:</b> <?php echo $bill['customer_name']; ?><br>
                        <b>เบอร์โทร:</b> <?php echo $bill['phone']; ?><br>
                        <b>ที่อยู่จัดส่ง:</b> <?php echo ($bill['address']) ? $bill['address'] : '-'; ?>
                    </div>
                    <div class="col-md-6 text-right">
                        <h4>รายละเอียดบิล</h4>
                        <b style="color: #D10024;">เลขที่บิล: <?php echo $bill['receipt_no']; ?></b><br>
                        <b>วันที่:</b> <?php echo date('d/m/Y H:i', strtotime($bill['sale_date'])); ?><br>
                        <b>สถานะการชำระ:</b> <?php echo $pay_method; ?>
                    </div>
                </div>

                <table class="table table-striped">
                    <thead style="background-color: #fbfbfb;">
                        <tr>
                            <th>ลำดับ</th>
                            <th>รายการสินค้า</th>
                            <th class="text-center">จำนวน</th>
                            <th class="text-right">ราคา/หน่วย</th>
                            <th class="text-right">รวมเป็นเงิน</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sql_detail = "SELECT sd.*, p.product_name, u.unit_name 
                                       FROM sales_detail sd 
                                       LEFT JOIN products p ON sd.product_id = p.product_id 
                                       LEFT JOIN units u ON p.unit_id = u.unit_id
                                       WHERE sd.sale_id = '$sale_id'";
                        $res_detail = mysqli_query($con, $sql_detail);
                        $i = 1;
                        $sum_total = 0;

                        while($item = mysqli_fetch_array($res_detail)) {
                            $subtotal = $item['qty'] * $item['selling_price'];
                            $sum_total += $subtotal;
                            echo "<tr>";
                            echo "<td>".$i++."</td>";
                            echo "<td>".$item['product_name']."</td>";
                            echo "<td class='text-center'>".$item['qty']." ".$item['unit_name']."</td>";
                            echo "<td class='text-right'>".number_format($item['selling_price'], 2)."</td>";
                            echo "<td class='text-right'>".number_format($subtotal, 2)."</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-right" style="font-size: 18px;">ยอดชำระทั้งสิ้น:</th>
                            <th class="text-right" style="font-size: 18px; color: #D10024;">฿ <?php echo number_format($sum_total, 2); ?></th>
                        </tr>
                    </tfoot>
                </table>

                <div class="text-center" style="margin-top: 30px;">
                    <button onclick="window.print()" class="primary-btn" style="background-color: #333; border: none;"><i class="fa fa-print"></i> พิมพ์ / บันทึกเป็น PDF</button>
                </div>

            </div>
            <?php } ?>

        </div>
    </div>
</div>

<?php include "footer.php"; ?>