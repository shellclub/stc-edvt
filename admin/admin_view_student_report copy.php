<?php
session_start();
include "../config.php";

if (!isset($_SESSION['admin_id'])) { header("location: admin_login.php"); exit(); }

$sid = mysqli_real_escape_string($conn, $_GET['sid']);

// 1. ดึงข้อมูลนักศึกษา
$sql_std = "SELECT * FROM students WHERE student_id = '$sid'";
$res_std = mysqli_query($conn, $sql_std);
$std = mysqli_fetch_assoc($res_std);

// 2. ดึงข้อมูลรายงาน เรียงจากน้อยไปมาก
$sql_report = "SELECT * FROM internship_reports WHERE student_id = '$sid' ORDER BY report_date ASC";
$res_report = mysqli_query($conn, $sql_report);

$monthly_reports = [];
if ($res_report) {
    while($row = mysqli_fetch_assoc($res_report)) {
        $month_key = date("Y-m", strtotime($row['report_date']));
        $monthly_reports[$month_key][] = $row;
    }
}

// 3. รูปภาพจาก RMS
$student_img_url = "https://rms.stc.ac.th/image.php?src=files/importpicstd/01/" . $std['student_id'] . ".jpg&x=200&f=0";

function dateThai($strDate, $withTime = false) {
    if (!$strDate || $strDate == "0000-00-00 00:00:00") return "-";
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonthCut = Array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
    $time = $withTime ? " (".date("H:i", strtotime($strDate))." น.)" : "";
    return date("j", strtotime($strDate)) . " " . $strMonthCut[date("n", strtotime($strDate))] . " " . $strYear . $time;
}

function monthThaiName($monthKey) {
    $ex = explode('-', $monthKey);
    $months = Array("", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
    return $months[(int)$ex[1]] . " " . ($ex[0] + 543);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานสรุป: <?php echo $std['fullname']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #1a237e; }
        body { font-family: 'Sarabun', sans-serif; background: #f8f9fa; }
        
        .profile-header { background: var(--primary); padding: 40px 0 80px; color: white; border-radius: 0 0 40px 40px; }
        .std-img { width: 100px; height: 125px; object-fit: cover; border-radius: 15px; border: 4px solid white; shadow: 0 5px 15px rgba(0,0,0,0.2); }
        
        .accordion-item { border: none; margin-bottom: 15px; border-radius: 20px !important; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .accordion-button { font-weight: bold; padding: 20px; }
        
        .job-detail { font-size: 0.95rem; white-space: pre-line; word-wrap: break-word; color: #1e293b; }
        .prob-detail { font-size: 0.85rem; color: #b91c1c; background: #fff1f2; padding: 8px; border-radius: 8px; margin-top: 6px; border-left: 3px solid #f43f5e; }
        
        /* สไตล์การพิมพ์เพื่อให้ตัวอักษรครบ */
        @media print {
            @page { size: A4; margin: 1cm; }
            body { background: white !important; font-size: 12pt; }
            .no-print { display: none !important; }
            .accordion-collapse { display: block !important; }
            .accordion-item { border: 1px solid #000 !important; margin-bottom: 0px; page-break-after: auto; }
            .table { width: 100% !important; border-collapse: collapse !important; }
            .table th, .table td { border: 1px solid #000 !important; padding: 8px !important; word-wrap: break-word !important; }
            .job-detail { white-space: pre-wrap !important; }
            .print-header { display: block !important; margin-bottom: 20px; }
            .accordion-button::after { display: none !important; }
        }
        .print-header { display: none; text-align: center; }
        .img-preview { width: 50px; height: 50px; object-fit: cover; cursor: pointer; border: 1px solid #ddd; border-radius: 5px; transition: 0.2s; }
        .img-preview:hover { opacity: 0.8; transform: scale(1.1); }
    </style>
</head>
<body>

<div class="profile-header no-print text-center text-md-start">
    <div class="container d-md-flex align-items-center">
        <img src="<?php echo $student_img_url; ?>" class="std-img mx-auto mb-3 mb-md-0 me-md-4 shadow" alt="Student">
        <div>
            <h3 class="fw-bold mb-1"><?php echo $std['fullname']; ?></h3>
            <p class="mb-0 opacity-75">รหัสนักศึกษา: <?php echo $std['student_id']; ?> | กลุ่ม: <?php echo $std['group_name']; ?></p>
        </div>
        <div class="ms-auto mt-3 mt-md-0">
            <a href="bilateral_student_list.php?gname=<?php echo urlencode($std['group_name']); ?>" class="btn btn-light rounded-pill px-4 shadow-sm">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
        </div>
    </div>
</div>

<div class="container mt-n4" style="margin-top: -40px;">
    <div class="accordion" id="reportAccordion">
        <?php if (!empty($monthly_reports)): ?>
            <?php foreach ($monthly_reports as $m_key => $reports): ?>
                <div class="accordion-item shadow-sm" id="print-section-<?php echo $m_key; ?>">
                    
                    <!-- ส่วนหัวสำหรับการพิมพ์ -->
                    <div class="print-header">
                        <h3 style="margin-bottom: 5px;">รายงานการปฏิบัติงานประจำเดือน <?php echo monthThaiName($m_key); ?></h3>
                        <p>นักศึกษา: <?php echo $std['fullname']; ?> (<?php echo $std['student_id']; ?>) | แผนกวิชาอิเล็กทรอนิกส์</p>
                    </div>

                    <h2 class="accordion-header no-print">
                        <div class="accordion-button collapsed d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $m_key; ?>">
                            <span><i class="bi bi-folder-fill me-2 text-warning"></i> เดือน <?php echo monthThaiName($m_key); ?></span>
                            <div class="ms-auto d-flex align-items-center">
                                <span class="badge bg-primary rounded-pill me-3"><?php echo count($reports); ?> วัน</span>
                                <button onclick="printDiv('print-section-<?php echo $m_key; ?>')" class="btn btn-sm btn-outline-dark rounded-pill shadow-sm">
                                    <i class="bi bi-printer"></i> พิมพ์เดือนนี้
                                </button>
                            </div>
                        </div>
                    </h2>

                    <div id="collapse<?php echo $m_key; ?>" class="accordion-collapse collapse" data-bs-parent="#reportAccordion">
                        <div class="accordion-body p-0">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%;">วันที่ปฏิบัติงาน</th>
                                        <th style="width: 20%;">วันที่ส่งรายงาน</th>
                                        <th style="width: 55%;">รายละเอียดงาน & ปัญหา</th>
                                        <th style="width: 10%;" class="text-center no-print">รูปภาพ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $data): 
                                        $on_time = (date('Y-m-d', strtotime($data['reported_at'])) === $data['report_date']);
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?php echo dateThai($data['report_date']); ?></td>
                                        <td class="small">
                                            <?php echo dateThai($data['reported_at'], true); ?>
                                            <div class="mt-1 <?php echo $on_time ? 'text-success' : 'text-danger'; ?> fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi <?php echo $on_time ? 'bi-check-circle' : 'bi-clock-history'; ?>"></i>
                                                <?php echo $on_time ? 'ส่งตรงเวลา' : 'ส่งย้อนหลัง'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="job-detail"><?php echo nl2br(htmlspecialchars($data['job_details'])); ?></div>
                                            <?php if(!empty($data['problems'])): ?>
                                                <div class="prob-detail"><strong>ปัญหา:</strong> <?php echo htmlspecialchars($data['problems']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center no-print">
                                            <?php if(!empty($data['report_image'])): ?>
                                                <img src="../uploads/<?php echo $data['report_image']; ?>" 
                                                     class="img-preview" 
                                                     onclick="showLargeImage('../uploads/<?php echo $data['report_image']; ?>')">
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal สำหรับดูรูปใหญ่ -->
<div class="modal fade no-print" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 bg-transparent">
            <div class="modal-body p-0 text-center">
                <img src="" id="largeImage" class="img-fluid rounded shadow-lg" style="max-height: 90vh;">
                <button type="button" class="btn btn-light rounded-circle position-absolute top-0 end-0 m-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ฟังก์ชันสำหรับพิมพ์ (แก้ปัญหาตัวอักษรขาด)
function printDiv(divId) {
    const section = document.getElementById(divId);
    const collapse = section.querySelector('.accordion-collapse');
    
    // กางข้อมูลเดือนนั้นออกก่อนพิมพ์
    collapse.classList.add('show');

    setTimeout(() => {
        window.print();
        // ไม่ต้องรีโหลด แต่ให้คงสถานะเดิมไว้
    }, 500);
}

// ฟังก์ชันสำหรับแสดงรูปใหญ่
function showLargeImage(src) {
    document.getElementById('largeImage').src = src;
    var myModal = new bootstrap.Modal(document.getElementById('imageModal'));
    myModal.show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>