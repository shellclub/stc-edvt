<?php
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) { header("location: index.php"); exit(); }
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

// 3. ตั้งค่าวันฝึกงานจากข้อมูลที่กรอก (เช็คว่ามีคำว่า 'เสาร์' หรือไม่)
$work_days = ['Monday'=>'จันทร์', 'Tuesday'=>'อังคาร', 'Wednesday'=>'พุธ', 'Thursday'=>'พฤหัสบดี', 'Friday'=>'ศุกร์'];
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
    if(!$strDate) return "-";
    $strYear = date("Y", strtotime($strDate)) + 543;
    $strMonthCut = Array("", "ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
    return date("j", strtotime($strDate))." ".$strMonthCut[date("n", strtotime($strDate))]." ".$strYear;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานการฝึกงาน - <?php echo $user['fullname']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style>
        @media screen {
            body { background-color: #525659; margin: 0; }
            .page { 
                width: 210mm; min-height: 297mm; padding: 15mm; 
                margin: 10mm auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.5); 
            }
        }
        
        body { font-family: 'Sarabun', sans-serif; color: #000; }
        .page { box-sizing: border-box; position: relative; }
        
        /* ตั้งค่าแยกหน้าสำหรับ Print */
        @media print {
            @page { size: A4; margin: 0; }
            body { background: none; margin: 0; padding: 0; }
            .page { 
                margin: 0; width: 210mm; height: 297mm; 
                padding: 15mm; page-break-after: always; 
                box-shadow: none; border: none;
            }
            .no-print { display: none !important; }
        }

        .table-bordered th, .table-bordered td { border: 1px solid black !important; padding: 5px; vertical-align: middle; font-size: 14px; }
        .logo { width: 3.5cm; margin-bottom: 1cm; }
        .info-title { font-weight: bold; width: 35%; background: #f8f9fa; }
        .cover-page { display: flex; flex-direction: column; align-items: center; text-align: center; height: 100%; justify-content: center; }
    </style>
</head>
<body>

<div class="no-print text-center p-3">
    <button onclick="window.print()" class="btn btn-danger btn-lg px-5 shadow">สั่งพิมพ์รายงาน (PDF)</button>
    <a href="dashboard.php" class="btn btn-light btn-lg ms-2 border">กลับหน้าหลัก</a>
</div>

<!-- หน้าที่ 1: หน้าปก -->
<div class="page">
    <div class="cover-page">
        <img src="image/icon_stc.jpg" class="logo">
        <h2 class="fw-bold">วิทยาลัยเทคนิคสุพรรณบุรี</h2>
        <h3 class="mt-4 fw-bold">รายงานการฝึกประสบการณ์วิชาชีพ</h3>
        <div class="mt-5" style="font-size: 18pt; line-height: 2.5;">
            โดย<br>
            <strong><?php echo $user['fullname']; ?></strong><br>
            รหัสนักศึกษา <?php echo $user['student_id']; ?><br>
            ระดับชั้น <?php echo $student_level; ?> กลุ่ม <?php echo $user['group_name']; ?>
        </div>
        <div class="mt-auto fw-bold" style="font-size: 16pt; margin-bottom: 2cm;">ภาคเรียนที่ 1 ปีการศึกษา 2569</div>
    </div>
</div>

<!-- หน้าที่ 2: ข้อมูลส่วนตัว -->
<div class="page">
    <h4 class="text-center fw-bold mb-4">ข้อมูลนักศึกษาและรายละเอียดการฝึกงาน</h4>
    <div class="text-center mb-4">
        <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $user['student_id']; ?>.jpg&x=150&f=0" class="rounded border" width="120" onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';">
    </div>

    <table class="table table-bordered w-100">
        <tr><th colspan="2" class="bg-light text-center">ข้อมูลส่วนตัว</th></tr>
        <tr><td class="info-title">ชื่อ-นามสกุล</td><td><?php echo $user['fullname']; ?></td></tr>
        <tr><td class="info-title">รหัสนักศึกษา</td><td><?php echo $user['student_id']; ?></td></tr>
        <tr><td class="info-title">ระดับชั้น</td><td><?php echo $student_level; ?></td></tr>
        
        <tr><th colspan="2" class="bg-light text-center">ข้อมูลสถานประกอบการ</th></tr>
        <tr><td class="info-title">ชื่อสถานประกอบการ</td><td><?php echo $user['company_name'] ?: '-'; ?></td></tr>
        <tr><td class="info-title">ชื่อครูฝึก </td><td><?php echo $user['mentor_name'] ?: '-'; ?></td></tr>
        <tr><td class="info-title">เบอร์โทรติดต่อ</td><td><?php echo $user['company_phone'] ?: '-'; ?></td></tr>
        <tr><td class="info-title">วันที่ฝึกงานต่อสัปดาห์</td><td><?php echo $user['training_days'] ?: '-'; ?></td></tr>
        <tr><td class="info-title">ที่อยู่สถานประกอบการ</td><td><?php echo $user['company_address'] ?: '-'; ?></td></tr>
    </table>
</div>

<!-- รายงานรายสัปดาห์ -->
<?php foreach ($weeks as $week_id => $days): ?>
<div class="page">
    <h5 class="fw-bold mb-3 text-center">บันทึกรายงานการฝึกงานประจำสัปดาห์</h5>
    
    <table class="table table-bordered">
        <thead class="text-center bg-light">
            <tr>
                <th width="15%">วัน / วันที่</th>
                <th width="45%">รายละเอียดงานที่ปฏิบัติ</th>
                <th width="15%">ปัญหา/อุปสรรค</th>
                <th width="25%">รูปภาพ</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $data_in_week = [];
            foreach($days as $d) {
                $day_name = date('l', strtotime($d['report_date']));
                $data_in_week[$day_name] = $d;
            }

            foreach ($work_days as $eng => $th): 
                $row = isset($data_in_week[$eng]) ? $data_in_week[$eng] : null;
            ?>
            <tr>
                <td class="text-center">
                    <strong><?php echo $th; ?></strong><br>
                    <small style="font-size: 11px;"><?php echo $row ? dateThaiShort($row['report_date']) : '-'; ?></small>
                </td>
                <td style="height: 90px; font-size: 13px;">
                    <?php echo $row ? nl2br(htmlspecialchars($row['job_details'])) : '<span class="text-muted small">ไม่มีบันทึกงาน</span>'; ?>
                </td>
                <td class="small text-danger" style="font-size: 11px;">
                    <?php echo ($row && $row['problems']) ? htmlspecialchars($row['problems']) : '-'; ?>
                </td>
                <td class="text-center">
                    <?php if($row && $row['report_image']): ?>
                        <img src="uploads/<?php echo $row['report_image']; ?>" style="max-width: 120px; max-height: 85px; object-fit: cover; border-radius: 4px;">
                    <?php else: ?>
                        <small class="text-muted">-</small>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="row mt-4 text-center" style="font-size: 14px;">
        <div class="col-6"><br>......................................................<br>( <?php echo $user['fullname']; ?> )<br>นักศึกษา</div>
        <div class="col-6"><br>......................................................<br>( <?php echo $user['mentor_name']; ?> )<br>ครูฝึก/ผู้ควบคุม</div>
    </div>
</div>
<?php endforeach; ?>

</body>
</html>