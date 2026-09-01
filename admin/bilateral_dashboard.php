<?php
session_start();
include "../config.php";
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); exit();
}

// ดึงสถิติต่างๆ มาโชว์ใน Card สรุป
$res_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM students");
$row_total = mysqli_fetch_assoc($res_total);

$res_groups = mysqli_query($conn, "SELECT COUNT(DISTINCT group_name) as total_g FROM students WHERE group_name != ''");
$row_groups = mysqli_fetch_assoc($res_groups);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DVT Dashboard | วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; --secondary: #3f51b5; --light: #f8f9fa; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        
        /* Sidebar Design */
        .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; box-shadow: 4px 0 10px rgba(0,0,0,0.05); }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 12px; padding: 12px 15px; margin-bottom: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        
        /* Stats Cards */
        .stat-card { border: none; border-radius: 20px; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08); }
        .icon-box { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        
        /* Quick Action */
        .action-card { border: 1px dashed #cbd5e0; border-radius: 20px; cursor: pointer; transition: 0.3s; height: 100%; }
        .action-card:hover { border-color: var(--secondary); background: #f0f4ff; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-4 text-center">
                <div class="bg-white p-2 rounded-circle d-inline-block mb-3 shadow-sm">
                    <img src="https://upload.wikimedia.org/wikipedia/th/d/d4/Vec_Logo.png" width="50" alt="Logo">
                </div>
                <h6 class="fw-bold">ระบบงานทวิภาคี</h6>
                <small class="opacity-50">วท.สุพรรณบุรี</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link active" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="bilateral_groups.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <a class="nav-link" href="#"><i class="bi bi-geo-alt me-2"></i> แผนที่พิกัดรวม</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold mb-1 text-dark">ภาพรวมระบบ</h2>
                    <p class="text-muted">ยินดีต้อนรับคุณ, <?php echo $_SESSION['admin_name']; ?></p>
                </div>
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold h5 mb-0" id="liveTime">00:00:00</div>
                    <small class="text-muted"><?php echo date('d/m/Y'); ?></small>
                </div>
            </div>

            <!-- Stats Overview -->
            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card stat-card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary-subtle text-primary me-3">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">นักศึกษาฝึกงานทั้งหมด</small>
                                <span class="h3 fw-bold mb-0"><?php echo $row_total['total']; ?> คน</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success-subtle text-success me-3">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">กลุ่มการเรียนที่มีในระบบ</small>
                                <span class="h3 fw-bold mb-0"><?php echo $row_groups['total_g']; ?> กลุ่ม</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-warning-subtle text-warning me-3">
                                <i class="bi bi-file-earmark-check"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">รายงานสัปดาห์ล่าสุด</small>
                                <span class="h3 fw-bold mb-0">95%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h5 class="fw-bold mb-4">เข้าถึงเมนูอย่างรวดเร็ว</h5>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card action-card p-4 text-center" onclick="location.href='bilateral_groups.php'">
                        <i class="bi bi-search fs-2 mb-3 text-primary"></i>
                        <h6 class="fw-bold">ดูข้อมูลแยกตามกลุ่ม</h6>
                        <small class="text-muted">ตรวจสอบรายงานและพิกัดตามห้องเรียน</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card action-card p-4 text-center" onclick="location.href='import_excel.php'">
                        <i class="bi bi-cloud-upload fs-2 mb-3 text-success"></i>
                        <h6 class="fw-bold">นำเข้าข้อมูลนักศึกษา</h6>
                        <small class="text-muted">อัปโหลดไฟล์ Excel (CSV) เข้าระบบ</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card action-card p-4 text-center">
                        <i class="bi bi-building-add fs-2 mb-3 text-info"></i>
                        <h6 class="fw-bold">สถานประกอบการ</h6>
                        <small class="text-muted">จัดการรายชื่อที่ฝึกงาน</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card action-card p-4 text-center">
                        <i class="bi bi-printer fs-2 mb-3 text-secondary"></i>
                        <h6 class="fw-bold">พิมพ์รายงานสรุป</h6>
                        <small class="text-muted">ส่งออกข้อมูลเป็น PDF/Excel</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function updateTime() {
        const now = new Date();
        document.getElementById('liveTime').innerText = now.toLocaleTimeString('th-TH');
    }
    setInterval(updateTime, 1000);
    updateTime();
</script>
</body>
</html>