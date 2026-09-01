<?php
session_start();
include "../config.php";

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password']; // รับรหัสผ่าน (แนะนำให้ใช้ password_hash ในอนาคต)

$sql = "SELECT * FROM admins WHERE username = '$username' AND password = '$password'";
$query = mysqli_query($conn, $sql);
$result = mysqli_fetch_array($query);

if ($result) {
    // เก็บข้อมูลเข้า Session
    $_SESSION['admin_id'] = $result['admin_id'];
    $_SESSION['admin_name'] = $result['fullname'];
    $_SESSION['role'] = $result['role']; // ค่าจะเป็น 'bilateral_officer' หรือ 'teacher'

    // แยกเส้นทางตาม Role
    if ($result['role'] == 'bilateral_officer') {
        header("location: bilateral_dashboard.php");
    } else if ($result['role'] == 'teacher') {
        header("location: teacher_dashboard.php");
    }
    exit();
} else {
    // กรณีข้อมูลผิด
    echo "<script>
        alert('ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง');
        window.location.href = 'admin_login.php';
    </script>";
}

mysqli_close($conn);
?>