<?php
session_start();
include "config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sid = $_SESSION['student_id'];
    $place_id = $_POST['place_id'];
    $name = mysqli_real_escape_string($conn, $_POST['company_name']);
    $mentor = mysqli_real_escape_string($conn, $_POST['mentor_name']);
    $days = mysqli_real_escape_string($conn, $_POST['training_days']);
    $phone = mysqli_real_escape_string($conn, $_POST['company_phone']);
    $addr = mysqli_real_escape_string($conn, $_POST['company_address']);
    $lat = $_POST['w_lat'];
    $lng = $_POST['w_lng'];

    if ($place_id != "") {
        // อัปเดตข้อมูลเดิม
        $sql = "UPDATE internship_places SET 
                company_name='$name', mentor_name='$mentor', training_days='$days', 
                company_phone='$phone', company_address='$addr', workplace_lat='$lat', workplace_lng='$lng' 
                WHERE place_id = '$place_id'";
        mysqli_query($conn, $sql);
    } else {
        // เพิ่มข้อมูลใหม่
        $sql = "INSERT INTO internship_places (company_name, mentor_name, training_days, company_phone, company_address, workplace_lat, workplace_lng) 
                VALUES ('$name', '$mentor', '$days', '$phone', '$addr', '$lat', '$lng')";
        mysqli_query($conn, $sql);
        $new_place_id = mysqli_insert_id($conn);
        
        // ผูก place_id กับนักศึกษา
        mysqli_query($conn, "UPDATE students SET place_id = '$new_place_id' WHERE student_id = '$sid'");
    }

    echo "<script>alert('บันทึกข้อมูลสถานที่ฝึกงานเรียบร้อย'); window.location.href='profile.php';</script>";
}
?>