<?php
session_start();
include "config.php";

// รับค่าจาก Fetch API
$input = json_decode(file_get_contents('php://input'), true);

if (isset($_SESSION['student_id']) && $input) {
    $sid = $_SESSION['student_id'];
    $dir = 'logs';

    // สร้างโฟลเดอร์ logs ถ้ายังไม่มี
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $filename = $dir . "/checkin_" . $sid . ".json";
    $today = date('Y-m-d');
    
    $log_data = [];
    if (file_exists($filename)) {
        $log_data = json_decode(file_get_contents($filename), true);
    }

    $new_entry = [
        'time' => date('H:i:s'),
        'lat' => $input['lat'],
        'lng' => $input['lng']
    ];

    if ($input['type'] == 'in') {
        $log_data[$today]['check_in'] = $new_entry;
        $msg = "ลงเวลาเข้างานสำเร็จ";
    } else {
        $log_data[$today]['check_out'] = $new_entry;
        $msg = "ลงเวลาเลิกงานสำเร็จ";
    }

    // บันทึกไฟล์
    if (file_put_contents($filename, json_encode($log_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
        echo json_encode(['status' => 'success', 'message' => $msg]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเขียนไฟล์ได้ ตรวจสอบสิทธิ์ Folder']);
    }
}
?>