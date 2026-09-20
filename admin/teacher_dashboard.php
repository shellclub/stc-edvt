<?php
session_start();
include "../config.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'teacher') {
    header("location: admin_login.php"); 
    exit();
}

$teacher_id = $_SESSION['admin_id'];
$teacher_name = $_SESSION['admin_name'];

// โฟลเดอร์ JSON
define('JSON_DIR', __DIR__ . '/teacher_data/');
$json_file = JSON_DIR . "groups_{$teacher_id}.json";
$my_saved_groups = file_exists($json_file) ? (json_decode(file_get_contents($json_file), true) ?: []) : [];

// จัดการการลบกลุ่มออกจากรายการติดตาม (ถ้าครูไม่ต้องการดูแล้ว)
if (isset($_GET['remove_group'])) {
    $rm_group = $_GET['remove_group'];
    $my_saved_groups = array_values(array_diff($my_saved_groups, [$rm_group]));
    file_put_contents($json_file, json_encode($my_saved_groups, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header("location: teacher_dashboard.php");
    exit();
}

// -------------------------------------------------------------
// ดึงข้อมูลกลุ่ม นักศึกษา และรายงาน จากกลุ่มที่บันทึกไว้ใน JSON
// -------------------------------------------------------------
$total_students = 0;
$query_groups_data = null;
$query_reports_data = null;

if (!empty($my_saved_groups)) {
    // นำชื่อกลุ่มมาแปลงเป็นสตริงสำหรับคำสั่ง SQL IN ('...', '...')
    $escaped_groups = array_map(function($g) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $g) . "'";
    }, $my_saved_groups);
    $in_clause = implode(",", $escaped_groups);

    // 1. ดึงสถิตินักศึกษารวม
    $res_count = mysqli_query($conn, "SELECT COUNT(student_id) as total FROM students WHERE group_name IN ($in_clause)");
    $total_students = mysqli_fetch_assoc($res_count)['total'];

    // 2. ดึงรายละเอียดแต่ละกลุ่มที่บันทึกไว้
    $sql_groups = "SELECT group_code, group_name, COUNT(student_id) as total_std 
                   FROM students 
                   WHERE group_name IN ($in_clause) 
                   GROUP BY group_code, group_name
                   ORDER BY group_name ASC";
    $query_groups_data = mysqli_query($conn, $sql_groups);

    // 3. ดึงรายงานฝึกงานล่าสุดของกลุ่มเหล่านี้ (10 รายการล่าสุด)
    $sql_reports = "SELECT r.report_date, r.job_details, s.fullname, s.group_name, r.student_id 
                    FROM internship_reports r
                    INNER JOIN students s ON r.student_id = s.student_id
                    WHERE s.group_name IN ($in_clause)
                    ORDER BY r.report_date DESC, r.report_id DESC 
                    LIMIT 10";
    $query_reports_data = mysqli_query($conn, $sql_reports);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Teacher Dashboard | ระบบงานทวิภาคี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        .sidebar { background: #198754; min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.8); border-radius: 10px; padding: 12px; margin-bottom: 6px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.2); color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-4 text-center">
                <div class="bg-white p-2 rounded-circle d-inline-block mb-2 shadow-sm">
                    <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=TeacherProfile&backgroundColor=c0aede" width="55" height="55" class="rounded-circle" alt="Logo">
                </div>
                <h6 class="fw-bold mb-0">ระบบงานทวิภาคี</h6>
                <small class="badge bg-light text-success mt-1">ครูนิเทศก์</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link active" href="teacher_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="teacher_search.php"><i class="bi bi-search me-2"></i> ค้นหา/เพิ่มกลุ่ม</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark mb-1">กลุ่มนักศึกษาที่ติดตาม</h3>
                    <p class="text-muted mb-0">ยินดีต้อนรับอาจารย์ <strong><?php echo htmlspecialchars($teacher_name); ?></strong></p>
                </div>
                <a href="teacher_search.php" class="btn btn-success rounded-pill px-4 shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> ค้นหาและติดตามกลุ่มใหม่
                </a>
            </div>

            <!-- สรุปสถิติ -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-success border-4">
                        <small class="text-muted d-block">กลุ่มที่คุณติดตาม</small>
                        <h3 class="fw-bold text-success mb-0"><?php echo count($my_saved_groups); ?> กลุ่ม</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-primary border-4">
                        <small class="text-muted d-block">นักศึกษาในกลุ่มที่ติดตามทั้งหมด</small>
                        <h3 class="fw-bold text-primary mb-0"><?php echo number_format($total_students); ?> คน</h3>
                    </div>
                </div>
            </div>

            <?php if (empty($my_saved_groups)): ?>
                <!-- กรณีครูยังไม่ได้บันทึกกลุ่มใดๆ -->
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="bi bi-bookmark-plus text-muted" style="font-size: 3.5rem;"></i>
                    <h5 class="fw-bold mt-3">คุณยังไม่ได้บันทึกกลุ่มที่ต้องการติดตาม</h5>
                    <p class="text-muted">สามารถค้นหารหัสกลุ่มหรือชื่อห้องเรียนที่ออกฝึกงานเพื่อบันทึกเก็บไว้ดูได้ทันที</p>
                    <div>
                        <a href="teacher_search.php" class="btn btn-success px-4 rounded-pill">
                            <i class="bi bi-search me-1"></i> เริ่มต้นค้นหากลุ่ม
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- ตารางกลุ่มที่บันทึกไว้ใน JSON -->
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-collection-fill text-success me-2"></i>กลุ่มที่คุณติดตาม</h5>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">ชื่อกลุ่ม</th>
                                            <th class="text-center">จำนวนนักศึกษา</th>
                                            <th class="text-center pe-4">การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($g = mysqli_fetch_assoc($query_groups_data)): ?>
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold"><?php echo htmlspecialchars($g['group_name']); ?></div>
                                                <small class="text-muted"><code><?php echo htmlspecialchars($g['group_code'] ?: '-'); ?></code></small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-success-subtle text-success px-3 py-1 rounded-pill">
                                                    <?php echo $g['total_std']; ?> คน
                                                </span>
                                            </td>
                                            <td class="text-center pe-4">
                                                <!-- ปุ่มดูรายชื่อนักศึกษาในกลุ่ม -->
                                                <a href="teacher_student_list.php?gname=<?php echo urlencode($g['group_name']); ?>" [cite: 3]
                                                   class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                                    ดูนักศึกษา
                                                </a>
                                                <!-- ปุ่มลบออกจากรายการติดตามใน JSON -->
                                                <a href="teacher_dashboard.php?remove_group=<?php echo urlencode($g['group_name']); ?>" [cite: 3]
                                                   class="btn btn-outline-danger btn-sm rounded-pill ms-1" 
                                                   onclick="return confirm('ต้องการยกเลิกการติดตามกลุ่มนี้หรือไม่?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- รายการรายงานล่าสุดของกลุ่มที่ติดตาม -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header bg-white py-3 border-0">
                                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-check text-primary me-2"></i>รายงานล่าสุดของกลุ่มที่ติดตาม</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush rounded-bottom-4">
                                    <?php if ($query_reports_data && mysqli_num_rows($query_reports_data) > 0): ?>
                                        <?php while ($rep = mysqli_fetch_assoc($query_reports_data)): 
                                            $d_th = date("d/m/", strtotime($rep['report_date'])) . (date("Y", strtotime($rep['report_date'])) + 543);
                                        ?>
                                        <div class="list-group-item p-3">
                                            <div class="d-flex justify-content-between">
                                                <strong class="text-success"><?php echo htmlspecialchars($rep['fullname']); ?></strong>
                                                <small class="text-muted"><?php echo $d_th; ?></small>
                                            </div>
                                            <small class="badge bg-light text-dark border mb-1"><?php echo htmlspecialchars($rep['group_name']); ?></small>
                                            <p class="text-muted small mb-0 text-truncate"><?php echo htmlspecialchars($rep['job_details']); ?></p>
                                        </div>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <div class="p-4 text-center text-muted">ยังไม่มีบันทึกรายงานจากนักศึกษาในกลุ่มที่ติดตาม</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>