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
        // ใช้ปีและเดือนเป็น Key เพื่อแยกกลุ่ม
        $month_key = date("Y-m", strtotime($row['report_date']));
        $monthly_reports[$month_key][] = $row;
    }
}

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
    <title>รายงาน: <?php echo $std['fullname']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --primary: #1a237e; --success: #198754; --danger: #dc3545; }
        body { font-family: 'Sarabun', sans-serif; background: #f8f9fa; }
        .profile-header { background: var(--primary); padding: 30px 0 60px; color: white; border-radius: 0 0 30px 30px; }
        .std-img { width: 90px; height: 110px; object-fit: cover; border-radius: 10px; border: 3px solid white; }
        .accordion-item { border: none; margin-bottom: 15px; border-radius: 15px !important; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; }
        .job-detail { font-size: 0.95rem; white-space: pre-wrap; word-break: break-word; color: #1e293b; }
        .img-preview { width: 60px; height: 60px; object-fit: cover; cursor: pointer; border: 1px solid #ddd; border-radius: 5px; }

        /* --- ปรับปรุงสำหรับการพิมพ์: แก้ไขข้อมูลหาย --- */
        @media print {
            @page { size: A4; margin: 1cm; }
            body { 
                background: white !important; 
                -webkit-print-color-adjust: exact !important; 
                print-color-adjust: exact !important; 
            }
            .no-print { display: none !important; }
            
            /* บังคับให้ Accordion กางออกทั้งหมดตอนพิมพ์ */
            .accordion-collapse { 
                display: block !important; 
                height: auto !important; 
                visibility: visible !important;
                overflow: visible !important;
            }
            .accordion-button::after { display: none !important; }
            .accordion-item { 
                border: 1px solid #eee !important; 
                box-shadow: none !important; 
                margin-bottom: 20px; 
                page-break-inside: avoid; /* ป้องกันตารางขาดกลางหน้า */
            }
            
            .print-header { display: block !important; text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
            .table { width: 100% !important; border-collapse: collapse !important; }
            .table th { background-color: #f8f9fa !important; border: 1px solid #dee2e6 !important; -webkit-print-color-adjust: exact; }
            .table td { border: 1px solid #dee2e6 !important; padding: 10px !important; }
            
            .text-success { color: #198754 !important; }
            .text-danger { color: #dc3545 !important; }
            img { max-width: 80px !important; visibility: visible !important; display: block !important; }
        }
        .print-header { display: none; }
    </style>
</head>
<body>

<div class="profile-header no-print">
    <div class="container d-md-flex align-items-center">
        <img src="<?php echo $student_img_url; ?>" class="std-img mx-auto mb-3 mb-md-0 me-md-4 shadow" alt="Student">
        <div>
            <h4 class="fw-bold mb-1"><?php echo $std['fullname']; ?></h4>
            <p class="mb-0 opacity-75">รหัส: <?php echo $std['student_id']; ?> | กลุ่ม: <?php echo $std['group_name']; ?></p>
        </div>
        <div class="ms-auto mt-3 mt-md-0">
            <a href="bilateral_student_list.php?gname=<?php echo urlencode($std['group_name']); ?>" class="btn btn-light btn-sm rounded-pill px-4">ย้อนกลับ</a>
        </div>
    </div>
</div>

<div class="container mt-n4" style="margin-top: -30px;">
    <div class="accordion" id="reportAccordion">
        <?php if (!empty($monthly_reports)): ?>
            <?php foreach ($monthly_reports as $m_key => $reports): ?>
                <div class="accordion-item" id="print-area-<?php echo $m_key; ?>">
                    
                    <div class="print-header">
                        <h4 class="fw-bold">รายงานผลการปฏิบัติงานประจำเดือน <?php echo monthThaiName($m_key); ?></h4>
                        <p>ชื่อ-นามสกุล: <?php echo $std['fullname']; ?> | รหัส: <?php echo $std['student_id']; ?> | กลุ่ม: <?php echo $std['group_name']; ?></p>
                    </div>

                    <h2 class="accordion-header no-print">
                        <div class="accordion-button collapsed d-flex justify-content-between align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $m_key; ?>">
                            <span><i class="bi bi-calendar3 me-2 text-primary"></i> เดือน <?php echo monthThaiName($m_key); ?></span>
                            <div class="ms-auto d-flex align-items-center">
                                <span class="badge bg-primary rounded-pill me-3"><?php echo count($reports); ?> วัน</span>
                                <button onclick="triggerPrint('print-area-<?php echo $m_key; ?>', 'collapse<?php echo $m_key; ?>')" class="btn btn-sm btn-dark rounded-pill py-1 px-3">
                                    <i class="bi bi-printer"></i> พิมพ์เดือนนี้
                                </button>
                            </div>
                        </div>
                    </h2>

                    <div id="collapse<?php echo $m_key; ?>" class="accordion-collapse collapse" data-bs-parent="#reportAccordion">
                        <div class="accordion-body p-0">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 15%; text-align: center;">วันที่ฝึกงาน</th>
                                        <th style="width: 25%;">สถานะการส่ง</th>
                                        <th>รายละเอียดงาน</th>
                                        <th style="width: 15%; text-align: center;">รูปภาพ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $data): 
                                        $is_on_time = (date('Y-m-d', strtotime($data['reported_at'])) === $data['report_date']);
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold small"><?php echo dateThai($data['report_date']); ?></td>
                                        <td>
                                            <div class="small">
                                                <i class="bi <?php echo $is_on_time ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'; ?>"></i>
                                                <span class="<?php echo $is_on_time ? 'text-success' : 'text-danger'; ?> fw-bold">
                                                    <?php echo $is_on_time ? 'ตรงวัน' : 'ย้อนหลัง'; ?>
                                                </span>
                                                <div class="text-muted mt-1" style="font-size: 0.7rem;">
                                                    บันทึก: <?php echo dateThai($data['created_at'], true); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="job-detail"><?php echo nl2br(htmlspecialchars($data['job_details'])); ?></div>
                                            <?php if(!empty($data['problems'])): ?>
                                                <div class="text-danger small mt-1"><strong>ปัญหา:</strong> <?php echo htmlspecialchars($data['problems']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if(!empty($data['report_image'])): ?>
                                                <img src="../uploads/<?php echo $data['report_image']; ?>" 
                                                     class="img-preview" 
                                                     onclick="zoomImg('../uploads/<?php echo $data['report_image']; ?>')">
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

<div class="modal fade no-print" id="zoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 text-end">
            <button type="button" class="btn-close btn-close-white mb-2" data-bs-dismiss="modal"></button>
            <img src="" id="zoomImg" class="img-fluid rounded shadow-lg">
        </div>
    </div>
</div>

<script>
function triggerPrint(areaId, collapseId) {
    // 1. สั่งให้ Accordion ของเดือนนั้นกางออกก่อน
    const collapseElement = document.getElementById(collapseId);
    const bsCollapse = new bootstrap.Collapse(collapseElement, { toggle: false });
    bsCollapse.show();

    // 2. รอให้ Animation การกางออกเสร็จสิ้น (ประมาณ 350-500ms) แล้วค่อยสั่งพิมพ์
    setTimeout(() => {
        window.print();
    }, 500);
}

function zoomImg(src) {
    document.getElementById('zoomImg').src = src;
    new bootstrap.Modal(document.getElementById('zoomModal')).show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>