<?php
date_default_timezone_set('Asia/Bangkok'); 
session_start();
include "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sid = $_SESSION['student_id'];
    $date = $_POST['report_date'];
    $job = mysqli_real_escape_string($conn, $_POST['job_details']);
    $prob = mysqli_real_escape_string($conn, $_POST['problems']);
    $lat = mysqli_real_escape_string($conn, $_POST['lat']);
    $lng = mysqli_real_escape_string($conn, $_POST['lng']);

    // 1. ตรวจสอบรายงานย้อนหลังไม่เกิน 30 วัน
    $today = new DateTime();
    $target_date = new DateTime($date);
    $diff = $today->diff($target_date)->days;
    
    if ($target_date > $today || $diff > 30) {
        echo "<script>alert('วันที่รายงานไม่ถูกต้อง (ห้ามเกิน 30 วัน)'); window.history.back();</script>";
        exit();
    }

    // 2. ตรวจสอบรายงานซ้ำวันเดิม
    $check = mysqli_query($conn, "SELECT report_id FROM internship_reports WHERE student_id = '$sid' AND report_date = '$date'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('คุณเคยรายงานวันที่ $date ไปแล้ว'); window.history.back();</script>";
        exit();
    }

    // 3. จัดการไฟล์รูป (ถ้ามี)
    $filename = "";
    if (isset($_FILES['report_image']) && $_FILES['report_image']['error'] == 0) {
        $filename = "IMG_" . time() . "_" . $sid . ".jpg";
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        move_uploaded_file($_FILES['report_image']['tmp_name'], "uploads/" . $filename);
    }

    // 4. บันทึกข้อมูล
    $sql = "INSERT INTO internship_reports (student_id, report_date, job_details, problems, report_image, location_lat, location_lng) 
            VALUES ('$sid', '$date', '$job', '$prob', '$filename', '$lat', '$lng')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('บันทึกสำเร็จ!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
mysqli_close($conn);
?>