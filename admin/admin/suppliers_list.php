<?php
session_start();
include("../../db.php");

// ระบบลบบริษัทคู่ค้า
if(isset($_GET['delete_id'])){
    $del_id = $_GET['delete_id'];
    
    $delete_query = "DELETE FROM suppliers WHERE supplier_id = '$del_id'";
    if(mysqli_query($con, $delete_query)){
        echo "<script>alert('ลบข้อมูลบริษัทคู่ค้าเรียบร้อยแล้ว!'); window.location.href='suppliers_list.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถลบได้! เนื่องจากบริษัทนี้มีประวัติการส่งสินค้าให้เราในระบบแล้ว'); window.location.href='suppliers_list.php';</script>";
    }
}

include "sidenav.php";
include "topheader.php";
?>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header card-header-info" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 class="card-title">รายชื่อบริษัทคู่ค้า / ผู้จัดจำหน่าย (Suppliers)</h4>
                <p class="card-category">ข้อมูลติดต่อโรงงานหรือร้านส่ง</p>
            </div>
            <a href="add_supplier.php" class="btn btn-primary">เพิ่มบริษัทคู่ค้า</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="text-info">
                  <th>รหัส</th>
                  <th>ชื่อบริษัท / ร้านส่ง</th>
                  <th>ชื่อผู้ติดต่อ (เซลส์)</th>
                  <th>เบอร์โทรศัพท์</th>
                  <th>ที่อยู่</th>
                  <th>จัดการ</th>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT * FROM suppliers ORDER BY supplier_id DESC";
                    $result = mysqli_query($con, $sql);
                    
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                            echo "<td>SUP-".sprintf('%03d', $row['supplier_id'])."</td>";
                            echo "<td><b>".$row['supplier_name']."</b></td>";
                            echo "<td>".$row['contact_person']."</td>";
                            echo "<td>".$row['phone']."</td>";
                            echo "<td>".$row['address']."</td>";
                            echo "<td>
                                    <a href='suppliers_list.php?delete_id=".$row['supplier_id']."' class='btn btn-danger btn-sm' onclick='return confirm(\"คุณแน่ใจหรือไม่ว่าต้องการลบบริษัทนี้?\")'>
                                        <i class='material-icons'>delete</i> ลบ
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>ยังไม่มีข้อมูลบริษัทคู่ค้าในระบบ</td></tr>";
                    }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>