<?php
session_start();
include "../config.php";

// ตรวจสอบสิทธิ์ Admin ทวิภาคี
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

$message = "";
$status = "";

// ส่วนประมวลผลการลบเมื่อมีการกดยืนยัน
if (isset($_POST['btn_delete_group'])) {
    $selected_group = mysqli_real_escape_string($conn, trim($_POST['target_group']));

    if (!empty($selected_group)) {
        // 1. ดึงรหัสนักศึกษาทั้งหมดที่อยู่ในกลุ่มที่เลือก
        $sql_students = "SELECT student_id FROM students WHERE group_name = '$selected_group'";
        $query_students = mysqli_query($conn, $sql_students);

        $student_ids = [];
        while ($row = mysqli_fetch_assoc($query_students)) {
            $student_ids[] = "'" . mysqli_real_escape_string($conn, $row['student_id']) . "'";
        }

        if (!empty($student_ids)) {
            $ids_in_query = implode(",", $student_ids);

            // 2. ค้นหารูปภาพทั้งหมดใน internship_reports ของนักศึกษากลุ่มนี้เพื่อลบไฟล์จริง
            $sql_imgs = "SELECT report_image FROM internship_reports 
                         WHERE student_id IN ($ids_in_query) 
                         AND report_image IS NOT NULL AND report_image != ''";
            $query_imgs = mysqli_query($conn, $sql_imgs);
            
            while ($img = mysqli_fetch_assoc($query_imgs)) {
                $image_path = "../uploads/" . $img['report_image'];
                if (file_exists($image_path)) {
                    @unlink($image_path); // ลบไฟล์รูปจริงออกจากเครื่อง
                }
            }

            // 3. ลบรายงานฝึกงานของนักศึกษากลุ่มนี้
            mysqli_query($conn, "DELETE FROM internship_reports WHERE student_id IN ($ids_in_query)");
            $deleted_reports = mysqli_affected_rows($conn);

            // 4. ลบข้อมูลนักศึกษาออกจากตาราง students
            mysqli_query($conn, "DELETE FROM students WHERE group_name = '$selected_group'");
            $deleted_students = mysqli_affected_rows($conn);

            $status = "success";
            $message = "ลบข้อมูลกลุ่ม <strong>" . htmlspecialchars($selected_group) . "</strong> สำเร็จ: ลบนักศึกษา {$deleted_students} คน และลบรายงานที่เกี่ยวข้อง {$deleted_reports} รายการ พร้อมไฟล์รูปภาพเรียบร้อยแล้ว";
        } else {
            $status = "warning";
            $message = "ไม่พบนักศึกษาในกลุ่มที่เลือก";
        }
    } else {
        $status = "danger";
        $message = "กรุณาเลือกกลุ่มที่ต้องการลบ";
    }
}

// ดึงสรุปข้อมูลกลุ่มและจำนวนนักศึกษามาแสดงผล
$sql_group_list = "SELECT group_name, group_code, COUNT(student_id) AS total_std 
                   FROM students 
                   WHERE group_name != '' 
                   GROUP BY group_name, group_code 
                   ORDER BY group_name ASC";
$query_group_list = mysqli_query($conn, $sql_group_list);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลบข้อมูลนักศึกษาตามกลุ่ม | DVT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 10px; padding: 12px; margin-bottom: 8px; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
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
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <a class="nav-link active bg-danger text-white" href="delete_students_by_group.php"><i class="bi bi-trash3 me-2"></i> ลบข้อมูลตามกลุ่ม</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-4 p-md-5">
            <div class="d-flex align-items-center mb-4">
                <a href="bilateral_dashboard.php" class="btn btn-outline-secondary me-3 btn-sm"><i class="bi bi-arrow-left"></i> ย้อนกลับ</a>
                <h3 class="fw-bold text-danger mb-0"><i class="bi bi-trash3-fill me-2"></i>จัดการลบข้อมูลนักศึกษาตามกลุ่ม</h3>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row g-4">
                <!-- ฟอร์มเลือกกลุ่มและปุ่มลบ -->
                <div class="col-lg-5">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-danger mb-3"><i class="bi bi-exclamation-triangle me-2"></i>เลือกกลุ่มที่ต้องการลบ</h5>
                            <p class="text-muted small">
                                การลบจะทำให้ข้อมูลของนักศึกษาในกลุ่มที่เลือก รวมถึง <strong>บันทึกรายงานประจำวัน และรูปภาพประกอบทั้งหมด</strong> ถูกลบถาวร
                            </p>

                            <form id="deleteForm" action="" method="post">
                                <div class="mb-4">
                                    <label class="form-label fw-bold">เลือกกลุ่มการเรียน</label>
                                    <select name="target_group" id="target_group" class="form-select form-select-lg" required>
                                        <option value="">-- กรุณาเลือกกลุ่ม --</option>
                                        <?php 
                                        mysqli_data_seek($query_group_list, 0);
                                        while ($g = mysqli_fetch_assoc($query_group_list)): 
                                        ?>
                                            <option value="<?php echo htmlspecialchars($g['group_name']); ?>" data-count="<?php echo $g['total_std']; ?>">
                                                <?php echo htmlspecialchars($g['group_name']); ?> (<?php echo $g['total_std']; ?> คน)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <button type="button" class="btn btn-danger btn-lg w-100 shadow-sm" onclick="confirmDelete()">
                                    <i class="bi bi-trash3 me-1"></i> ลบข้อมูลกลุ่มนี้
                                </button>

                                <input type="hidden" name="btn_delete_group" value="1">
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ตารางแสดงกลุ่มและจำนวนนักศึกษาที่มีในระบบ -->
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="bi bi-people-fill me-2 text-primary"></i>รายชื่อกลุ่มที่มีในฐานข้อมูล</h5>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>รหัสกลุ่ม (group_code)</th>
                                            <th>ชื่อกลุ่ม (group_name)</th>
                                            <th class="text-center">จำนวนนักศึกษา</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        mysqli_data_seek($query_group_list, 0);
                                        if (mysqli_num_rows($query_group_list) > 0):
                                            while ($row = mysqli_fetch_assoc($query_group_list)): 
                                        ?>
                                            <tr>
                                                <td><code><?php echo htmlspecialchars($row['group_code'] ?: '-'); ?></code></td>
                                                <td class="fw-bold"><?php echo htmlspecialchars($row['group_name']); ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary rounded-pill px-3 py-2"><?php echo $row['total_std']; ?> คน</span>
                                                </td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else: 
                                        ?>
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">ไม่พบข้อมูลกลุ่มนักศึกษา</td>
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
    </div>
</div>

<!-- Modal ยืนยันการลบ -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>ยืนยันการลบข้อมูลถาวร</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <i class="bi bi-x-circle text-danger" style="font-size: 4rem;"></i>
                <h5 class="fw-bold mt-3">คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูล?</h5>
                <p class="text-muted mb-0">กลุ่มที่เลือก: <strong id="modalGroupName" class="text-dark"></strong></p>
                <p class="text-danger small mt-2">
                    * นักศึกษาทั้งหมดในกลุ่มนี้ รายงานฝึกงาน และไฟล์รูปภาพจะถูกลบออกจาก Server โดยไม่สามารถกู้คืนได้
                </p>
            </div>
            <div class="modal-footer border-0 p-3 justify-content-center">
                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger px-4 shadow-sm" onclick="executeDelete()">ยืนยันการลบ</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

    function confirmDelete() {
        const select = document.getElementById('target_group');
        const selectedValue = select.value;

        if (!selectedValue) {
            alert('กรุณาเลือกกลุ่มที่ต้องการลบก่อนครับ');
            return;
        }

        document.getElementById('modalGroupName').innerText = selectedValue;
        confirmModal.show();
    }

    function executeDelete() {
        document.getElementById('deleteForm').submit();
    }
</script>
</body>
</html>