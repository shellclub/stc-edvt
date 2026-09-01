<?php
session_start();
include "config.php";

$student_id = $_SESSION['student_id'];
$new_pass = $_POST['new_pass'];
$confirm_pass = $_POST['confirm_pass'];

// 1. เช็คว่ารหัสผ่านตรงกันไหม
if ($new_pass !== $confirm_pass) {
    echo "<script>alert('รหัสผ่านไม่ตรงกัน กรุณาลองใหม่'); window.history.back();</script>";
    exit();
}

// 2. เข้ารหัสผ่านเพื่อความปลอดภัย (Hashing)
$hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);

// 3. อัปเดตข้อมูลและเปลี่ยนสถานะ is_first_login เป็น 0
$sql = "UPDATE students SET 
        password = '$hashed_password', 
        is_first_login = 0 
        WHERE student_id = '$student_id'";

if (mysqli_query($conn, $sql)) {
    echo "<script>
            alert('เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบอีกครั้ง');
            window.location.href = 'index.php';
          </script>";
    session_destroy(); // ล้าง Session เพื่อให้ Login ใหม่ด้วยรหัสใหม่
} else {
    echo "เกิดข้อผิดพลาด: " . mysqli_error($conn);
}

mysqli_close($conn);
?>