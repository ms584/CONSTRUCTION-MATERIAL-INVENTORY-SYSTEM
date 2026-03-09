<?php
include "header.php";
?>

<div class="section">
    <div class="container">
        <div class="row">
            <div id="aside" class="col-md-3">
                <div class="aside">
                    <h3 class="aside-title">หมวดหมู่วัสดุก่อสร้าง</h3>
                    <div class="list-group">
                        <a href="store.php" class="list-group-item">ดูสินค้าทั้งหมด</a>
                        <?php
                        $cat_q = mysqli_query($con, "SELECT * FROM categories");
                        while($c = mysqli_fetch_array($cat_q)){
                            echo "<a href='store.php?cat_id=".$c['category_id']."' class='list-group-item'>".$c['category_name']."</a>";
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div id="store" class="col-md-9">
                <div class="row">
                    <?php
                    // ตรวจสอบว่ามีการกดเลือกหมวดหมู่มาหรือไม่
                    $cat_id = isset($_GET['cat_id']) ? $_GET['cat_id'] : '';
                    
                    $sql = "SELECT p.*, c.category_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.category_id";
                    
                    if($cat_id != '') {
                        $sql .= " WHERE p.category_id = '$cat_id'"; // กรองตามหมวดหมู่
                    }
                    $sql .= " ORDER BY p.product_id DESC";
                    
                    $run_query = mysqli_query($con, $sql);
                    
                    if(mysqli_num_rows($run_query) > 0){
                        while($row = mysqli_fetch_array($run_query)){
                            // เช็คสต๊อกเพื่อทำป้ายเตือน
                            $stock_label = ($row['stock_qty'] > 0) ? "<span class='new'>มีสินค้า</span>" : "<span class='new' style='background-color:red;'>สินค้าหมดชั่วคราว</span>";
                            ?>
                            
                            <div class="col-md-4 col-xs-6">
                                <div class="product">
                                    <div class="product-img">
                                        <img src="product_images/<?php echo $row['product_image']; ?>" alt="" style="height:200px; object-fit:cover;">
                                        <div class="product-label">
                                            <?php echo $stock_label; ?>
                                        </div>
                                    </div>
                                    <div class="product-body">
                                        <p class="product-category"><?php echo $row['category_name']; ?></p>
                                        <h3 class="product-name"><a href="product.php?id=<?php echo $row['product_id']; ?>"><?php echo $row['product_name']; ?></a></h3>
                                        <h4 class="product-price"><?php echo number_format($row['selling_price'], 2); ?> ฿</h4>
                                    </div>
                                    <div class="add-to-cart">
                                        <a href="product.php?id=<?php echo $row['product_id']; ?>" class="add-to-cart-btn"><i class="fa fa-eye"></i> ดูรายละเอียด</a>
                                    </div>
                                </div>
                            </div>

                            <?php
                        }
                    } else {
                        echo "<div class='col-md-12 text-center'><h4 style='margin-top:50px; color:#D10024;'>ไม่พบรายการสินค้าในหมวดหมู่นี้</h4></div>";
                    }
                    ?>
                </div>
            </div>
            </div>
    </div>
</div>

<?php include "footer.php"; ?>