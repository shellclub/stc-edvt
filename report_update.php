<?php
date_default_timezone_set('Asia/Bangkok'); 
session_start();
include "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($conn, $_POST['report_id']);
    $sid = $_SESSION['student_id'];
    $job = mysqli_real_escape_string($conn, $_POST['job_details']);
    $prob = mysqli_real_escape_string($conn, $_POST['problems']);

    // 1. ตรวจสอบรูปภาพใหม่
    $img_sql_part = "";
    if (isset($_FILES['report_image']) && $_FILES['report_image']['error'] == 0) {
        
        // --- ส่วนที่เพิ่ม: ลบไฟล์รูปเก่า ---
        $check_sql = "SELECT report_image FROM internship_reports WHERE report_id = '$id' AND student_id = '$sid'";
        $res = mysqli_query($conn, $check_sql);
        $old_data = mysqli_fetch_array($res);
        
        if ($old_data['report_image'] != "") {
            $old_file = "uploads/" . $old_data['report_image'];
            if (file_exists($old_file)) {
                @unlink($old_file); // ลบไฟล์เก่าทิ้ง
            }
        }
        // -----------------------------

        // 2. จัดการไฟล์รูปใหม่
        $filename = "IMG_" . time() . "_" . $sid . ".jpg";
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        
        // (แนะนำให้ใช้ระบบย่อรูปฝั่ง JavaScript เหมือนหน้า report_form ถ้าต้องการคุมขนาดไฟล์)
        move_uploaded_file($_FILES['report_image']['tmp_name'], "uploads/" . $filename);
        $img_sql_part = ", report_image = '$filename'";
    }

    // 3. อัปเดตฐานข้อมูล
    $sql = "UPDATE internship_reports SET 
            job_details = '$job', 
            problems = '$prob' 
            $img_sql_part 
            WHERE report_id = '$id' AND student_id = '$sid'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('แก้ไขข้อมูลเรียบร้อยแล้ว'); window.location.href='report_history.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>