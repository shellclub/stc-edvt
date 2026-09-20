<?php
session_start();
include "../config.php";

// เช็คสถานะการเข้าสู่ระบบของครู (ถ้ามี)
$is_teacher_logged_in = (isset($_SESSION['admin_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher');
$teacher_id = $is_teacher_logged_in ? $_SESSION['admin_id'] : null;

// โฟลเดอร์เก็บข้อมูล JSON
define('JSON_DIR', __DIR__ . '/teacher_data/');
if (!file_exists(JSON_DIR)) { 
    mkdir(JSON_DIR, 0777, true); 
}

function getTeacherSavedGroups($tid) {
    if (!$tid) return [];
    $file = JSON_DIR . "groups_{$tid}.json";
    return file_exists($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];
}

// การทำงานเมื่อครูที่ Login กดบันทึกกลุ่มเข้า JSON ของตนเอง
if ($is_teacher_logged_in && isset($_POST['btn_save_group'])) {
    $gname = trim($_POST['group_name']);
    $file = JSON_DIR . "groups_{$teacher_id}.json";
    $saved = getTeacherSavedGroups($teacher_id);
    
    if (!in_array($gname, $saved)) {
        $saved[] = $gname;
        file_put_contents($file, json_encode($saved, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
    header("location: teacher_dashboard.php");
    exit();
}

// การค้นหากลุ่ม
$search_query = "";
$search_result = null;
if (isset($_GET['keyword']) && trim($_GET['keyword']) !== '') {
    $kw = mysqli_real_escape_string($conn, trim($_GET['keyword']));
    $sql = "SELECT group_code, group_name, advisor_name, COUNT(student_id) as total_std 
            FROM students 
            WHERE (group_code LIKE '%$kw%' OR group_name LIKE '%$kw%' OR advisor_name LIKE '%$kw%')
              AND group_name IS NOT NULL AND group_name != ''
            GROUP BY group_code, group_name, advisor_name
            ORDER BY group_code ASC, group_name ASC";
    $search_result = mysqli_query($conn, $sql);
}

$saved_groups = $is_teacher_logged_in ? getTeacherSavedGroups($teacher_id) : [];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ค้นหากลุ่มนักศึกษาฝึกงาน | ระบบงานทวิภาคี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f4f7fa; 
            color: #333;
        }
        .search-box {
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="py-4">

<div class="container col-lg-9">
    
    <!-- ส่วนหัวหน้าจอ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h3 class="fw-bold text-success mb-0">
                <i class="bi bi-search me-2"></i>ค้นหากลุ่มนักศึกษาฝึกประสบการณ์ฯ
            </h3>
            <small class="text-muted">คุณครูสามารถค้นหารหัสกลุ่ม หรือชื่อกลุ่มเพื่อดูข้อมูลนักศึกษาได้ทันที</small>
        </div>
        <div>
            <?php if ($is_teacher_logged_in): ?>
                <a href="teacher_dashboard.php" class="btn btn-outline-success rounded-pill px-3 shadow-sm">
                    <i class="bi bi-person-circle me-1"></i> กลับหน้าหลักของฉัน
                </a>
            <?php else: ?>
                <a href="index.php" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบครูนิเทศก์
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- แถบแจ้งเตือนสถานะ Login -->
    <?php if (!$is_teacher_logged_in): ?>
        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center">
            <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
            <div>
                <strong>สำหรับครูนิเทศก์:</strong> หากต้องการบันทึกกลุ่มที่ดูแลไว้ในหน้าหลักเพื่อเปิดดูครั้งถัดไปได้ทันที กรุณา 
                <a href="index.php" class="alert-link text-decoration-underline">เข้าสู่ระบบ</a> ก่อนบันทึกกลุ่ม
            </div>
        </div>
    <?php endif; ?>

    <!-- กล่องฟอร์มค้นหา -->
    <div class="card search-box border-0 mb-4">
        <div class="card-body p-4">
            <form method="get" action="">
                <label class="form-label fw-bold mb-2">คำค้นหา (รหัสกลุ่ม / ชื่อห้องเรียน / ครูที่ปรึกษา)</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search fs-5"></i></span>
                    <input type="text" name="keyword" class="form-control form-control-lg border-start-0 ps-0" 
                           placeholder="พิมพ์รหัสกลุ่ม เช่น 6530... หรือชื่อห้อง เช่น ชฟ.1/1..." 
                           value="<?php echo htmlspecialchars($_GET['keyword'] ?? ''); ?>" required autofocus>
                    <button class="btn btn-success px-4 fw-bold" type="submit">ค้นหากลุ่ม</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ตารางแสดงผลการค้นหา -->
    <?php if ($search_result !== null): ?>
        <div class="card search-box border-0">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark">
                    <i class="bi bi-list-check me-2 text-success"></i>ผลการค้นหากลุ่ม
                </h5>
                <span class="badge bg-light text-dark border px-3 py-2">
                    พบ <?php echo mysqli_num_rows($search_result); ?> รายการ
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 15%;">รหัสกลุ่ม</th>
                                <th style="width: 30%;">ชื่อกลุ่มการเรียน</th>
                                <th style="width: 25%;">ครูที่ปรึกษา</th>
                                <th class="text-center" style="width: 12%;">จำนวนนักศึกษา</th>
                                <th class="text-center pe-4" style="width: 18%;">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($search_result) > 0): ?>
                                <?php while ($r = mysqli_fetch_assoc($search_result)): 
                                    $is_saved = in_array($r['group_name'], $saved_groups);
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                            <?php echo htmlspecialchars($r['group_code'] ?: '-'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-dark"><?php echo htmlspecialchars($r['group_name']); ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <i class="bi bi-person me-1"></i><?php echo htmlspecialchars($r['advisor_name'] ?: '-'); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">
                                            <?php echo $r['total_std']; ?> คน
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex justify-content-center gap-1">
                                            <!-- ปุ่มดูรายชื่อนักศึกษา (เปิดดูได้ทุกคน ไม่ต้องล็อกอิน) -->
                                            <a href="teacher_student_list.php?gname=<?php echo urlencode($r['group_name']); ?>" 
                                               class="btn btn-outline-primary btn-sm rounded-pill px-3" title="ดูข้อมูลนักศึกษาในกลุ่ม">
                                                ดูข้อมูล
                                            </a>

                                            <!-- ปุ่มบันทึกลง JSON (แสดงเฉพาะครูที่ Login แล้ว) -->
                                            <?php if ($is_teacher_logged_in): ?>
                                                <?php if ($is_saved): ?>
                                                    <span class="btn btn-light btn-sm rounded-pill text-success border disabled px-2" title="บันทึกไว้ในหน้าหลักแล้ว">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    </span>
                                                <?php else: ?>
                                                    <form method="post" action="" class="m-0">
                                                        <input type="hidden" name="group_name" value="<?php echo htmlspecialchars($r['group_name']); ?>">
                                                        <button type="submit" name="btn_save_group" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm" title="บันทึกไว้ติดตามใน Dashboard">
                                                            <i class="bi bi-bookmark-plus me-1"></i> ติดตาม
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        ไม่พบข้อมูลกลุ่มที่ตรงกับคำค้นหา "<?php echo htmlspecialchars($_GET['keyword']); ?>"
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>