<?php
session_start();
include "../config.php";

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password'];

// 1. ดึงข้อมูลจากฐานข้อมูลด้วย username
$sql = "SELECT * FROM admins WHERE username = '$username'";
$query = mysqli_query($conn, $sql);
$admin = mysqli_fetch_assoc($query);

$login_success = false;
$need_force_change = false;

if ($admin) {
    // 2. ตรวจสอบว่าในฐานข้อมูลเป็น hash แล้วหรือยัง
    if (password_verify($password, $admin['password'])) {
        // กรณีรหัสผ่านเป็น Hash ที่ถูกต้อง
        $login_success = true;
    } else if ($password === $admin['password']) {
        // กรณีรหัสผ่านยังเป็น Plain Text เดิม (ยังไม่ hash)
        $login_success = true;
        $need_force_change = true; // บังคับเปลี่ยนรหัสผ่าน
    }
}

if ($login_success) {
    // เก็บข้อมูลพื้นฐานเข้า Session
    $_SESSION['admin_id'] = $admin['admin_id'];
    $_SESSION['admin_name'] = $admin['fullname'];
    $_SESSION['role'] = $admin['role'];

    // 3. ถ้ายังไม่ได้เป็น Hash ให้ส่งไปหน้าบังคับเปลี่ยนรหัสผ่านทันที
    if ($need_force_change) {
        $_SESSION['force_change_pwd'] = true;
        header("location: force_change_password.php");
        exit();
    }

    // กรณีรหัสผ่านปลอดภัยแล้ว แยกเส้นทางตาม Role
    unset($_SESSION['force_change_pwd']);
    if ($admin['role'] === 'bilateral_officer') {
        header("location: bilateral_dashboard.php");
    } else if ($admin['role'] === 'teacher') {
        header("location: teacher_dashboard.php");
    }
    exit();
} else {
    // กรณีข้อมูลไม่ถูกต้อง
    echo "<script>
        alert('ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
        window.location.href = 'index.php';
    </script>";
    exit();
}

mysqli_close($conn);
?>