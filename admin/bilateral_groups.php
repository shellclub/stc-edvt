<?php
session_start();
include "../config.php";
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); exit();
}

// ดึงข้อมูลกลุ่ม พร้อมนับจำนวนนักศึกษาในแต่ละกลุ่ม
$sql = "SELECT group_name, COUNT(student_id) as total_students 
        FROM students 
        WHERE group_name IS NOT NULL AND group_name != ''
        GROUP BY group_name 
        ORDER BY group_name ASC";
$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กลุ่มการเรียน | งานทวิภาคี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; --secondary: #3f51b5; --accent: #00d2ff; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f2f5; }
        
        .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 12px; padding: 12px 15px; margin-bottom: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        
        /* Group Card Styling */
        .group-card { 
            border: none; 
            border-radius: 24px; 
            background: white; 
            transition: all 0.3s ease;
            overflow: hidden;
            border-bottom: 4px solid transparent;
        }
        .group-card:hover { 
            transform: translateY(-10px); 
            box-shadow: 0 15px 30px rgba(26, 35, 126, 0.12);
            border-bottom: 4px solid var(--accent);
        }
        .icon-circle {
            width: 60px;
            height: 60px;
            background: #f0f3ff;
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .student-count-badge {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
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
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link active" href="bilateral_groups.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold text-dark mb-1">กลุ่มนักศึกษาฝึกงาน</h2>
                    <p class="text-muted">ข้อมูลสรุปแยกตามกลุ่มการเรียนที่บันทึกในระบบ</p>
                </div>
                <button class="btn btn-primary rounded-pill px-4" onclick="location.href='import_excel.php'">
                    <i class="bi bi-plus-circle me-2"></i> เพิ่มกลุ่ม/นักศึกษา
                </button>
            </div>

            <div class="row g-4">
                <?php if(mysqli_num_rows($query) > 0): ?>
                    <?php while($row = mysqli_fetch_array($query)): ?>
                    <div class="col-xl-4 col-md-6">
                        <div class="card group-card shadow-sm h-100 shadow-hover" 
                             onclick="location.href='bilateral_student_list.php?gname=<?php echo urlencode($row['group_name']); ?>'" 
                             style="cursor: pointer;">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-4">
                                    <div class="icon-circle shadow-sm">
                                        <i class="bi bi-collection"></i>
                                    </div>
                                    <span class="student-count-badge">
                                        <i class="bi bi-person-fill me-1"></i> <?php echo $row['total_students']; ?> คน
                                    </span>
                                </div>
                                
                                <h4 class="fw-bold mb-2 text-dark"><?php echo $row['group_name']; ?></h4>
                                <p class="text-muted small mb-4">ตรวจสอบประวัติการลงเวลาและรายงานการฝึกงานของนักศึกษาในกลุ่มนี้</p>
                                
                                <div class="d-flex align-items-center text-primary fw-bold">
                                    เรียกดูรายชื่อ <i class="bi bi-arrow-right ms-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-folder-x text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">ยังไม่มีข้อมูลกลุ่มในระบบ</h5>
                        <p class="small text-muted">กรุณานำเข้าข้อมูลจากไฟล์ Excel เพื่อเริ่มต้น</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>