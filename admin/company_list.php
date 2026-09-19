<?php
session_start();
include "../config.php";

// ตรวจสอบสิทธิ์ Admin ทวิภาคี
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

// รวมข้อมูลสถานประกอบการที่ซ้ำกัน (GROUP BY) พร้อมนับจำนวนนักศึกษา
$sql = "
    SELECT 
        p.place_id,
        p.company_name,
        p.mentor_name,
        p.company_address,
        p.company_phone,
        p.training_days,
        p.workplace_lat,
        p.workplace_lng,
        COUNT(s.student_id) AS total_std,
        GROUP_CONCAT(CONCAT(s.fullname, ' (', s.group_name, ')') SEPARATOR ', ') AS student_names
    FROM internship_places p
    LEFT JOIN students s ON p.place_id = s.place_id
    WHERE p.company_name IS NOT NULL AND TRIM(p.company_name) != ''
    GROUP BY p.company_name, p.mentor_name, p.company_address, p.company_phone, p.training_days
    ORDER BY total_std DESC, p.company_name ASC
";
$query = mysqli_query($conn, $sql);
$total_places = mysqli_num_rows($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลสถานประกอบการ | DVT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 10px; padding: 12px; margin-bottom: 8px; transition: 0.2s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        .table thead th { background-color: #f8f9fa; font-weight: 600; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-4 text-center">
                <h6 class="fw-bold">ระบบงานทวิภาคี</h6>
                <small class="opacity-50">วท.สุพรรณบุรี</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="bilateral_groups.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link active" href="company_list.php"><i class="bi bi-building me-2"></i> สถานประกอบการ</a>
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <a class="nav-link text-danger" href="delete_students_by_group.php"><i class="bi bi-trash3 me-2"></i> ลบข้อมูลตามกลุ่ม</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4 p-md-5">
            <!-- Header & Action -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-dark mb-1"><i class="bi bi-building-check me-2 text-primary"></i>ข้อมูลสถานประกอบการฝึกงาน</h3>
                    <p class="text-muted mb-0">รวบรวมและจัดกลุ่มข้อมูลสถานประกอบการที่นักศึกษากรอกเข้ามาในระบบ</p>
                </div>
                <div>
                    <!-- ปุ่มเปิดหน้าพิมพ์รายงานสรุป A4 (เปิดแท็บใหม่พร้อมสั่งพิมพ์) -->
                    <a href="company_summary_report.php" target="_blank" class="btn btn-danger btn-lg shadow-sm">
                        <i class="bi bi-printer-fill me-2"></i> พิมพ์รายงานสรุป (PDF)
                    </a>
                </div>
            </div>

            <!-- Card สถิติและการค้นหา -->
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-4">
                            <span class="text-muted">จำนวนสถานประกอบการทั้งหมด:</span>
                            <h4 class="fw-bold text-primary mb-0"><?php echo number_format($total_places); ?> แห่ง</h4>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchInput" class="form-control bg-light border-start-0" placeholder="พิมพ์ชื่อสถานประกอบการ หรือชื่อครูฝึก เพื่อค้นหา...">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ตารางแสดงข้อมูลสถานประกอบการ -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="companyTable">
                            <thead>
                                <tr>
                                    <th class="ps-4" style="width: 5%;">#</th>
                                    <th style="width: 25%;">สถานประกอบการ</th>
                                    <th style="width: 20%;">ครูฝึก / วันที่ฝึก</th>
                                    <th style="width: 20%;">เบอร์โทรศัพท์ / ที่อยู่</th>
                                    <th class="text-center" style="width: 10%;">จำนวนนักศึกษา</th>
                                    <th style="width: 20%;" class="pe-4">นักศึกษาที่ฝึกงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                if ($total_places > 0):
                                    while ($row = mysqli_fetch_assoc($query)): 
                                ?>
                                <tr>
                                    <td class="ps-4 text-muted"><?php echo $i++; ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['company_name']); ?></div>
                                        <?php if (!empty($row['workplace_lat']) && !empty($row['workplace_lng'])): ?>
                                            <a href="https://maps.google.com/?q=<?php echo $row['workplace_lat']; ?>,<?php echo $row['workplace_lng']; ?>" target="_blank" class="badge bg-light text-primary text-decoration-none border mt-1">
                                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>ดูแผนที่
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><strong><?php echo htmlspecialchars($row['mentor_name'] ?: '-'); ?></strong></div>
                                        <small class="text-muted"><i class="bi bi-calendar-week me-1"></i><?php echo htmlspecialchars($row['training_days'] ?: '-'); ?></small>
                                    </td>
                                    <td>
                                        <div><i class="bi bi-telephone me-1 text-muted"></i><?php echo htmlspecialchars($row['company_phone'] ?: '-'); ?></div>
                                        <small class="text-muted d-block text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($row['company_address']); ?>">
                                            <?php echo htmlspecialchars($row['company_address'] ?: '-'); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 fs-6">
                                            <?php echo $row['total_std']; ?> คน
                                        </span>
                                    </td>
                                    <td class="pe-4">
                                        <small class="text-secondary d-block" style="max-height: 75px; overflow-y: auto;">
                                            <?php echo $row['student_names'] ?: '<span class="text-muted">-</span>'; ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                else: 
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">ไม่พบข้อมูลสถานประกอบการ</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // สคริปต์กรองค้นหาข้อมูลในตารางแบบเรียลไทม์
    document.getElementById('searchInput').addEventListener('keyup', function() {
        let filter = this.value.toLowerCase();
        let rows = document.querySelectorAll('#companyTable tbody tr');

        rows.forEach(row => {
            let text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>
</body>
</html>