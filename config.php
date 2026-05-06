<?php
// ตั้งค่าตัวแปรเชื่อมต่อ (รองรับ Docker + Local)
$hostname = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASSWORD') ?: "";
$dbname   = getenv('DB_NAME') ?: "edvtstc"; // ชื่อฐานข้อมูลของคุณ

// สร้างการเชื่อมต่อ
$conn = mysqli_connect($hostname, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}

// ตั้งค่าให้รองรับภาษาไทย
mysqli_set_charset($conn, "utf8");

// (Optional) เปิดเพื่อเช็คว่าต่อติดไหม ถ้าติดแล้วให้ปิดไว้ครับ
// echo "เชื่อมต่อสำเร็จ!";

if (!function_exists('dateThai')) {
    function dateThai($strDate) {
        if($strDate == "" || $strDate == "0000-00-00") return "-";
        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("n", strtotime($strDate));
        $strDay = date("j", strtotime($strDate));
        $strMonthCut = Array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
        $strMonthThai = $strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear";
    }
}

if (!function_exists('dateTimeThai')) {
    function dateTimeThai($strDate) {
        if($strDate == "") return "-";
        $strYear = date("Y", strtotime($strDate)) + 543;
        $strMonth = date("n", strtotime($strDate));
        $strDay = date("j", strtotime($strDate));
        $strHour= date("H", strtotime($strDate));
        $strMinute= date("i", strtotime($strDate));
        $strMonthCut = Array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
        $strMonthThai = $strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear ($strHour:$strMinute น.)";
    }
}
?>
