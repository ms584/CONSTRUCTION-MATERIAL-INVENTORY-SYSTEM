<?php
include "header.php";
?>

<?php
// ===== Sort & Filter Logic =====
$cat_id = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, trim($_GET['search'])) : '';

// Whitelist sort options
$sort_options = [
    'newest'    => 'p.product_id DESC',
    'price_asc' => 'p.selling_price ASC',
    'price_desc'=> 'p.selling_price DESC',
    'name_asc'  => 'p.product_name ASC',
    'name_desc' => 'p.product_name DESC',
];
$sort = (isset($_GET['sort']) && array_key_exists($_GET['sort'], $sort_options)) ? $_GET['sort'] : 'newest';
$order_sql = $sort_options[$sort];

$sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE 1=1";
if($cat_id > 0) $sql .= " AND p.category_id = '$cat_id'";
if($search != '') $sql .= " AND (p.product_name LIKE '%$search%' OR p.product_code LIKE '%$search%')";
$sql .= " ORDER BY $order_sql";

$run_query = mysqli_query($con, $sql);
$current_params = http_build_query(['cat_id' => $cat_id ?: '', 'sort' => $sort, 'search' => $search]);
?>

<div class="section">
    <div class="container">
        <div class="row">
            <!-- Sidebar หมวดหมู่ -->
            <div id="aside" class="col-md-3">
                <div class="aside">
                    <h3 class="aside-title">หมวดหมู่วัสดุก่อสร้าง</h3>
                    <div class="list-group">
                        <a href="store.php" class="list-group-item <?php echo ($cat_id == 0 && $search == '') ? 'active' : ''; ?>">ดูสินค้าทั้งหมด</a>
                        <?php
                        $cat_q = mysqli_query($con, "SELECT * FROM categories");
                        while($c = mysqli_fetch_array($cat_q)){
                            $active = ($cat_id == $c['category_id']) ? 'active' : '';
                            echo "<a href='store.php?cat_id=".$c['category_id']."&sort=$sort' class='list-group-item $active'>".$c['category_name']."</a>";
                        }
                        ?>
                    </div>
                </div>
            </div>

            <!-- สินค้า -->
            <div id="store" class="col-md-9">

                <!-- แถบค้นหา + sort -->
                <div class="row" style="margin-bottom:16px; align-items:center;">
                    <div class="col-md-6">
                        <form method="get" action="store.php" style="display:flex; gap:8px;">
                            <input type="hidden" name="cat_id" value="<?php echo $cat_id; ?>">
                            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="🔍 ค้นหาสินค้า..." class="form-control"
                                   style="border-radius:20px; padding:6px 14px;">
                            <button type="submit" class="btn btn-sm" style="background:#D10024; color:#fff; border-radius:20px; white-space:nowrap;">ค้นหา</button>
                        </form>
                    </div>
                    <div class="col-md-6 text-right">
                        <label style="margin-right:8px; font-weight:600; color:#333;">เรียงตาม:</label>
                        <select onchange="window.location.href=this.value" style="padding:6px 12px; border-radius:20px; border:1px solid #ccc; cursor:pointer;">
                            <?php
                            $sort_labels = [
                                'newest'     => '🕒 ใหม่ล่าสุด',
                                'price_asc'  => '💰 ราคา: น้อย → มาก',
                                'price_desc' => '💰 ราคา: มาก → น้อย',
                                'name_asc'   => '🔤 ชื่อ ก → ฮ',
                                'name_desc'  => '🔤 ชื่อ ฮ → ก',
                            ];
                            foreach($sort_labels as $val => $label) {
                                $selected = ($sort == $val) ? 'selected' : '';
                                $url = "store.php?" . http_build_query(['cat_id' => $cat_id ?: '', 'sort' => $val, 'search' => $search]);
                                echo "<option value='$url' $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- จำนวนสินค้าที่พบ -->
                <p style="color:#888; font-size:13px; margin-bottom:12px;">
                    พบสินค้า <b><?php echo mysqli_num_rows($run_query); ?></b> รายการ
                    <?php if($search != '') echo "สำหรับ \"<b>".htmlspecialchars($search)."</b>\""; ?>
                </p>

                <div class="row">
                    <?php
                    if(mysqli_num_rows($run_query) > 0){
                        while($row = mysqli_fetch_array($run_query)){
                            $stock_label = ($row['stock_qty'] > 0)
                                ? "<span class='new'>มีสินค้า</span>"
                                : "<span class='new' style='background-color:red;'>สินค้าหมดชั่วคราว</span>";
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