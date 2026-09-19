<?php
session_start();
include "../config.php";

// ตรวจสอบสิทธิ์ Admin ทวิภาคี
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

// คำสั่ง SQL ดึงข้อมูลสถานประกอบการ และรวมข้อมูลที่ซ้ำกัน (GROUP BY)
$sql_summary = "
    SELECT 
        p.company_name,
        p.company_address,
        p.company_phone,
        p.mentor_name,
        p.training_days,
        COUNT(s.student_id) AS total_students,
        GROUP_CONCAT(CONCAT(s.fullname, ' (', s.group_name, ')') ORDER BY s.student_id SEPARATOR '<br>') AS student_list
    FROM internship_places p
    INNER JOIN students s ON p.place_id = s.place_id
    WHERE p.company_name IS NOT NULL AND TRIM(p.company_name) != ''
    GROUP BY p.company_name, p.company_address, p.mentor_name, p.company_phone, p.training_days
    ORDER BY total_students DESC, p.company_name ASC
";

$query_summary = mysqli_query($conn, $sql_summary);

// นับจำนวนรวมทั้งหมด
$total_places = mysqli_num_rows($query_summary);
$total_students_all = 0;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปข้อมูลสถานประกอบการฝึกงาน | วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; color: #000; }
        
        /* สไตล์ตาราง */
        .table-custom { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table-custom th, .table-custom td {
            border: 1px solid #000 !important;
            padding: 8px 10px;
            font-size: 13px;
            vertical-align: top;
        }
        .table-custom th {
            background-color: #f2f2f2 !important;
            text-align: center;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* รูปแบบหน้ากระดาษและสำหรับ Print */
        .report-page {
            background: white;
            padding: 20mm 15mm;
            margin: 15px auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 297mm; /* หน้ากว้าง A4 แนวนอน */
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 12mm 10mm;
            }
            body { background: none !important; margin: 0 !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .report-page {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            tr { page-break-inside: avoid !important; break-inside: avoid !important; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>

<!-- แถบเครื่องมือควบคุม (ไม่แสดงเวลาพิมพ์) -->
<div class="no-print bg-white border-bottom shadow-sm p-3 mb-4">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <a href="bilateral_dashboard.php" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-1"></i> กลับหน้าหลัก
            </a>
            <span class="fw-bold fs-5 text-dark">สรุปข้อมูลสถานประกอบการ</span>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm">
                <i class="bi bi-printer-fill me-2"></i> สั่งพิมพ์รายงาน / บันทึกเป็น PDF
            </button>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="report-page">
        <!-- หัวเอกสารรายงาน -->
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">รายงานสรุปรายชื่อสถานประกอบการฝึกประสบการณ์วิชาชีพ</h4>
            <h5 class="fw-bold mb-1">วิทยาลัยเทคนิคสุพรรณบุรี</h5>
            <div class="text-muted small">
                ข้อมูล ณ วันที่ <?php echo date('d/m/') . (date('Y') + 543); ?> | จำนวนสถานประกอบการทั้งหมด: <b><?php echo $total_places; ?></b> แห่ง
            </div>
        </div>

        <!-- ตารางข้อมูล -->
        <table class="table-custom">
            <thead>
                <tr>
                    <th style="width: 5%;">ลำดับ</th>
                    <th style="width: 22%;">ชื่อสถานประกอบการ</th>
                    <th style="width: 25%;">ที่อยู่ / เบอร์โทรติดต่อ</th>
                    <th style="width: 15%;">ชื่อครูฝึก / วันที่ฝึก</th>
                    <th style="width: 8%;">จำนวน (คน)</th>
                    <th style="width: 25%;">รายชื่อนักศึกษา (กลุ่ม)</th>
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
                        <small class="text-muted"><i class="bi bi-telephone"></i> โทร: <?php echo htmlspecialchars($row['company_phone'] ?: '-'); ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($row['mentor_name'] ?: '-'); ?></strong><br>
                        <small class="text-muted">ฝึกวัน: <?php echo htmlspecialchars($row['training_days'] ?: '-'); ?></small>
                    </td>
                    <td class="text-center fw-bold">
                        <?php echo $row['total_students']; ?>
                    </td>
                    <td style="font-size: 12px; line-height: 1.5;">
                        <?php echo $row['student_list']; ?>
                    </td>
                </tr>
                <?php 
                    endwhile; 
                ?>
                <tr style="background-color: #f8f9fa; font-weight: bold;">
                    <td colspan="4" class="text-end">รวมจำนวนนักศึกษาทั้งหมดที่ออกฝึกงาน:</td>
                    <td class="text-center"><?php echo $total_students_all; ?></td>
                    <td>คน</td>
                </tr>
                <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูลสถานประกอบการในระบบ</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- ช่องลงชื่อท้ายเอกสาร -->
        <div class="row text-center mt-5 pt-4 no-break" style="font-size: 14px;">
            <div class="col-6"></div>
            <div class="col-6">
                ลงชื่อ......................................................................<br>
                ( ...................................................................... )<br>
                เจ้าหน้าที่งานทวิภาคี / ผู้รับผิดชอบ<br>
                วันที่ ........ / ........ / ................
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>