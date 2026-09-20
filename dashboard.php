<?php
session_start();
include "config.php";

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['student_id'])) {
    header("location: index.php");
    exit();
}

$sid = $_SESSION['student_id'];

// ดึงข้อมูลนักศึกษาและชื่อสถานประกอบการ
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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --stc-crimson: #800000; --stc-gold: #d4af37; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; padding-bottom: 100px; }
        .kanit { font-family: 'Kanit', sans-serif; }
        
        .top-banner { 
            background: linear-gradient(135deg, var(--stc-crimson) 0%, #b30000 100%); 
            color: white; 
            padding: 50px 20px 70px; 
            border-radius: 0 0 40px 40px; 
        }
        
        .profile-card { 
            background: white; 
            border-radius: 25px; 
            margin-top: -45px; 
            border: none; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }
        
        .menu-icon { 
            width: 55px; 
            height: 55px; 
            border-radius: 18px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 24px; 
            margin-bottom: 12px; 
        }
        
        .card-menu { 
            border: none; 
            border-radius: 22px; 
            transition: 0.3s; 
            text-decoration: none; 
            color: #333; 
            height: 100%; 
            background: white;
        }
        
        .card-menu:active { transform: scale(0.92); background-color: #f8f9fa; }

        /* ปุ่มโหลดไฟล์ PDF พิเศษ */
        .download-btn-card {
            background-color: #fcfaff;
            border: 2px dashed #6f42c1 !important;
            color: #6f42c1 !important;
        }
        .text-purple { color: #6f42c1; }
        .bg-purple-light { background-color: rgba(111, 66, 193, 0.1); }

        .nav-bottom { 
            position: fixed; 
            bottom: 0; 
            width: 100%; 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px);
            display: flex; 
            justify-content: space-around; 
            padding: 15px 0; 
            box-shadow: 0 -5px 20px rgba(0,0,0,0.05); 
            z-index: 1000;
        }
    </style>
</head>
<body>

    <!-- Header Banner -->
    <div class="top-banner text-center">
        <h4 class="kanit fw-bold mb-1">ระบบรายงานฝึกงานออนไลน์</h4>
        <p class="small opacity-75">วิทยาลัยเทคนิคสุพรรณบุรี</p>
    </div>

    <div class="container px-4">
        <!-- Profile Card -->
        <div class="card profile-card mb-4">
            <div class="card-body d-flex align-items-center p-3">
                <div class="position-relative">
                    <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $user['student_id']; ?>.jpg&x=200&f=0" 
                         class="rounded-circle border border-2 border-white shadow-sm" width="75" height="75" style="object-fit: cover;"
                         onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';">
                </div>
                <div class="ms-3">
                    <h6 class="mb-0 fw-bold kanit"><?php echo $user['fullname']; ?></h6>
                    <p class="text-muted small mb-1">รหัส: <?php echo $user['student_id']; ?></p>
                    <div class="badge bg-danger-subtle text-danger px-3 rounded-pill" style="font-weight: 500; font-size: 0.7rem;">
                        <i class="bi bi-buildings me-1"></i> <?php echo $user['company_name'] ?: 'ยังไม่ได้ระบุที่ฝึกงาน'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Menu Grid -->
        <div class="row g-3 text-center">
            <!-- เมนูเช็คชื่อ -->
            <div class="col-6">
                <a href="checkin.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-success bg-opacity-10 text-success mx-auto">
                        <i class="bi bi-geo-fill"></i>
                    </div>
                    <span class="fw-bold small kanit">เช็คชื่อฝึกงาน</span>
                </a>
            </div>
            
            <!-- เมนูบันทึกงาน -->
            <div class="col-6">
                <a href="report_form.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-primary bg-opacity-10 text-primary mx-auto">
                        <i class="bi bi-journal-plus"></i>
                    </div>
                    <span class="fw-bold small kanit">บันทึกรายงาน</span>
                </a>
            </div>

            <!-- เมนูประวัติ -->
            <div class="col-6">
                <a href="report_history.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-warning bg-opacity-10 text-warning mx-auto">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <span class="fw-bold small kanit">ประวัติรายงาน</span>
                </a>
            </div>

            <!-- เมนูพิมพ์รายงาน -->
            <div class="col-6">
                <a href="print_report.php" class="card card-menu shadow-sm p-3">
                    <div class="menu-icon bg-info bg-opacity-10 text-info mx-auto">
                        <i class="bi bi-printer-fill"></i>
                    </div>
                    <span class="fw-bold small kanit">พิมพ์รายงาน</span>
                </a>
            </div>

            <!-- ปุ่มโหลดใบลงเวลา ( regis_time.pdf ) -->
            <div class="col-12 mt-4">
                <a href="regis_time.pdf" target="_blank" class="card card-menu shadow-sm p-3 download-btn-card">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="menu-icon bg-purple-light text-purple mb-0 me-3">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                        </div>
                        <div class="text-start">
                            <span class="fw-bold d-block text-purple kanit">โหลดใบลงเวลาปฏิบัติงาน</span>
                            <small class="text-muted" style="font-size: 0.7rem;">คลิกเพื่อเปิดไฟล์ regis_time.pdf</small>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Advisor Info -->
        <div class="card mt-4 border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body">
                <div class="d-flex align-items-center text-dark mb-2">
                    <i class="bi bi-person-badge-fill me-2 text-danger"></i>
                    <span class="fw-bold kanit">ข้อมูลครูที่ปรึกษา</span>
                </div>
                <div class="ps-4 border-start border-2 border-danger-subtle">
                    <p class="small mb-1"><strong>ครูผู้ดูแล:</strong> <?php echo $user['advisor_name']; ?></p>
                    <p class="small text-muted mb-0"><strong>กลุ่มการเรียน:</strong> <?php echo $user['group_name']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation Bar -->
    <nav class="nav-bottom">
        <a href="dashboard.php" class="text-center text-decoration-none" style="color: var(--stc-crimson);">
            <i class="bi bi-house-door-fill fs-4 d-block"></i>
            <small class="kanit" style="font-size: 0.65rem;">หน้าหลัก</small>
        </a>
        <a href="report_form.php" class="text-center text-decoration-none text-muted">
            <i class="bi bi-plus-circle fs-4 d-block"></i>
            <small class="kanit" style="font-size: 0.65rem;">รายงาน</small>
        </a>
        <a href="profile.php" class="text-center text-decoration-none text-muted">
            <i class="bi bi-person fs-4 d-block"></i>
            <small class="kanit" style="font-size: 0.65rem;">โปรไฟล์</small>
        </a>
        <a href="logout.php" class="text-center text-decoration-none text-muted">
            <i class="bi bi-box-arrow-right fs-4 d-block"></i>
            <small class="kanit" style="font-size: 0.65rem;">ออกงาน</small>
        </a>
    </nav>

</body>
</html>