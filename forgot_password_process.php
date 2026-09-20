<?php
session_start();
include "config.php"; // ตรวจสอบ Path ให้ถูกต้อง

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sid = mysqli_real_escape_string($conn, trim($_POST['student_id']));
    $dob = mysqli_real_escape_string($conn, trim($_POST['birth_date'])); 
    
    // ถ้าในหน้า forgot_password.php ใช้ <input type="date"> 
    // ค่าที่ส่งมาจะเป็น YYYY-MM-DD แต่ใน DB ของคุณอาจจะเป็น วว/ดด/ปปปป
    // ดังนั้นเราต้องแปลงค่าที่รับมาให้ตรงกับใน DB ก่อนค้นหา
    
    $date_parts = explode('-', $dob);
    if(count($date_parts) == 3) {
        // แปลงจาก 2009-04-20 เป็น 20/04/2552 (ตามรูปแบบที่คุณนำเข้าล่าสุด)
        $y_th = (int)$date_parts[0] + 543;
        $m_th = $date_parts[1];
        $d_th = $date_parts[2];
        $search_date = "$d_th/$m_th/$y_th";
    } else {
        $search_date = $dob;
    }

    // ตรวจสอบ SQL
    $sql = "SELECT * FROM students WHERE student_id = '$sid' AND birth_date = '$search_date'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        // กรณีพบข้อมูล
        $_SESSION['student_id'] = $sid;
        header("Location: change_password.php");
        exit(); // สำคัญมาก: ต้องมี exit เพื่อหยุดการทำงานของสคริปต์
    } else {
        // กรณีไม่พบข้อมูล หรือ SQL ผิดพลาด
        echo "<script>
                alert('ไม่พบข้อมูลนักศึกษา หรือวันเกิดไม่ถูกต้อง\\n(ข้อมูลในระบบ: $search_date)'); 
                window.history.back();
              </script>";
        exit();
    }
} else {
    // ถ้าไม่ได้มาด้วยการ POST
    header("Location: login.php");
    exit();
}
?>