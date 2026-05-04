<?php
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) {
    header("location: index.php");
    exit();
}

$sid = $_SESSION['student_id'];
$sql = "SELECT s.*, p.company_name FROM students s 
        LEFT JOIN internship_places p ON s.place_id = p.place_id 
        WHERE s.student_id = '$sid'";
$query = mysqli_query($conn, $sql);
$user = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ระบบฝึกงาน วท.สุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; padding-bottom: 90px; }
        .top-banner { background: linear-gradient(135deg, #800000 0%, #b30000 100%); color: white; padding: 40px 20px; border-radius: 0 0 30px 30px; }
        .profile-card { background: white; border-radius: 20px; margin-top: -30px; border: none; }
        .menu-icon { width: 55px; height: 55px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 10px; }
        .card-menu { border: none; border-radius: 20px; transition: 0.2s; text-decoration: none; color: #333; height: 100%; }
        .card-menu:active { transform: scale(0.95); background-color: #f1f1f1; }
        .nav-bottom { position: fixed; bottom: 0; width: 100%; background: white; display: flex; justify-content: space-around; padding: 12px 0; box-shadow: 0 -5px 15px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

    <!-- Header & Profile Summary -->
    <div class="top-banner text-center shadow">
        <h4 class="fw-bold mb-0">ระบบรายงานฝึกงานออนไลน์</h4>
        <p class="small opacity-75">วิทยาลัยเทคนิคสุพรรณบุรี</p>
    </div>

    <div class="container">
        <div class="card profile-card shadow-sm mb-4">
            <div class="card-body d-flex align-items-center p-3">
                <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $user['student_id']; ?>.jpg&x=200&f=0" 
                     class="rounded-circle border" width="65" height="65" style="object-fit: cover;"
                     onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';">
                <div class="ms-3">
                    <h6 class="mb-0 fw-bold"><?php echo $user['fullname']; ?></h6>
                    <p class="text-muted small mb-0">รหัส: <?php echo $user['student_id']; ?></p>
                    <span class="badge bg-danger mt-1" style="font-weight: normal; font-size: 0.65rem;">
                        <i class="bi bi-buildings"></i> <?php echo $user['company_name'] ?: 'ยังไม่ได้ระบุที่ฝึกงาน'; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Menu Grid -->
        <div class="row g-3 text-center">
            <!-- เมนูเช็คชื่อ (ใหม่) -->
            <div class="col-6">
                <a href="checkin.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-success bg-opacity-10 text-success mx-auto">
                        <i class="bi bi-geo-fill"></i>
                    </div>
                    <span class="fw-bold small">เช็คชื่อฝึกงาน</span>
                </a>
            </div>
            
            <!-- เมนูบันทึกงาน -->
            <div class="col-6">
                <a href="report_form.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-primary bg-opacity-10 text-primary mx-auto">
                        <i class="bi bi-journal-plus"></i>
                    </div>
                    <span class="fw-bold small">บันทึกรายงาน</span>
                </a>
            </div>

            <!-- เมนูประวัติ -->
            <div class="col-6">
                <a href="report_history.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-warning bg-opacity-10 text-warning mx-auto">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <span class="fw-bold small">ประวัติรายงาน</span>
                </a>
            </div>

            <!-- เมนูพิมพ์รายงาน (ใหม่แทนติดต่อ) -->
            <div class="col-6">
                <a href="print_report.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-info bg-opacity-10 text-info mx-auto">
                        <i class="bi bi-printer-fill"></i>
                    </div>
                    <span class="fw-bold small">พิมพ์รายงาน</span>
                </a>
            </div>
        </div>

        <!-- ข้อมูลเพิ่มเติม -->
        <div class="card mt-4 border-0 shadow-sm rounded-4">
            <div class="card-body">
                <div class="d-flex align-items-center text-danger mb-2">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <span class="fw-bold">สถานะการฝึกงาน</span>
                </div>
                <p class="small text-muted mb-0">ครูที่ปรึกษา: <?php echo $user['advisor_name']; ?></p>
                <p class="small text-muted">กลุ่ม: <?php echo $user['group_name']; ?></p>
            </div>
        </div>
    </div>

    <!-- Bottom Nav -->
    <nav class="nav-bottom">
        <a href="dashboard.php" class="text-center text-decoration-none" style="color: #800000;">
            <i class="bi bi-house-door-fill fs-4 d-block"></i>
            <small style="font-size: 0.7rem;">หน้าหลัก</small>
        </a>
        <a href="report_form.php" class="text-center text-decoration-none text-muted">
            <i class="bi bi-plus-circle fs-4 d-block"></i>
            <small style="font-size: 0.7rem;">รายงาน</small>
        </a>
        <a href="profile.php" class="text-center text-decoration-none text-muted">
            <i class="bi bi-person fs-4 d-block"></i>
            <small style="font-size: 0.7rem;">โปรไฟล์</small>
        </a>
    </nav>

</body>
</html>