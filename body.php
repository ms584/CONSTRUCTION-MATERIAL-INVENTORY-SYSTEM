
   <?php
include "db.php";
?>
<div class="section">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="section-title">
                    <h3 class="title">รายการวัสดุก่อสร้างมาใหม่ (New Arrivals)</h3>
                </div>
            </div>

            <div class="col-md-12">
                <div class="row">
                    <?php
                    // ดึงข้อมูลสินค้าจากตาราง products ของเรา
                    $product_query = "SELECT p.*, c.category_name 
                                      FROM products p 
                                      LEFT JOIN categories c ON p.category_id = c.category_id 
                                      ORDER BY p.product_id DESC LIMIT 12";
                    $run_query = mysqli_query($con, $product_query);

                    if(mysqli_num_rows($run_query) > 0){
                        while($row = mysqli_fetch_array($run_query)){
                            $pro_id    = $row['product_id'];
                            $pro_cat   = $row['category_name'];
                            $pro_title = $row['product_name'];
                            $pro_price = $row['selling_price'];
                            $pro_image = $row['product_image'];
                            $pro_stock = $row['stock_qty'];

                            // เช็คสถานะสต๊อก
                            $stock_label = ($pro_stock > 0) ? "<span class='new'>มีสินค้า ($pro_stock)</span>" : "<span class='new' style='background-color:red;'>สินค้าหมด</span>";

                            echo "
                            <div class='col-md-3 col-xs-6 mb-4'>
                                <div class='product' style='margin-bottom: 30px;'>
                                    <div class='product-img'>
                                        <img src='product_images/$pro_image' alt='' style='height: 200px; width: 100%; object-fit: cover;'>
                                        <div class='product-label'>
                                            $stock_label
                                        </div>
                                    </div>
                                    <div class='product-body'>
                                        <p class='product-category'>$pro_cat</p>
                                        <h3 class='product-name'><a href='product.php?id=$pro_id'>$pro_title</a></h3>
                                        <h4 class='product-price'>".number_format($pro_price, 2)." ฿</h4>
                                    </div>
                                    <div class='add-to-cart'>
                                        <a href='product.php?id=$pro_id' class='add-to-cart-btn text-center' style='display:block;'><i class='fa fa-eye'></i> ดูรายละเอียด</a>
                                    </div>
                                </div>
                            </div>
                            ";
                        }
                    } else {
                        echo "<div class='col-md-12'><h4 class='text-center' style='margin-top:50px;'>ยังไม่มีรายการสินค้าในระบบ</h4></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>