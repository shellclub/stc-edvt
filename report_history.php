<?php
date_default_timezone_set('Asia/Bangkok');
session_start();
include "config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['student_id'])) {
    header("location: index.php");
    exit();
}

$sid = $_SESSION['student_id'];

// 2. ดึงข้อมูลรายงาน เรียงตามวันที่ล่าสุด
$sql = "SELECT * FROM internship_reports WHERE student_id = '$sid' ORDER BY report_date DESC";
$query = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการรายงาน - วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f6f9; padding-bottom: 90px; }
        .header-box { background: linear-gradient(135deg, #800000 0%, #a00000 100%); color: white; padding: 25px 0; border-radius: 0 0 25px 25px; margin-bottom: 20px; }
        .report-card { border: none; border-radius: 18px; margin-bottom: 15px; overflow: hidden; }
        .date-tag { background-color: #800000; color: white; border-radius: 10px; padding: 4px 12px; font-weight: 600; font-size: 0.9rem; }
        
        /* สไตล์รูปภาพและกรณีไม่มีรูป */
        .img-preview { width: 75px; height: 75px; object-fit: cover; border-radius: 12px; border: 1px solid #eee; }
        .empty-img { 
            width: 75px; height: 75px; 
            background-color: #fff5f5; 
            border: 1px dashed #fab1a0; 
            border-radius: 12px; 
            display: flex; flex-direction: column; 
            align-items: center; justify-content: center; 
            color: #dc3545; 
        }
        .no-img-text { font-size: 0.65rem; font-weight: bold; margin-top: 2px; }

        .btn-action { font-size: 0.85rem; border-radius: 10px; }
        .nav-bottom { position: fixed; bottom: 0; width: 100%; background: white; display: flex; justify-content: space-around; padding: 12px 0; box-shadow: 0 -5px 15px rgba(0,0,0,0.05); z-index: 1000; }
        .nav-link-custom { text-align: center; color: #6c757d; text-decoration: none; font-size: 0.75rem; }
        .nav-link-custom.active { color: #800000; font-weight: bold; }
        .nav-link-custom i { font-size: 1.4rem; display: block; }
    </style>
</head>
<body>

    <div class="header-box shadow-sm">
        <div class="container d-flex align-items-center">
            <a href="dashboard.php" class="text-white me-3 text-decoration-none"><i class="bi bi-chevron-left fs-3"></i></a>
            <h4 class="mb-0 fw-bold">ประวัติการรายงาน</h4>
        </div>
    </div>

    <div class="container">
        <?php if(mysqli_num_rows($query) > 0): ?>
            <?php while($row = mysqli_fetch_array($query)): ?>
                <div class="card report-card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="date-tag shadow-sm">
                                <i class="bi bi-calendar3 me-2"></i>
                                <?php echo dateThai($row['report_date']); ?>
                            </div>
                            <small class="text-muted" style="font-size: 0.7rem;">
                                บันทึกเมื่อ: <?php echo dateTimeThai($row['reported_at']); ?>
                            </small>
                        </div>

                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0">
                                <?php if($row['report_image'] != "" && file_exists("uploads/".$row['report_image'])): ?>
                                    <img src="uploads/<?php echo $row['report_image']; ?>" class="img-preview shadow-sm" onclick="window.open(this.src)">
                                <?php else: ?>
                                    <!-- กรณีไม่มีรูปภาพ -->
                                    <div class="empty-img text-center">
                                        <i class="bi bi-camera-video-off fs-4"></i>
                                        <span class="no-img-text">ไม่รายงานรูป</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="ms-3 flex-grow-1">
                                <p class="mb-1 fw-bold text-dark" style="font-size: 0.95rem;">งานที่ปฏิบัติ:</p>
                                <p class="mb-1 text-muted small text-break"><?php echo nl2br(htmlspecialchars($row['job_details'])); ?></p>
                                <?php if($row['problems']): ?>
                                    <p class="mb-0 text-danger small"><strong><i class="bi bi-exclamation-triangle"></i> ปัญหา:</strong> <?php echo htmlspecialchars($row['problems']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                            <div>
                                <?php if($row['location_lat']): ?>
                                    <a href="https://www.google.com/maps?q=<?php echo $row['location_lat']; ?>,<?php echo $row['location_lng']; ?>" target="_blank" class="text-primary text-decoration-none small fw-bold">
                                        <i class="bi bi-geo-alt-fill"></i> ดูพิกัด GPS
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="report_edit.php?id=<?php echo $row['report_id']; ?>" class="btn btn-outline-primary btn-action px-3 py-1"><i class="bi bi-pencil-square"></i> แก้ไข</a>
                                <a href="report_delete.php?id=<?php echo $row['report_id']; ?>" class="btn btn-outline-danger btn-action px-3 py-1" onclick="return confirm('ยืนยันการลบรายงาน?')"><i class="bi bi-trash"></i> ลบ</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center mt-5 py-5">
                <i class="bi bi-clipboard-x text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3">ยังไม่พบประวัติการส่งรายงาน</p>
                <a href="report_form.php" class="btn btn-primary px-4 py-2 mt-2" style="background:#800000; border:none; border-radius:12px;">เริ่มรายงานวันนี้</a>
            </div>
        <?php endif; ?>
    </div>

    <nav class="nav-bottom shadow">
        <a href="dashboard.php" class="nav-link-custom"><i class="bi bi-house-door"></i>หน้าหลัก</a>
        <a href="report_form.php" class="nav-link-custom"><i class="bi bi-plus-circle"></i>รายงาน</a>
        <a href="report_history.php" class="nav-link-custom active"><i class="bi bi-clock-history"></i>ประวัติ</a>
        <a href="logout.php" class="nav-link-custom text-danger" onclick="return confirm('ออกจากระบบ?')"><i class="bi bi-box-arrow-right"></i>ออกระบบ</a>
    </nav>
</body>
</html>