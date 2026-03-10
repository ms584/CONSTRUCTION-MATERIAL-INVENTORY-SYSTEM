<?php
session_start();
include("../../db.php");

$current_username = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'admin';

$query = mysqli_query($con, "SELECT * FROM employees WHERE username = '$current_username' LIMIT 1");
if(mysqli_num_rows($query) == 0) {
    $query = mysqli_query($con, "SELECT * FROM employees WHERE employee_id = 1");
}
$user   = mysqli_fetch_array($query);
$emp_id = $user['employee_id'];

// ===== UPDATE PROFILE =====
if(isset($_POST['btn_update_profile'])) {
    $full_name = mysqli_real_escape_string($con, $_POST['full_name']);
    $username  = mysqli_real_escape_string($con, $_POST['username']);
    $new_pass  = $_POST['password'];

    $check_dup = mysqli_query($con, "SELECT * FROM employees WHERE username='$username' AND employee_id != '$emp_id'");
    if(mysqli_num_rows($check_dup) > 0) {
        echo "<script>alert('Username นี้มีคนอื่นใช้แล้ว!'); window.history.back();</script>";
        exit();
    }

    // จัดการรูปโปรไฟล์
    $avatar_sql = "";
    if(!empty($_FILES['avatar']['name'])) {
        $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
        if(!in_array($_FILES['avatar']['type'], $allowed)) {
            echo "<script>alert('ไฟล์รูปต้องเป็น JPG, PNG, WEBP หรือ GIF เท่านั้น'); window.history.back();</script>";
            exit();
        }
        if($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
            echo "<script>alert('ไฟล์รูปต้องไม่เกิน 5MB'); window.history.back();</script>";
            exit();
        }
        $ext      = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_name = 'avatar_' . $emp_id . '_' . time() . '.' . $ext;
        $dest     = "../../profile_images/" . $new_name;

        // ลบรูปเก่า (ถ้าไม่ใช่ default)
        if(!empty($user['avatar']) && $user['avatar'] != 'default_avatar.png' && file_exists("../../profile_images/" . $user['avatar'])) {
            unlink("../../profile_images/" . $user['avatar']);
        }

        if(move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
            $avatar_sql = ", avatar = '$new_name'";
        }
    }

    $sql_update = "UPDATE employees SET full_name='$full_name', username='$username'";
    if(!empty($new_pass)) $sql_update .= ", password='$new_pass'";
    $sql_update .= $avatar_sql . " WHERE employee_id='$emp_id'";

    if(mysqli_query($con, $sql_update)) {
        $_SESSION['admin_name'] = $username;
        echo "<script>alert('อัปเดตโปรไฟล์สำเร็จ!'); window.location.href='profile.php';</script>";
    } else {
        echo "<script>alert('เกิดข้อผิดพลาด: " . addslashes(mysqli_error($con)) . "');</script>";
    }
}

// โฟลเดอร์เก็บรูป
if(!is_dir("../../profile_images")) mkdir("../../profile_images", 0755, true);

// โหลดข้อมูลล่าสุด
$query = mysqli_query($con, "SELECT * FROM employees WHERE employee_id='$emp_id'");
$user  = mysqli_fetch_array($query);

$avatar_file = (!empty($user['avatar'])) ? $user['avatar'] : 'default_avatar.png';
$avatar_url  = "../../profile_images/" . $avatar_file;
if(!file_exists($avatar_url)) {
    $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($user['full_name']) . "&background=7B1FA2&color=fff&size=128";
} else {
    $avatar_url = "../../profile_images/" . $avatar_file;
}

include "sidenav.php";
include "topheader.php";
?>

<style>
.avatar-wrap { text-align: center; margin-bottom: 20px; }
.avatar-circle {
    width: 120px; height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #9c27b0;
    box-shadow: 0 4px 20px rgba(156,39,176,0.4);
    display: block; margin: 0 auto 12px;
}
.avatar-btn {
    background: linear-gradient(135deg, #9c27b0, #ce93d8);
    border: none; color: #fff;
    padding: 7px 18px; border-radius: 20px;
    font-size: 13px; cursor: pointer;
    transition: opacity .2s;
}
.avatar-btn:hover { opacity: 0.85; }
</style>

<div class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8 offset-md-2">
        <div class="card">
          <div class="card-header card-header-primary">
            <h4 class="card-title">ตั้งค่าบัญชีผู้ใช้ (Edit Profile)</h4>
            <p class="card-category">อัปเดตข้อมูลส่วนตัวและเปลี่ยนรหัสผ่าน</p>
          </div>
          <div class="card-body">
            <form action="" method="post" enctype="multipart/form-data">

              <!-- รูปโปรไฟล์ -->
              <div class="avatar-wrap">
                <img id="avatar-preview" src="<?php echo $avatar_url; ?>" class="avatar-circle" alt="Profile">
                <input type="file" name="avatar" id="avatar-input" accept=".jpg,.jpeg,.png,.webp,.gif" style="display:none;" onchange="previewAvatar(this)">
                <br>
                <button type="button" class="avatar-btn" onclick="document.getElementById('avatar-input').click()">
                  📷 เปลี่ยนรูปโปรไฟล์
                </button>
                <div style="color:#888; font-size:11px; margin-top:6px;">JPG, PNG, WEBP ไม่เกิน 5MB</div>
              </div>

              <div class="row mt-3">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">รหัสพนักงาน (ID)</label>
                    <input type="text" class="form-control" value="EMP-<?php echo sprintf('%03d', $user['employee_id']); ?>" disabled>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">สิทธิ์การใช้งาน (Role)</label>
                    <input type="text" class="form-control" value="<?php echo $user['role']; ?>" disabled>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อเข้าใช้งาน (Username)</label>
                    <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="bmd-label-floating">ชื่อ-นามสกุล (Full Name)</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo $user['full_name']; ?>" required>
                  </div>
                </div>
              </div>

              <div class="row mt-4">
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="bmd-label-floating">รหัสผ่านใหม่ (ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน)</label>
                    <input type="password" name="password" class="form-control" placeholder="*** พิมพ์รหัสผ่านใหม่ที่นี่ ***">
                  </div>
                </div>
              </div>

              <button type="submit" name="btn_update_profile" class="btn btn-primary pull-right mt-4">อัปเดตโปรไฟล์</button>
              <div class="clearfix"></div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function previewAvatar(input) {
    if(input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include "footer.php"; ?>