<?php
session_start();
include("../../db.php");

// ระบบลบข้อมูลลูกค้า
if(isset($_GET['delete_id'])){
    $del_id = $_GET['delete_id'];
    
    $delete_query = "DELETE FROM customers WHERE customer_id = '$del_id'";
    if(mysqli_query($con, $delete_query)){
        echo "<script>alert('ลบข้อมูลลูกค้าเรียบร้อยแล้ว!'); window.location.href='customers_list.php';</script>";
    } else {
        echo "<script>alert('ไม่สามารถลบได้! เนื่องจากลูกค้ารายนี้มีประวัติการซื้อ/เปิดบิลในระบบแล้ว'); window.location.href='customers_list.php';</script>";
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
                <h4 class="card-title">ฐานข้อมูลลูกค้า / ผู้รับเหมา (Customers)</h4>
                <p class="card-category">รายชื่อสำหรับออกบิลและจัดส่งสินค้า</p>
            </div>
            <a href="add_customer.php" class="btn btn-primary">เพิ่มลูกค้าใหม่</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="text-info">
                  <th>รหัสลูกค้า</th>
                  <th>ชื่อ-นามสกุล / บริษัท</th>
                  <th>ประเภท</th>
                  <th>เบอร์โทรศัพท์</th>
                  <th>ที่อยู่</th>
                  <th>จัดการ</th>
                </thead>
                <tbody>
                  <?php 
                    $sql = "SELECT * FROM customers ORDER BY customer_id DESC";
                    $result = mysqli_query($con, $sql);
                    
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result)) {
                            // ทำป้ายสีแบ่งตามประเภทลูกค้า
                            $badge_color = "badge-secondary"; 
                            if($row['customer_type'] == 'ผู้รับเหมา') $badge_color = "badge-warning"; // สีส้ม
                            if($row['customer_type'] == 'ช่างประจำ') $badge_color = "badge-success"; // สีเขียว
                            if($row['customer_type'] == 'บริษัท/นิติบุคคล') $badge_color = "badge-info"; // สีฟ้า

                            echo "<tr>";
                            echo "<td>CUS-".sprintf('%04d', $row['customer_id'])."</td>";
                            echo "<td><b>".$row['customer_name']."</b></td>";
                            echo "<td><span class='badge $badge_color' style='font-size: 13px; padding: 5px 10px;'>".$row['customer_type']."</span></td>";
                            echo "<td>".($row['phone'] ? $row['phone'] : '-')."</td>";
                            echo "<td>".($row['address'] ? $row['address'] : '-')."</td>";
                            echo "<td>
                                    <a href='customers_list.php?delete_id=".$row['customer_id']."' class='btn btn-danger btn-sm' onclick='return confirm(\"คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลลูกค้ารายนี้?\")'>
                                        <i class='material-icons'>delete</i> ลบ
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>ยังไม่มีข้อมูลลูกค้าในระบบ</td></tr>";
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