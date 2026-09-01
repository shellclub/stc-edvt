<?php
session_start();
include "config.php";

$user = $_POST['username'];
$pass = $_POST['password'];

// ป้องกัน SQL Injection เบื้องต้น
$user = mysqli_real_escape_string($conn, $user);

$sql = "SELECT * FROM students WHERE student_id = '$user'";
$query = mysqli_query($conn, $sql);
$result = mysqli_fetch_array($query);

if ($result) {
    if ($result['is_first_login'] == 1) {
        // กรณีเข้าสู่ระบบครั้งแรก: เช็คกับวันเกิด
        if ($pass == $result['birth_date']) {
            $_SESSION['student_id'] = $result['student_id'];
            header("location: change_password.php");
            exit();
        } else {
            // รหัสผ่านวันเกิดไม่ถูก
            echo "<script>
                alert('รหัสผ่าน (วันเกิด) ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
                window.location.href = 'index.php';
            </script>";
        }
    } else {
        // กรณีเคยเปลี่ยนรหัสผ่านแล้ว: ใช้ password_verify
        if (password_verify($pass, $result['password'])) {
            $_SESSION['student_id'] = $result['student_id'];
            header("location: dashboard.php");
            exit();
        } else {
            // รหัสผ่านที่เปลี่ยนแล้วไม่ถูก
            echo "<script>
                alert('รหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
                window.location.href = 'index.php';
            </script>";
        }
    }
} else {
    // ไม่พบรหัสนักศึกษาในระบบ
    echo "<script>
        alert('ไม่พบข้อมูลนักศึกษานี้ในระบบ');
        window.location.href = 'index.php';
    </script>";
}

mysqli_close($conn);
?>