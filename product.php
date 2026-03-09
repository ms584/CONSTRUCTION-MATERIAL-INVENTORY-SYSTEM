<?php
include "header.php";

// เช็คว่ามีการส่ง ID สินค้ามาหรือไม่
if(!isset($_GET['id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}
$product_id = $_GET['id'];

// ดึงข้อมูล JOIN ตารางยี่ห้อและหน่วยนับมาด้วย
$sql = "SELECT p.*, c.category_name, b.brand_name, u.unit_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN brands b ON p.brand_id = b.brand_id
        LEFT JOIN units u ON p.unit_id = u.unit_id
        WHERE p.product_id = '$product_id'";

$query = mysqli_query($con, $sql);
$product = mysqli_fetch_array($query);
?>

<div class="section">
    <div class="container">
        <div class="row" style="margin-top: 30px; margin-bottom: 50px;">
            
            <div class="col-md-5">
                <div id="product-main-img" style="border: 1px solid #E4E7ED; padding: 10px; border-radius: 5px;">
                    <img src="product_images/<?php echo $product['product_image']; ?>" alt="" style="width:100%; object-fit:contain; border-radius: 5px;">
                </div>
            </div>

            <div class="col-md-7">
                <div class="product-details">
                    <h2 class="product-name" style="font-size: 28px;"><?php echo $product['product_name']; ?></h2>
                    
                    <div style="margin-bottom: 20px;">
                        <h3 class="product-price" style="display:inline-block; font-size: 32px; color: #D10024;">
                            <?php echo number_format($product['selling_price'], 2); ?> ฿
                        </h3>
                        <span style="font-size: 16px; margin-left: 15px;">
                            / <?php echo $product['unit_name']; ?>
                        </span>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <?php if($product['stock_qty'] > 0) { ?>
                            <span style="background-color: #4CAF50; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                                <i class="fa fa-check-circle"></i> มีสินค้าพร้อมรับ (เหลือ <?php echo $product['stock_qty']; ?> <?php echo $product['unit_name']; ?>)
                            </span>
                        <?php } else { ?>
                            <span style="background-color: #D10024; color: white; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                                <i class="fa fa-times-circle"></i> สินค้าหมดชั่วคราว
                            </span>
                        <?php } ?>
                    </div>

                    <table class="table table-bordered mt-4" style="margin-top: 30px; background-color: #fcfcfc;">
                        <tr>
                            <th width="30%">รหัสสินค้า:</th>
                            <td><b><?php echo $product['product_code']; ?></b></td>
                        </tr>
                        <tr>
                            <th>หมวดหมู่วัสดุ:</th>
                            <td><?php echo $product['category_name']; ?></td>
                        </tr>
                        <tr>
                            <th>ยี่ห้อ / แบรนด์:</th>
                            <td><?php echo $product['brand_name']; ?></td>
                        </tr>
                    </table>

                    <div class="add-to-cart" style="margin-top: 40px;">
                        <button class="add-to-cart-btn" onclick="alert('กรุณาติดต่อหน้าร้าน\nโทรศัพท์: +66 012-345-6789\nLine ID: @constructshop\nเพื่อสั่งซื้อและจัดเตรียมสินค้าครับ')" style="width: 100%; font-size: 18px; padding: 15px; border-radius: 5px;">
                            <i class="fa fa-phone"></i> โทรติดต่อเพื่อสั่งซื้อ / ขอใบเสนอราคา
                        </button>
                    </div>

                </div>
            </div>
            </div>
    </div>
</div>

<?php include "footer.php"; ?>