<?php
session_start();
include "../config.php";

$sid = isset($_GET['sid']) ? mysqli_real_escape_string($conn, $_GET['sid']) : '';

if (empty($sid)) {
    die("<div class='container mt-5 alert alert-danger'>ไม่พบข้อมูลนักศึกษา</div>");
}

// 1. ดึงข้อมูลนักศึกษา JOIN กับตารางสถานประกอบการ
$sql_std = "SELECT s.*, p.company_name, p.mentor_name, p.company_address, 
                   p.company_phone, p.training_days, p.workplace_lat, p.workplace_lng 
            FROM students s
            LEFT JOIN internship_places p ON s.place_id = p.place_id 
            WHERE s.student_id = '$sid'";

$res_std = mysqli_query($conn, $sql_std);
$std = mysqli_fetch_assoc($res_std);

// 2. ดึงรายงาน
$sql_report = "SELECT * FROM internship_reports WHERE student_id = '$sid' ORDER BY report_date ASC";
$res_report = mysqli_query($conn, $sql_report);

$monthly_reports = [];
while($row = mysqli_fetch_assoc($res_report)) {
    $month_key = date("Y-m", strtotime($row['report_date']));
    $monthly_reports[$month_key][] = $row;
}

function get_val($data, $key, $default = "-") {
    return (isset($data[$key]) && !empty($data[$key])) ? $data[$key] : $default;
}

function dateThai($strDate, $withTime = false) {
    if (!$strDate || $strDate == "0000-00-00 00:00:00") return "-";
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonthCut = Array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
    $time = $withTime ? " (".date("H:i", strtotime($strDate))." น.)" : "";
    return date("j", strtotime($strDate)) . " " . $strMonthCut[date("n", strtotime($strDate))] . " " . $strYear . $time;
}

function monthThaiName($monthKey) {
    $ex = explode('-', $monthKey);
    $months = ["", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"];
    return $months[(int)$ex[1]] . " " . ($ex[0] + 543);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report: <?php echo get_val($std, 'fullname'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #06d6a0;
            --danger: #ef476f;
            --bg-body: #f4f7ff;
            --glass: rgba(255, 255, 255, 0.95);
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg-body);
            color: #2b2d42;
        }

        .kanit { font-family: 'Kanit', sans-serif; }

        /* Header Card */
        .profile-section {
            background: linear-gradient(135deg, #1a237e 0%, #4361ee 100%);
            padding: 40px 0 100px;
            color: white;
            border-radius: 0 0 50px 50px;
            margin-bottom: -60px;
        }

        .info-card {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border: none;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.05);
            padding: 35px;
            margin-bottom: 30px;
        }

        .std-photo-big {
            width: 130px;
            height: 160px;
            object-fit: cover;
            border-radius: 20px;
            border: 5px solid white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .btn-back-nav {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            text-decoration: none;
            transition: 0.3s;
            font-size: 0.9rem;
        }

        .btn-back-nav:hover {
            background: white;
            color: var(--primary);
        }

        /* --- Print Settings --- */
        @media print {
            @page { size: A4; margin: 1cm; }
            .no-print { display: none !important; }
            body { background: white !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            .profile-section { background: #1a237e !important; padding: 20px 0 !important; border-radius: 0 !important; margin-bottom: 20px !important; }
            .info-card { box-shadow: none !important; border: 1px solid #ddd !important; margin-top: 0 !important; padding: 20px !important; }
            .accordion-collapse { display: block !important; visibility: visible !important; }
            .accordion-item { border: 1px solid #eee !important; margin-bottom: 20px !important; page-break-inside: avoid; }
            .img-print-visible { 
                display: block !important; 
                width: 120px !important; 
                height: auto !important; 
                visibility: visible !important; 
                opacity: 1 !important;
                margin: 5px auto;
            }
            .text-success { color: #06d6a0 !important; }
            .text-danger { color: #ef476f !important; }
        }

        .company-badge {
            background: #f0f3ff;
            border-left: 5px solid var(--primary);
            padding: 15px 20px;
            border-radius: 15px;
        }

        .accordion-item {
            border: none;
            margin-bottom: 15px;
            border-radius: 20px !important;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.03);
        }

        .accordion-button:not(.collapsed) {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>

<div class="profile-section no-print">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="teacher_student_list.php?gname=<?php echo urlencode($std['group_name']); ?>" class="btn-back-nav">
            <i class="bi bi-chevron-left"></i> ย้อนกลับไปเลือกนักศึกษา
        </a>
        <div class="text-center flex-grow-1 me-5">
            <h2 class="kanit fw-bold mb-0">รายงานสรุปผลการปฏิบัติงาน</h2>
            <p class="opacity-75 mb-0">วิทยาลัยเทคนิคสุพรรณบุรี</p>
        </div>
    </div>
</div>

<div class="container mt-4 mt-md-0">
    <div class="card info-card">
        <div class="row align-items-center">
            <div class="col-lg-2 col-md-3 text-center mb-4 mb-md-0">
                <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $sid; ?>.jpg&x=200&f=0" 
                     class="std-photo-big" alt="Student">
            </div>
            <div class="col-lg-5 col-md-9 border-end pe-lg-5">
                <span class="badge bg-primary rounded-pill mb-2 px-3 py-2 kanit"><?php echo get_val($std, 'student_id'); ?></span>
                <h2 class="fw-bold kanit mb-1"><?php echo get_val($std, 'fullname'); ?></h2>
                <p class="text-muted"><i class="bi bi-people me-1"></i> กลุ่ม: <?php echo get_val($std, 'group_name'); ?></p>
                
                <div class="mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-dark rounded-pill px-4 shadow-sm">
                        <i class="bi bi-printer me-2"></i> พิมพ์รายงาน
                    </button>
                </div>
            </div>
            <div class="col-lg-5 col-md-12 mt-4 mt-lg-0 ps-lg-5">
                <div class="company-badge h-100">
                    <h6 class="fw-bold kanit text-primary"><i class="bi bi-building-check me-2"></i>สถานประกอบการ</h6>
                    <div class="fw-bold mb-1"><?php echo get_val($std, 'company_name', 'ไม่ระบุชื่อบริษัท'); ?></div>
                    <div class="small text-muted mb-2"><?php echo get_val($std, 'company_address'); ?></div>
                    <div class="row small g-2">
                        <div class="col-6"><strong>ครูฝึก:</strong> <?php echo get_val($std, 'mentor_name'); ?></div>
                        <div class="col-6"><strong>โทร:</strong> <?php echo get_val($std, 'company_phone'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Report List -->
    <div class="accordion mb-5 shadow-sm" id="reportAcc">
        <?php if(!empty($monthly_reports)): ?>
            <?php foreach($monthly_reports as $m_key => $reports): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header no-print">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c<?php echo $m_key; ?>">
                            <i class="bi bi-calendar-check me-3 fs-5"></i> 
                            เดือน <?php echo monthThaiName($m_key); ?>
                            <span class="badge bg-light text-primary rounded-pill ms-auto me-3 px-3">
                                ส่งแล้ว <?php echo count($reports); ?> วัน
                            </span>
                        </button>
                    </h2>
                    <div id="c<?php echo $m_key; ?>" class="accordion-collapse collapse">
                        <div class="accordion-body p-0">
                            <table class="table mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 15%;">วันที่</th>
                                        <th style="width: 20%;">สถานะบันทึก</th>
                                        <th>รายละเอียดงาน</th>
                                        <th class="text-center" style="width: 15%;">รูปหลักฐาน</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($reports as $r): 
                                        $on_time = (date('Y-m-d', strtotime($r['reported_at'])) === $r['report_date']);
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?php echo dateThai($r['report_date']); ?></td>
                                        <td>
                                            <span class="fw-bold <?php echo $on_time ? 'text-success' : 'text-danger'; ?>" style="font-size: 0.85rem;">
                                                <i class="bi <?php echo $on_time ? 'bi-check-circle-fill' : 'bi-clock-history'; ?> me-1"></i>
                                                <?php echo $on_time ? 'ตรงเวลา' : 'ส่งย้อนหลัง'; ?>
                                            </span>
                                            <div class="text-muted" style="font-size: 0.7rem;">
                                                บันทึก: <?php echo date('H:i', strtotime($r['reported_at'])); ?> น.
                                            </div>
                                        </td>
                                        <td class="py-3">
                                            <div style="white-space: pre-wrap; font-size: 0.9rem;"><?php echo nl2br(htmlspecialchars($r['job_details'])); ?></div>
                                            <?php if($r['problems']): ?>
                                                <div class="mt-2 text-danger small"><strong>ปัญหา:</strong> <?php echo htmlspecialchars($r['problems']); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if($r['report_image']): ?>
                                                <img src="../uploads/<?php echo $r['report_image']; ?>" 
                                                     class="img-fluid rounded shadow-sm img-print-visible" 
                                                     style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;" 
                                                     onclick="viewImg(this.src)">
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

<!-- Modal -->
<div class="modal fade no-print" id="vModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0 text-center">
            <img src="" id="fImg" class="img-fluid rounded shadow-lg">
        </div>
    </div>
</div>

<script>
function viewImg(s){ document.getElementById('fImg').src=s; new bootstrap.Modal(document.getElementById('vModal')).show(); }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>