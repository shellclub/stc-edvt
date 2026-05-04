<?php
include "../config.php";

if ($_FILES['student_file']['name']) {
    $filename = $_FILES['student_file']['tmp_name'];
    $file = fopen($filename, "r");

    $count = 0;
    while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
        $count++;
        if ($count == 1) continue; // ข้ามหัวตาราง

        $student_id = $data[0];
        $fullname = $data[1];
        $birth_date = $data[2];

        // บันทึกลงฐานข้อมูล (is_first_login = 1 ตามเงื่อนไขของคุณ)
        $sql = "INSERT INTO students (student_id, fullname, birth_date, is_first_login) 
                VALUES ('$student_id', '$fullname', '$birth_date', 1)
                ON DUPLICATE KEY UPDATE fullname='$fullname', birth_date='$birth_date'";
        mysqli_query($conn, $sql);
    }
    fclose($file);
    echo "<script>alert('นำเข้าข้อมูลสำเร็จ $count รายการ'); window.location.href='bilateral_dashboard.php';</script>";
}
?>