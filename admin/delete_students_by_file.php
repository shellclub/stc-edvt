<?php
session_start();
// เรียกใช้ไฟล์ config.php เดียวกับระบบเดิม
include "../config.php";

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

$message = "";
$status = "";

// ทำงานเมื่อกดปุ่มยืนยันการลบ
if (isset($_POST['btn_delete'])) {
    if (isset($_FILES['csv_file']['tmp_name']) && is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
        $fileName = $_FILES['csv_file']['tmp_name'];
        $fileExt = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));

        if ($fileExt === 'csv') {
            $handle = fopen($fileName, "r");
            $deleted_students = 0;
            $deleted_reports = 0;

            // วนลูปอ่านข้อมูลในไฟล์ CSV ทีละแถว
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // คอลัมน์ที่ 1 (Index 0) คือ student_id
                $sid = trim($data[0]);

                // กรองแถวว่าง หรือแถวที่เป็นหัวตาราง (เช่น คำว่า student_id หรือ รหัสนักศึกษา)
                if (empty($sid) || !is_numeric($sid)) {
                    continue;
                }

                $sid = mysqli_real_escape_string($conn, $sid);

                // 1. ดึงชื่อไฟล์รูปภาพจากตาราง internship_reports ของนักศึกษาคนนี้ เพื่อลบไฟล์จริงในเครื่อง
                $sql_get_imgs = "SELECT report_image FROM internship_reports WHERE student_id = '$sid' AND report_image IS NOT NULL AND report_image != ''";
                $query_imgs = mysqli_query($conn, $sql_get_imgs);
                while ($img_row = mysqli_fetch_assoc($query_imgs)) {
                    $image_path = "../uploads/" . $img_row['report_image'];
                    if (file_exists($image_path)) {
                        @unlink($image_path); // สั่งลบรูปภาพจริง
                    }
                }

                // 2. ลบข้อมูลบันทึกรายงานจากตาราง internship_reports
                $sql_del_reports = "DELETE FROM internship_reports WHERE student_id = '$sid'";
                mysqli_query($conn, $sql_del_reports);
                $deleted_reports += mysqli_affected_rows($conn);

                // 3. ลบข้อมูลนักศึกษาจากตาราง students
                $sql_del_student = "DELETE FROM students WHERE student_id = '$sid'";
                mysqli_query($conn, $sql_del_student);
                if (mysqli_affected_rows($conn) > 0) {
                    $deleted_students++;
                }
            }

            fclose($handle);
            $status = "success";
            $message = "ดำเนินการลบสำเร็จ: ลบนักศึกษาจำนวน {$deleted_students} คน และลบรายงานฝึกงานที่เกี่ยวข้องจำนวน {$deleted_reports} รายการ";
        } else {
            $status = "danger";
            $message = "ไฟล์ไม่ถูกต้อง กรุณาอัปโหลดไฟล์นามสกุล .csv เท่านั้น";
        }
    } else {
        $status = "warning";
        $message = "กรุณาเลือกไฟล์ CSV ก่อนกดดำเนินการ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลบข้อมูลนักศึกษาจากฐานข้อมูล | วท.สุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        .sidebar { background: #1a237e; min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 10px; padding: 12px; margin-bottom: 8px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- เมนูด้านข้าง -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-4 text-center">
                <h6 class="fw-bold">ระบบงานทวิภาคี</h6>
                <small class="opacity-50">วท.สุพรรณบุรี</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="bilateral_groups.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <a class="nav-link active bg-danger text-white" href="delete_students_by_file.php"><i class="bi bi-trash3 me-2"></i> ลบข้อมูลนักศึกษา</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- พื้นที่เนื้อหาหลัก -->
        <div class="col-md-10 p-4 p-md-5">
            <div class="d-flex align-items-center mb-4">
                <a href="bilateral_dashboard.php" class="btn btn-outline-secondary me-3 btn-sm"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
                <h3 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>ลบข้อมูลนักศึกษาออกจากฐานข้อมูล</h3>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show shadow-sm" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0 rounded-4 col-lg-8">
                <div class="card-body p-4">
                    <p class="text-muted">
                        อัปโหลดไฟล์ <strong>.CSV</strong> ที่มีรหัสนักศึกษา ระบบจะทำการลบข้อมูลออกจากตาราง <code>students</code>, รายงานฝึกงานจากตาราง <code>internship_reports</code> และลบไฟล์รูปภาพในโฟลเดอร์จริง
                    </p>

                    <form action="" method="post" enctype="multipart/form-data" onsubmit="return confirm('ยืนยันการลบข้อมูลหรือไม่? ข้อมูลนักศึกษาและรายงานฝึกงานทั้งหมดจะถูกลบออกจากฐานข้อมูลถาวร');">
                        <div class="mb-4">
                            <label class="form-label fw-bold">เลือกไฟล์รายชื่อ (.CSV)</label>
                            <input type="file" name="csv_file" class="form-control form-control-lg" accept=".csv" required>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i> ใช้ไฟล์ <b>CSV</b> โดยให้ <b>คอลัมน์ A (แถวแรก) เป็นรหัสนักศึกษา</b>
                            </small>
                        </div>

                        <button type="submit" name="btn_delete" class="btn btn-danger btn-lg px-4 shadow-sm">
                            <i class="bi bi-trash3 me-1"></i> ลบข้อมูลทันที
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>