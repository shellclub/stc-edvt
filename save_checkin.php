<?php
date_default_timezone_set('Asia/Bangkok'); 
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session หมดอายุ']);
    exit();
}

$sid = $_SESSION['student_id'];
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $type = $data['type']; 
    $lat = $data['lat'];
    $lng = $data['lng'];
    $today = date('Y-m-d');
    $time = date('H:i:s');

    $log_dir = "logs";
    if (!is_dir($log_dir)) { mkdir($log_dir, 0777, true); }
    
    $log_file = $log_dir . "/checkin_" . $sid . ".json";
    
    $records = [];
    if (file_exists($log_file)) {
        $records = json_decode(file_get_contents($log_file), true);
    }

    $type_key = ($type == 'in') ? 'check_in' : 'check_out';
    $type_name = ($type == 'in') ? 'เข้างาน' : 'เลิกงาน';

    // --- ส่วนที่เพิ่ม: เช็คว่าบันทึกไปแล้วหรือยัง ---
    if (isset($records[$today][$type_key])) {
        echo json_encode([
            'status' => 'warning', 
            'message' => "คุณได้บันทึกเวลา $type_name ของวันนี้ไปแล้วเมื่อเวลา " . substr($records[$today][$type_key]['time'], 0, 5) . " น."
        ]);
        exit();
    }
    // ---------------------------------------

    $records[$today][$type_key] = [
        'time' => $time,
        'lat' => $lat,
        'lng' => $lng
    ];

    if (file_put_contents($log_file, json_encode($records, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        echo json_encode(['status' => 'success', 'message' => "บันทึกเวลา $type_name เรียบร้อยที่เวลา $time น."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเขียนไฟล์ได้']);
    }
}
?>