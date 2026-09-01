<?php
session_start();
include "config.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sid = $_SESSION['student_id'];

    // 1. ดึงชื่อไฟล์รูปมาลบทิ้งก่อน เพื่อประหยัดพื้นที่ Server
    // แก้ไขชื่อตัวแปรจาก $sql_sql_img เป็น $sql_img ให้ถูกต้อง
    $sql_img = "SELECT report_image FROM internship_reports WHERE report_id = '$id' AND student_id = '$sid'";
    $res_img = mysqli_query($conn, $sql_img);
    $row_img = mysqli_fetch_array($res_img);

    if ($row_img && $row_img['report_image']) {
        $file_path = "uploads/" . $row_img['report_image'];
        if (file_exists($file_path)) {
            @unlink($file_path); // ลบไฟล์รูปจริงออกจากโฟลเดอร์
        }
    }

    // 2. ลบข้อมูลออกจากฐานข้อมูล
    $sql_del = "DELETE FROM internship_reports WHERE report_id = '$id' AND student_id = '$sid'";
    
    if (mysqli_query($conn, $sql_del)) {
        echo "<script>alert('ลบข้อมูลและรูปภาพสำเร็จ'); window.location.href='report_history.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    header("location: report_history.php");
}

mysqli_close($conn);
?>