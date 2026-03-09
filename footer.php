<footer id="footer" style="background-color: #15161D;">
            <div class="section">
                <div class="container">
                    <div class="row">
                        <div class="col-md-4 col-xs-6">
                            <div class="footer">
                                <h3 class="footer-title">เกี่ยวกับเรา (About Us)</h3>
                                <p style="color: #B9BABC;">CONSTRUCTSHOP ร้านจำหน่ายวัสดุก่อสร้าง ฮาร์ดแวร์ เครื่องมือช่าง และอุปกรณ์ตกแต่งบ้านครบวงจร สินค้าคุณภาพ ราคามาตรฐาน พร้อมบริการจัดส่งถึงหน้าไซต์งาน</p>
                                <ul class="footer-links">
                                    <li><a href="#"><i class="fa fa-phone" style="color: #D10024;"></i> +66 012-345-6789</a></li>
                                    <li><a href="#"><i class="fa fa-envelope-o" style="color: #D10024;"></i> info@constructshop.com</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="col-md-4 col-xs-6">
                            <div class="footer">
                                <h3 class="footer-title">หมวดหมู่สินค้า (Categories)</h3>
                                <ul class="footer-links">
                                    <?php 
                                    // เช็คว่ามีการเชื่อมต่อ DB หรือยัง (ปกติถูก include มาจาก header.php)
                                    if(isset($con)) {
                                        $cat_query = mysqli_query($con, "SELECT * FROM categories LIMIT 5");
                                        if($cat_query){
                                            while($row = mysqli_fetch_array($cat_query)){
                                                echo "<li><a href='store.php?cat_id=".$row['category_id']."'>".$row['category_name']."</a></li>";
                                            }
                                        }
                                    }
                                    ?>
                                    <li><a href="store.php" style="color: #D10024;">ดูแคตตาล็อกทั้งหมด &rarr;</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="clearfix visible-xs"></div>

                        <div class="col-md-4 col-xs-6">
                            <div class="footer">
                                <h3 class="footer-title">บริการลูกค้า (Services)</h3>
                                <ul class="footer-links">
                                    <li><a href="check_bill.php">ตรวจสอบบิล / ใบเสร็จ</a></li>
                                    <li><a href="#" onclick="alert('เงื่อนไขการจัดส่ง: ส่งฟรีเมื่อสั่งซื้อครบ 10,000 บาทขึ้นไป ในระยะทาง 20 กิโลเมตร')">เงื่อนไขการจัดส่งสินค้า</a></li>
                                    <li><a href="#">ขอใบเสนอราคา (Quotation)</a></li>
                                    <li><a href="admin/login.php"><i class="fa fa-lock"></i> ระบบหลังบ้าน (Backend)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            <div id="bottom-footer" class="section">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <span class="copyright">
                                Copyright &copy;<script>document.write(new Date().getFullYear());</script> All rights reserved | CONSTRUCTSHOP ระบบคลังสินค้าวัสดุก่อสร้าง
                            </span>
                        </div>
                    </div>
                    </div>
                </div>
            </footer>
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/slick.min.js"></script>
        <script src="js/nouislider.min.js"></script>
        <script src="js/jquery.zoom.min.js"></script>
        
        </body>
</html>