<?php
session_start();
include "../config.php";
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); exit();
}

$gname = $_GET['gname']; 

// ดึงข้อมูลนักศึกษาในกลุ่มนี้
$sql = "SELECT * FROM students WHERE group_name = '$gname' ORDER BY student_id ASC";
$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อนักศึกษา | กลุ่ม <?php echo $gname; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; --secondary: #3f51b5; --light-bg: #f8f9fa; }
        body { font-family: 'Sarabun', sans-serif; background-color: var(--light-bg); }
        .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 12px; padding: 12px 15px; margin-bottom: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        
        .table-card { border: none; border-radius: 25px; background: white; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .table thead th { background-color: #fcfcfd; border-bottom: 2px solid #f1f1f1; padding: 20px; font-weight: 600; color: #666; font-size: 0.85rem; text-transform: uppercase; }
        .table tbody td { padding: 15px 20px; vertical-align: middle; border-bottom: 1px solid #f8f8f8; }
        
        /* สไตล์รูปนักศึกษา */
        .std-img { 
            width: 50px; 
            height: 60px; 
            object-fit: cover; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: 2px solid white;
        }
        .avatar-placeholder {
            width: 50px; 
            height: 60px; 
            background: #eef0f7; 
            color: var(--primary); 
            border-radius: 10px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold;
            font-size: 1.2rem;
        }

        .btn-view { background-color: #f0f3ff; color: var(--primary); border: none; border-radius: 10px; padding: 8px 16px; font-weight: 600; transition: 0.3s; }
        .btn-view:hover { background-color: var(--primary); color: white; }
        .btn-info-user { background-color: #fff4e5; color: #d97706; border: none; border-radius: 10px; width: 40px; height: 40px; transition: 0.3s; }
        .btn-info-user:hover { background-color: #d97706; color: white; }
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
                <hr class="mx-2 text-white-50">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">กลุ่ม <?php echo $gname; ?></h2>
                    <p class="text-muted mb-0">แสดงรายชื่อพร้อมรูปถ่ายจากระบบ RMS</p>
                </div>
                <button class="btn btn-outline-primary border-2 rounded-pill px-4 shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i> พิมพ์รายชื่อ
                </button>
            </div>

            <div class="table-card shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4 text-center" style="width: 80px;">รูปถ่าย</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>รหัสประจำตัว</th>
                                <th class="text-center">ข้อมูลวันเกิด</th>
                                <th>สถานะ</th>
                                <th class="text-center">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_array($query)): ?>
                            <tr>
                                <td class="ps-4 text-center">
                                    <!-- รูปจากลิงก์ RMS -->
                                    <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $row['student_id']; ?>.jpg&x=200&f=0" 
                                         class="std-img" 
                                         alt="Profile"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <!-- กรณีไม่มีรูปให้แสดงตัวอักษรแทน -->
                                    <div class="avatar-placeholder mx-auto" style="display:none;">
                                        <?php echo mb_substr($row['fullname'], 0, 1, "UTF-8"); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $row['fullname']; ?></div>
                                    <small class="text-muted">วท.สุพรรณบุรี</small>
                                </td>
                                <td><span class="badge bg-light text-dark border p-2 fw-normal"><?php echo $row['student_id']; ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-info-user" data-bs-toggle="modal" data-bs-target="#modal<?php echo $row['student_id']; ?>">
                                        <i class="bi bi-calendar-event"></i>
                                    </button>

                                    <!-- Modal ดูวันเกิด -->
                                    <div class="modal fade" id="modal<?php echo $row['student_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
                                                <div class="modal-body text-center p-5">
                                                    <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $row['student_id']; ?>.jpg&x=200&f=0" 
                                                         style="width:100px; height:120px; object-fit:cover; border-radius:15px;" class="mb-3 shadow-sm">
                                                    <h4 class="fw-bold mb-1"><?php echo $row['fullname']; ?></h4>
                                                    <p class="text-muted small mb-3">รหัสประจำตัว: <?php echo $row['student_id']; ?></p>
                                                    <div class="bg-light p-3 rounded-4">
                                                        <span class="text-muted d-block small">วันเดือนปีเกิด</span>
                                                        <h3 class="text-primary fw-bold mb-0"><?php echo $row['birth_date']; ?></h3>
                                                    </div>
                                                    <button class="btn btn-secondary w-100 rounded-pill mt-4" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['is_first_login'] == 1): ?>
                                        <span class="badge bg-warning-subtle text-warning rounded-pill px-3">ยังไม่ได้ยืนยัน</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3">ยืนยันแล้ว</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="admin_view_student_report.php?sid=<?php echo $row['student_id']; ?>" class="btn btn-view">
                                        ตรวจรายงาน
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>