<?php
session_start();
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['student_file'])) {
    $file = $_FILES['student_file']['tmp_name'];
    $content = file_get_contents($file);
    
    // แปลงภาษาไทยป้องกันสระเพี้ยน
    if (!mb_check_encoding($content, 'UTF-8')) {
        $content = iconv('CP874', 'UTF-8//IGNORE', $content);
    }

    $stream = fopen('php://memory', 'r+');
    fwrite($stream, $content);
    rewind($stream);

    $success = 0;
    $error = 0;
    fgetcsv($stream); // ข้ามบรรทัดหัวตาราง

    while (($data = fgetcsv($stream, 1000, ",")) !== FALSE) {
        // ตรวจสอบตัวคั่น (Comma หรือ Semicolon)
        if (count($data) < 6) {
            $data = str_getcsv($data[0], ";");
            if (count($data) < 6) continue;
        }

        // --- รับค่าจาก CSV ตรงๆ ตามลำดับ ---
        $student_id   = mysqli_real_escape_string($conn, trim($data[0]));
        $fullname     = mysqli_real_escape_string($conn, trim($data[1]));
        
        // นำเข้าวันที่แบบดั้งเดิมจากไฟล์ CSV (มาอย่างไร เข้าอย่างนั้น)
        $birth_date   = mysqli_real_escape_string($conn, trim($data[2])); 
        
        $group_code   = mysqli_real_escape_string($conn, trim($data[3]));
        $group_name   = mysqli_real_escape_string($conn, trim($data[4]));
        $advisor_name = mysqli_real_escape_string($conn, trim($data[5]));

        if (empty($student_id)) continue;

        // --- บันทึกลงฐานข้อมูล ---
        $sql = "INSERT INTO students (student_id, fullname, birth_date, group_code, group_name, advisor_name, password, is_first_login) 
                VALUES ('$student_id', '$fullname', '$birth_date', '$group_code', '$group_name', '$advisor_name', '$student_id', 1)
                ON DUPLICATE KEY UPDATE 
                fullname = '$fullname', 
                birth_date = '$birth_date', 
                group_code = '$group_code',
                group_name = '$group_name', 
                advisor_name = '$advisor_name'";

        if (mysqli_query($conn, $sql)) {
            $success++;
        } else {
            $error++;
        }
    }
    fclose($stream);
    
    echo "<script>
            alert('นำเข้าสำเร็จ $success รายการ | ผิดพลาด $error รายการ');
            window.location.href = 'import_excel.php';
          </script>";
}
?>