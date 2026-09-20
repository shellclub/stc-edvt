<?php
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) { header("location: index.php"); exit(); }

$id = mysqli_real_escape_string($conn, $_GET['id']);
$sid = $_SESSION['student_id'];

$sql = "SELECT * FROM internship_reports WHERE report_id = '$id' AND student_id = '$sid'";
$query = mysqli_query($conn, $sql);
$data = mysqli_fetch_array($query);

if (!$data) { echo "ไม่พบข้อมูล"; exit(); }

function dateThai($strDate) {
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonthCut = Array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
    return date("j", strtotime($strDate)) . " " . $strMonthCut[date("n", strtotime($strDate))] . " " . $strYear;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขรายงาน - วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Sarabun', sans-serif; padding-bottom: 30px; }
        .header-edit { background: #800000; color: white; padding: 20px 0; border-radius: 0 0 20px 20px; }
        .card-custom { border: none; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .img-container { position: relative; width: 100%; max-height: 250px; overflow: hidden; border-radius: 15px; border: 2px dashed #ddd; background: #f8f9fa; }
        .img-container img { width: 100%; height: auto; object-fit: cover; }
        .btn-update { background: #800000; color: white; border: none; border-radius: 12px; font-weight: 600; padding: 15px; }
    </style>
</head>
<body>

    <div class="header-edit mb-4 shadow-sm">
        <div class="container d-flex align-items-center">
            <a href="report_history.php" class="text-white me-3"><i class="bi bi-chevron-left fs-4"></i></a>
            <h4 class="mb-0">แก้ไขข้อมูลรายงาน</h4>
        </div>
    </div>

    <div class="container">
        <div class="card card-custom">
            <div class="card-body p-4">
                <form action="report_update.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="report_id" value="<?php echo $data['report_id']; ?>">

                    <div class="mb-4 text-center">
                        <span class="badge bg-secondary p-2 px-3 rounded-pill">
                            <i class="bi bi-calendar3 me-2"></i> วันที่รายงาน: <?php echo dateThai($data['report_date']); ?>
                        </span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">รายละเอียดงานที่ทำ</label>
                        <textarea name="job_details" class="form-control" rows="6" required><?php echo $data['job_details']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ปัญหาและอุปสรรค</label>
                        <textarea name="problems" class="form-control" rows="2"><?php echo $data['problems']; ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">รูปภาพประกอบ</label>
                        <div class="img-container mb-2 d-flex align-items-center justify-content-center">
                            <?php if($data['report_image'] != "" && file_exists("uploads/".$data['report_image'])): ?>
                                <img src="uploads/<?php echo $data['report_image']; ?>" id="preview">
                            <?php else: ?>
                                <div id="no-img" class="text-muted text-center p-5">
                                    <i class="bi bi-camera fs-1 d-block"></i>
                                    <span>ไม่มีรูปภาพเดิม</span>
                                </div>
                                <img src="#" id="preview" style="display:none;">
                            <?php endif; ?>
                        </div>
                        <input type="file" name="report_image" id="fileInput" class="form-control" accept="image/*">
                        <small class="text-muted mt-1 d-block">* หากไม่เลือกใหม่ จะใช้รูปภาพเดิม</small>
                    </div>

                    <button type="submit" class="btn btn-update w-100 mb-3 shadow">
                        <i class="bi bi-check-circle-fill me-2"></i> บันทึกการแก้ไข
                    </button>
                    <a href="report_history.php" class="btn btn-light w-100 py-3" style="border-radius:12px;">ยกเลิก</a>
                </form>
            </div>
        </div>
    </div>

    <script>
        fileInput.onchange = evt => {
            const [file] = fileInput.files;
            if (file) {
                if(document.getElementById('no-img')) document.getElementById('no-img').style.display = 'none';
                preview.src = URL.createObjectURL(file);
                preview.style.display = 'block';
            }
        }
    </script>
</body>
</html>