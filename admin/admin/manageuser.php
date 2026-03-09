
    <?php
session_start();
include("../../db.php");

// ระบบลบพนักงาน
if($_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'Manager') {
    echo "<script>alert('ขออภัย! คุณไม่มีสิทธิ์เข้าถึงหน้านี้ เฉพาะผู้ดูแลระบบเท่านั้น'); window.location.href='index.php';</script>";
    exit(); // สั่งให้หยุดการทำงานของหน้านี้ทันที
}
if(isset($_GET['delete_id'])){
    $del_id = $_GET['delete_id'];
    
    // ป้องกันการกดลบ Admin หลัก (ID = 1) เพื่อไม่ให้ระบบล็อคเข้าไม่ได้
    if($del_id == 1) {
        echo "<script>alert('ไม่อนุญาตให้ลบ Admin หลักของระบบได้!'); window.location.href='manageuser.php';</script>";
    } else {
        $delete_query = "DELETE FROM employees WHERE employee_id = '$del_id'";
        if(mysqli_query($con, $delete_query)){
            echo "<script>alert('ลบข้อมูลพนักงานออกจากระบบเรียบร้อยแล้ว!'); window.location.href='manageuser.php';</script>";
        } else {
            // กรณีลบไม่ได้เพราะพนักงานคนนี้เคยเปิดบิลไปแล้ว (ติด Foreign Key)
            echo "<script>alert('ไม่สามารถลบได้! เนื่องจากพนักงานคนนี้มีประวัติการทำรายการรับเข้า/เบิกออกในระบบแล้ว'); window.location.href='manageuser.php';</script>";
        }
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
          <div class="card-header card-header-primary" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 class="card-title">จัดการผู้ใช้งาน (Manage Users)</h4>
                <p class="card-category">รายชื่อพนักงานและสิทธิ์การเข้าถึงระบบ</p>
            </div>
            <a href="addemployees.php" class="btn btn-info"><i class="material-icons">person_add</i> เพิ่มพนักงานใหม่</a>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead class="text-primary">
                  <th>รหัสพนักงาน</th>
                  <th>ชื่อเข้าใช้งาน (Username)</th>
                  <th>ชื่อ-นามสกุล</th>
                  <th>ตำแหน่ง / สิทธิ์ (Role)</th>
                  <th class="text-center">จัดการ</th>
                </thead>
                <tbody>
                  <?php 
                    // ดึงข้อมูลพนักงานจากตาราง employees
                    $sql = "SELECT * FROM employees ORDER BY employee_id ASC";
                    $result = mysqli_query($con, $sql);
                    
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_array($result)) {
                            
                            echo "<tr>";
                            echo "<td><b>EMP-" . sprintf('%03d', $row['employee_id']) . "</b></td>";
                            echo "<td>" . $row['username'] . "</td>";
                            echo "<td>" . $row['full_name'] . "</td>";
                            echo "<td>" . $row['role'] . "</td>";
                            
                            // ปุ่มจัดการ
                            echo "<td class='text-center' style='white-space:nowrap;'>";
                            // ปุ่มเปลี่ยนรหัสผ่าน (ทุกคน)
                            echo "<a href='reset_password.php?id=".$row['employee_id']."' class='btn btn-info btn-sm' style='margin-right:5px;'><i class='material-icons' style='font-size:16px;vertical-align:middle;'>lock_reset</i> รีเซ็ตรหัส</a>";
                            if($row['employee_id'] != 1) {
                                echo "<a href='manageuser.php?delete_id=".$row['employee_id']."' class='btn btn-danger btn-sm' onclick='return confirm(\"คุณแน่ใจหรือไม่ว่าต้องการระงับสิทธิ์/ลบพนักงานคนนี้?\")'><i class='material-icons' style='font-size:16px;vertical-align:middle;'>delete</i> ลบ</a>";
                            } else {
                                echo "<button class='btn btn-default btn-sm' disabled>Admin หลัก</button>";
                            }
                            echo "</td>";

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>ยังไม่มีข้อมูลพนักงาน</td></tr>";
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