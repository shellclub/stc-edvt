<?php
session_start();
include "../config.php";

// ตรวจสอบสิทธิ์ Admin ทวิภาคี[cite: 2]
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

// -------------------------------------------------------------
// 1. ฟังก์ชันส่งออกเป็น EXCEL (.xls)
// -------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'excel') {
    $filename = "สรุปสถานประกอบการฝึกงาน_" . date('Ymd_His') . ".xls";
    
    // ตั้งค่า Header สำหรับดาวน์โหลดไฟล์ Excel รองรับภาษาไทย UTF-8
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");

    // ใส่ BOM ป้องกันภาษาไทยเพี้ยนใน MS Excel
    echo "\xEF\xBB\xBF";

    // ดึงข้อมูลสถานประกอบการที่รวมกลุ่มแล้ว[cite: 2]
    $sql_excel = "
        SELECT 
            p.company_name,
            p.mentor_name,
            p.company_phone,
            p.company_address,
            p.training_days,
            COUNT(s.student_id) AS total_std,
            GROUP_CONCAT(CONCAT(s.student_id, ' ', s.fullname, ' (', s.group_name, ')') SEPARATOR '\n') AS student_list
        FROM internship_places p
        INNER JOIN students s ON p.place_id = s.place_id
        WHERE p.company_name IS NOT NULL AND TRIM(p.company_name) != ''
        GROUP BY p.company_name, p.mentor_name, p.company_address, p.company_phone, p.training_days
        ORDER BY total_std DESC, p.company_name ASC
    ";
    $query_excel = mysqli_query($conn, $sql_excel);

    echo '<table border="1">';
    echo '<thead>';
    echo '<tr style="background-color: #d9edf7; font-weight: bold;">';
    echo '<th>ลำดับ</th>';
    echo '<th>ชื่อสถานประกอบการ</th>';
    echo '<th>ครูฝึก</th>';
    echo '<th>เบอร์โทร</th>';
    echo '<th>ที่อยู่</th>';
    echo '<th>วันที่ฝึกงาน</th>';
    echo '<th>จำนวน (คน)</th>';
    echo '<th>รายชื่อนักศึกษาที่ฝึกงาน</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $no = 1;
    $sum_students = 0;
    while ($row = mysqli_fetch_assoc($query_excel)) {
        $sum_students += $row['total_std'];
        echo '<tr>';
        echo '<td align="center">' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($row['company_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['mentor_name'] ?: '-') . '</td>';
        echo '<td style="mso-number-format:\'\@\';">' . htmlspecialchars($row['company_phone'] ?: '-') . '</td>';
        echo '<td>' . htmlspecialchars($row['company_address'] ?: '-') . '</td>';
        echo '<td>' . htmlspecialchars($row['training_days'] ?: '-') . '</td>';
        echo '<td align="center">' . $row['total_std'] . '</td>';
        echo '<td style="white-space: pre-line;">' . htmlspecialchars($row['student_list']) . '</td>';
        echo '</tr>';
    }
    echo '<tr style="background-color: #f5f5f5; font-weight: bold;">';
    echo '<td colspan="6" align="right">รวมนักศึกษาทั้งหมด:</td>';
    echo '<td align="center">' . $sum_students . '</td>';
    echo '<td>คน</td>';
    echo '</tr>';
    echo '</tbody>';
    echo '</table>';
    exit();
}

// -------------------------------------------------------------
// 2. ดึงข้อมูลสำหรับแสดงผลหน้าเว็บ & สั่งพิมพ์เป็น PDF (A4 Landscape)
// -------------------------------------------------------------
$sql_summary = "
    SELECT 
        p.company_name,
        p.company_address,
        p.company_phone,
        p.mentor_name,
        p.training_days,
        COUNT(s.student_id) AS total_students,
        GROUP_CONCAT(CONCAT('• ', s.fullname, ' (', s.group_name, ')') ORDER BY s.student_id SEPARATOR '<br>') AS student_list
    FROM internship_places p
    INNER JOIN students s ON p.place_id = s.place_id
    WHERE p.company_name IS NOT NULL AND TRIM(p.company_name) != ''
    GROUP BY p.company_name, p.company_address, p.mentor_name, p.company_phone, p.training_days
    ORDER BY total_students DESC, p.company_name ASC
";
$query_summary = mysqli_query($conn, $sql_summary);
$total_places = mysqli_num_rows($query_summary);
$total_students_all = 0;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์รายงานสรุปสถานประกอบการ (PDF/Excel) | DVT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Sarabun', sans-serif; background-color: #525659; color: #000; margin: 0; }
        
        /* มุมมองแสดงผลบนเบราว์เซอร์ */
        .page-sheet {
            background: white;
            width: 297mm; /* หน้ากว้าง A4 แนวนอน */
            min-height: 210mm;
            padding: 15mm 15mm;
            margin: 20px auto;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table-custom th, .table-custom td {
            border: 1px solid #000 !important;
            padding: 6px 8px;
            font-size: 12.5px;
            vertical-align: top;
        }
        .table-custom th {
            background-color: #f2f2f2 !important;
            text-align: center;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* รูปแบบเมื่อสั่งพิมพ์เป็น PDF (Print View) */
        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm 10mm;
            }
            body { background: none !important; }
            .no-print { display: none !important; }
            .page-sheet {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                min-height: auto !important;
                box-shadow: none !important;
                border: none !important;
            }
            tr { page-break-inside: avoid !important; break-inside: avoid !important; }
            thead { display: table-header-group; }
            .signature-box { page-break-inside: avoid !important; break-inside: avoid !important; }
        }
    </style>
</head>
<body>

<!-- แถบควบคุมด้านบนสำหรับดาวน์โหลด Excel และสั่งพิมพ์ PDF -->
<div class="no-print bg-white border-bottom shadow-sm p-3 sticky-top">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <a href="company_list.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> ย้อนกลับ
            </a>
            <span class="fw-bold fs-5 text-dark">ศูนย์ส่งออกและพิมพ์รายงานสรุปสถานประกอบการ</span>
        </div>
        <div class="d-flex gap-2">
            <!-- ปุ่มส่งออก Excel -->
            <a href="export_company_summary.php?action=excel" class="btn btn-success shadow-sm">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> ส่งออกเป็น Excel (.xls)
            </a>
            <!-- ปุ่มสั่งพิมพ์/บันทึก PDF -->
            <button onclick="window.print()" class="btn btn-danger shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> สั่งพิมพ์รายงาน / บันทึก PDF
            </button>
        </div>
    </div>
</div>

<!-- พื้นที่กระดาษแสดงรายงานสรุป -->
<div class="page-sheet">
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">รายงานสรุปรายชื่อสถานประกอบการฝึกประสบการณ์วิชาชีพ</h4>
        <h5 class="fw-bold mb-1">วิทยาลัยเทคนิคสุพรรณบุรี</h5>
        <div class="text-muted small">
            ข้อมูล ณ วันที่ <?php echo date('d/m/') . (date('Y') + 543); ?> | จำนวนสถานประกอบการทั้งหมด: <b><?php echo number_format($total_places); ?></b> แห่ง
        </div>
    </div>

    <table class="table-custom">
        <thead>
            <tr>
                <th style="width: 4%;">ลำดับ</th>
                <th style="width: 22%;">ชื่อสถานประกอบการ</th>
                <th style="width: 24%;">ที่อยู่ / เบอร์โทรศัพท์</th>
                <th style="width: 16%;">ชื่อครูฝึก / วันที่ฝึก</th>
                <th style="width: 8%;">จำนวน</th>
                <th style="width: 26%;">รายชื่อนักศึกษา (กลุ่มการเรียน)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $i = 1;
            if ($total_places > 0):
                while ($row = mysqli_fetch_assoc($query_summary)): 
                    $total_students_all += $row['total_students'];
            ?>
            <tr>
                <td class="text-center"><?php echo $i++; ?></td>
                <td>
                    <strong><?php echo htmlspecialchars($row['company_name']); ?></strong>
                </td>
                <td>
                    <?php echo nl2br(htmlspecialchars($row['company_address'] ?: '-')); ?><br>
                    <small class="text-muted">โทร: <?php echo htmlspecialchars($row['company_phone'] ?: '-'); ?></small>
                </td>
                <td>
                    <strong><?php echo htmlspecialchars($row['mentor_name'] ?: '-'); ?></strong><br>
                    <small class="text-muted">วันฝึก: <?php echo htmlspecialchars($row['training_days'] ?: '-'); ?></small>
                </td>
                <td class="text-center fw-bold fs-6">
                    <?php echo $row['total_students']; ?>
                </td>
                <td style="line-height: 1.45;">
                    <?php echo $row['student_list']; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            <tr style="background-color: #f8f9fa; font-weight: bold;">
                <td colspan="4" class="text-end pe-3">รวมจำนวนนักศึกษาที่ออกฝึกงานทั้งหมด:</td>
                <td class="text-center text-primary fs-6"><?php echo number_format($total_students_all); ?></td>
                <td>คน</td>
            </tr>
            <?php else: ?>
            <tr>
                <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูลสถานประกอบการในระบบ</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ส่วนเซ็นชื่อกำกับท้ายรายงานสรุป -->
    <div class="row text-center mt-5 pt-3 signature-box" style="font-size: 13px;">
        <div class="col-6"></div>
        <div class="col-6">
            ลงชื่อ......................................................................<br>
            ( ...................................................................... )<br>
            เจ้าหน้าที่งานทวิภาคี / ผู้รับผิดชอบงาน<br>
            วันที่ ........ เดือน ........................ พ.ศ. ............
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>