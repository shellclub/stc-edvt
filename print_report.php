<?php
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) { 
    header("location: index.php"); 
    exit(); 
}
$sid = $_SESSION['student_id'];

// 1. ดึงข้อมูลนักศึกษาและสถานที่ฝึกงาน
$sql_user = "SELECT s.*, p.* FROM students s 
             LEFT JOIN internship_places p ON s.place_id = p.place_id 
             WHERE s.student_id = '$sid'";
$query_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_array($query_user);

// 2. ตรวจสอบระดับชั้น
$sid_char3 = substr($sid, 2, 1);
$student_level = ($sid_char3 == '2') ? "ปวช." : (($sid_char3 == '3') ? "ปวส." : "");

// 3. ตั้งค่าวันฝึกงานจากข้อมูลที่กรอก
$work_days = [
    'Monday'    => 'จันทร์',
    'Tuesday'   => 'อังคาร',
    'Wednesday' => 'พุธ',
    'Thursday'  => 'พฤหัสบดี',
    'Friday'    => 'ศุกร์'
];
if (strpos($user['training_days'], 'เสาร์') !== false) {
    $work_days['Saturday'] = 'เสาร์';
}

// 4. ดึงข้อมูลรายงาน
$sql_report = "SELECT *, YEARWEEK(report_date, 1) as week_id FROM internship_reports 
               WHERE student_id = '$sid' ORDER BY report_date ASC";
$query_report = mysqli_query($conn, $sql_report);

$weeks = [];
while ($row = mysqli_fetch_assoc($query_report)) {
    $weeks[$row['week_id']][] = $row;
}

function dateThaiShort($strDate) {
    if (!$strDate) return "-";
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonthCut = ["", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
    return date("j", strtotime($strDate)) . " " . $strMonthCut[(int)date("n", strtotime($strDate))] . " " . $strYear;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานการฝึกงาน - <?php echo htmlspecialchars($user['fullname']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body { 
            font-family: 'Sarabun', sans-serif; 
            color: #000; 
            background-color: #525659;
            margin: 0;
            padding: 0;
            font-size: 13px;
            line-height: 1.45;
        }

        /* สำหรับการแสดงผลบนหน้าจอ */
        @media screen {
            .page-container { 
                width: 210mm; 
                min-height: 297mm; 
                padding: 15mm; 
                margin: 10mm auto; 
                background: white; 
                box-shadow: 0 0 10px rgba(0,0,0,0.5); 
            }
        }

        /* สำหรับการสั่งพิมพ์ (Print / Save PDF) */
        @media print {
            @page { 
                size: A4 portrait; 
                margin: 15mm 12mm 15mm 12mm; /* ให้ Margin เบราว์เซอร์คุมระยะ ป้องกันตัวหนังสือชนขอบ/ทับกัน */
            }
            body { 
                background: white !important; 
                margin: 0 !important; 
                padding: 0 !important; 
            }
            .page-container { 
                margin: 0 !important; 
                padding: 0 !important; 
                width: 100% !important; 
                min-height: auto !important; 
                box-shadow: none !important; 
                border: none !important;
                page-break-after: always;
                break-after: page;
            }
            .no-print { display: none !important; }
            
            /* กฎสำคัญ: ป้องกันแถวตารางขาดครึ่งตัวอักษร */
            tr { 
                page-break-inside: avoid !important; 
                break-inside: avoid !important; 
            }
            
            /* ให้หัวตารางวนซ้ำอัตโนมัติหากสัปดาห์นั้นยาวจนขึ้นหน้า 2 */
            thead { 
                display: table-header-group; 
            }
            
            /* ป้องกันลายเซ็นขาดวิ่น */
            .signature-block {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        /* สไตล์ตารางรายงาน */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #000 !important;
            padding: 6px 8px;
            vertical-align: top;
        }
        .table-custom th {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            text-align: center;
            font-weight: bold;
        }

        .logo { width: 3.2cm; margin-bottom: 0.8cm; }
        .info-title { font-weight: bold; width: 35%; background: #f8f9fa; }
        
        .cover-content { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            text-align: center; 
            min-height: 240mm;
            justify-content: space-between; 
            padding: 30mm 0 10mm 0;
        }

        .report-text {
            font-size: 12.5px;
            word-break: break-word;
            white-space: normal;
        }

        .img-report {
            max-width: 100px;
            max-height: 80px;
            object-fit: cover;
            border-radius: 3px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>

<div class="no-print text-center p-3">
    <button onclick="window.print()" class="btn btn-danger btn-lg px-5 shadow">สั่งพิมพ์รายงาน (PDF)</button>
    <a href="dashboard.php" class="btn btn-light btn-lg ms-2 border">กลับหน้าหลัก</a>
</div>

<!-- ================= หน้าที่ 1: หน้าปก ================= -->
<div class="page-container">
    <div class="cover-content">
        <div>
            <img src="image/icon_stc.jpg" class="logo" onerror="this.style.display='none'">
            <h2 class="fw-bold" style="font-size: 22pt;">วิทยาลัยเทคนิคสุพรรณบุรี</h2>
            <h3 class="mt-3 fw-bold" style="font-size: 18pt;">รายงานการฝึกประสบการณ์วิชาชีพ</h3>
        </div>
        
        <div style="font-size: 16pt; line-height: 2.2;">
            โดย<br>
            <strong><?php echo htmlspecialchars($user['fullname']); ?></strong><br>
            รหัสนักศึกษา <?php echo htmlspecialchars($user['student_id']); ?><br>
            ระดับชั้น <?php echo $student_level; ?> กลุ่ม <?php echo htmlspecialchars($user['group_name']); ?>
        </div>
        
        <div class="fw-bold" style="font-size: 15pt;">
            ภาคเรียนที่ 1 ปีการศึกษา 2569
        </div>
    </div>
</div>

<!-- ================= หน้าที่ 2: ข้อมูลส่วนตัว ================= -->
<div class="page-container">
    <h4 class="text-center fw-bold mb-4" style="font-size: 16pt;">ข้อมูลนักศึกษาและรายละเอียดการฝึกงาน</h4>
    <div class="text-center mb-4">
        <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $user['student_id']; ?>.jpg&x=150&f=0" class="rounded border" width="120" onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';">
    </div>

    <table class="table-custom">
        <tr><th colspan="2">ข้อมูลส่วนตัว</th></tr>
        <tr><td class="info-title">ชื่อ-นามสกุล</td><td><?php echo htmlspecialchars($user['fullname']); ?></td></tr>
        <tr><td class="info-title">รหัสนักศึกษา</td><td><?php echo htmlspecialchars($user['student_id']); ?></td></tr>
        <tr><td class="info-title">ระดับชั้น</td><td><?php echo $student_level; ?></td></tr>
        
        <tr><th colspan="2">ข้อมูลสถานประกอบการ</th></tr>
        <tr><td class="info-title">ชื่อสถานประกอบการ</td><td><?php echo htmlspecialchars($user['company_name'] ?: '-'); ?></td></tr>
        <tr><td class="info-title">ชื่อครูฝึก</td><td><?php echo htmlspecialchars($user['mentor_name'] ?: '-'); ?></td></tr>
        <tr><td class="info-title">เบอร์โทรติดต่อ</td><td><?php echo htmlspecialchars($user['company_phone'] ?: '-'); ?></td></tr>
        <tr><td class="info-title">วันที่ฝึกงานต่อสัปดาห์</td><td><?php echo htmlspecialchars($user['training_days'] ?: '-'); ?></td></tr>
        <tr><td class="info-title">ที่อยู่สถานประกอบการ</td><td><?php echo htmlspecialchars($user['company_address'] ?: '-'); ?></td></tr>
    </table>
</div>

<!-- ================= รายงานรายสัปดาห์ (1 สัปดาห์ = 1 ชุดข้อมูล มีลายเซ็นท้ายสัปดาห์) ================= -->
<?php 
$week_num = 1;
foreach ($weeks as $week_id => $days): 
    $data_in_week = [];
    foreach ($days as $d) {
        $day_name = date('l', strtotime($d['report_date']));
        $data_in_week[$day_name] = $d;
    }
?>
<div class="page-container">
    <h5 class="fw-bold mb-3" style="font-size: 14pt;">บันทึกรายงานการฝึกงานประจำสัปดาห์ที่ <?php echo $week_num; ?></h5>
    
    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 17%;">วัน / วันที่</th>
                <th style="width: 48%;">รายละเอียดงานที่ปฏิบัติ</th>
                <th style="width: 17%;">ปัญหา/อุปสรรค</th>
                <th style="width: 18%;">รูปภาพ</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            foreach ($work_days as $eng => $th): 
                $row = isset($data_in_week[$eng]) ? $data_in_week[$eng] : null;
            ?>
            <tr>
                <td style="text-align: center;">
                    <strong>วัน<?php echo $th; ?></strong><br>
                    <small style="font-size: 11px; color: #444;"><?php echo $row ? dateThaiShort($row['report_date']) : '-'; ?></small>
                </td>
                <td class="report-text">
                    <?php echo ($row && !empty(trim($row['job_details']))) ? nl2br(htmlspecialchars($row['job_details'])) : '<span class="text-muted">- ไม่มีบันทึกงาน -</span>'; ?>
                </td>
                <td class="report-text text-danger" style="font-size: 11px;">
                    <?php echo ($row && !empty(trim($row['problems']))) ? nl2br(htmlspecialchars($row['problems'])) : '<span class="text-muted">-</span>'; ?>
                </td>
                <td style="text-align: center; vertical-align: middle;">
                    <?php if ($row && !empty($row['report_image'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['report_image']); ?>" class="img-report">
                    <?php else: ?>
                        <span class="text-muted small">-</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- ส่วนเซ็นชื่อ 1 จุด ต่อ 1 สัปดาห์ (อยู่ท้ายสุดของตารางสัปดาห์นั้น) -->
    <div class="signature-block mt-4 pt-2" style="font-size: 13px;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; border: none !important;">
                    ......................................................<br>
                    ( <?php echo htmlspecialchars($user['fullname']); ?> )<br>
                    นักศึกษา
                </td>
                <td style="width: 50%; text-align: center; border: none !important;">
                    ......................................................<br>
                    ( <?php echo htmlspecialchars($user['mentor_name'] ?: '......................................................'); ?> )<br>
                    ครูฝึก/ผู้ควบคุม
                </td>
            </tr>
        </table>
    </div>
</div>
<?php 
    $week_num++;
endforeach; 
?>

</body>
</html>