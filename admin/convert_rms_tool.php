<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

// -------------------------------------------------------------
// ฟังก์ชันอ่านไฟล์ .xlsx ด้วย ZipArchive ของ PHP (ไม่ต้องลง Library เพิ่ม)
// -------------------------------------------------------------
function readXlsxRows($filePath) {
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== TRUE) {
        return false;
    }

    // อ่าน Shared Strings (ข้อความภาษาไทย)
    $sharedStrings = [];
    if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
        $xmlStrings = simplexml_load_string($zip->getFromIndex($index));
        foreach ($xmlStrings->si as $val) {
            if (isset($val->t)) {
                $sharedStrings[] = (string)$val->t;
            } elseif (isset($val->r)) {
                $t = '';
                foreach ($val->r as $part) {
                    $t .= (string)$part->t;
                }
                $sharedStrings[] = $t;
            } else {
                $sharedStrings[] = '';
            }
        }
    }

    // อ่านข้อมูลใน Sheet1
    $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
    if (!$sheetXmlContent) {
        $sheetXmlContent = $zip->getFromIndex($zip->locateName('xl/worksheets/sheet1.xml'));
    }
    $zip->close();

    if (!$sheetXmlContent) return false;

    $sheet = simplexml_load_string($sheetXmlContent);
    $rows = [];

    foreach ($sheet->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $c) {
            $cellRef = (string)$c['r'];
            preg_match('/([A-Z]+)(\d+)/', $cellRef, $matches);
            $colLetters = $matches[1];
            
            $colIndex = 0;
            for ($i = 0; $i < strlen($colLetters); $i++) {
                $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - ord('A') + 1);
            }
            $colIndex -= 1;

            $val = (string)$c->v;
            if (isset($c['t']) && (string)$c['t'] === 's') {
                $val = $sharedStrings[(int)$val] ?? '';
            }
            $rowData[$colIndex] = $val;
        }

        if (!empty($rowData)) {
            $maxCol = max(array_keys($rowData));
            $fullRow = [];
            for ($i = 0; $i <= $maxCol; $i++) {
                $fullRow[$i] = $rowData[$i] ?? '';
            }
            $rows[] = $fullRow;
        }
    }
    return $rows;
}

// -------------------------------------------------------------
// ประมวลผลเมื่อกดแปลงไฟล์และดาวน์โหลด
// -------------------------------------------------------------
$error_msg = "";
if (isset($_POST['btn_convert']) && isset($_FILES['rms_file']['tmp_name'])) {
    $fileTmp = $_FILES['rms_file']['tmp_name'];
    $fileName = $_FILES['rms_file']['name'];
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($ext !== 'xlsx') {
        $error_msg = "กรุณาเลือกไฟล์สกุล .xlsx ที่ดาวน์โหลดมาจาก RMS";
    } else {
        $rows = readXlsxRows($fileTmp);
        if ($rows === false || empty($rows)) {
            $error_msg = "ไม่สามารถอ่านข้อมูลจากไฟล์ Excel ได้ หรือไฟล์เสียหาย";
        } else {
            // เตรียมสร้างไฟล์ CSV สำหรับดาวน์โหลด
            $group_code_detected = "template";
            $output_data = [];

            foreach ($rows as $data) {
                // ต้องมีอย่างน้อย 40 คอลัมน์ (โครงสร้าง RMS มี 82 คอลัมน์)
                if (count($data) < 30) continue;

                $student_id = trim($data[1] ?? ''); // คอลัมน์ B: รหัส
                
                // ตรวจสอบว่าเป็นรหัสนักศึกษา (ตัวเลข 8 หลักขึ้นไป)
                if (!is_numeric($student_id) || strlen($student_id) < 8) {
                    continue;
                }

                // รวม คำนำหน้า + ชื่อ + สกุล (C, D, E)
                $prefix = trim($data[2] ?? '');
                $fname  = trim($data[3] ?? '');
                $lname  = trim($data[4] ?? '');
                $fullname = trim("{$prefix}{$fname} {$lname}");

                // วันเกิด คอลัมน์ AJ (Index 35)
                $birth_date = trim($data[35] ?? '');

                // รหัสกลุ่ม คอลัมน์ AM (Index 38)
                $group_code = trim($data[38] ?? '');
                if (!empty($group_code)) {
                    $group_code_detected = $group_code;
                }

                // ชื่อกลุ่ม คอลัมน์ AN (Index 39) ตัดเอาในวงเล็บ เช่น ชย.2/2
               // ตัวอย่าง: ถ้าข้อความมาแนว "สาขางาน... | ปวช.2/1" หรือต้องการข้อความฝั่งที่มีคำว่า ปวช.
$full_group = trim($data[39] ?? '');
$group_parts = explode('|', $full_group);
if (count($group_parts) > 1) {
    // ตัดเอาฝั่งหลังขีด | 
    $group_name = trim($group_parts[1]);
} else {
    $group_name = $full_group;
}

                // ครูที่ปรึกษา คอลัมน์ BW (Index 74)
                $advisor_name = trim($data[74] ?? '');

                $output_data[] = [
                    $student_id,
                    $fullname,
                    $birth_date,
                    $group_code,
                    $group_name,
                    $advisor_name
                ];
            }

            if (empty($output_data)) {
                $error_msg = "ไม่พบแถวข้อมูลนักศึกษาในไฟล์ กรุณาตรวจสอบไฟล์ RMS ต้นฉบับ";
            } else {
                // ส่งออกเป็นไฟล์ CSV (UTF-8 with BOM)
                $outFilename = "converted_students_" . $group_code_detected . ".csv";
                header('Content-Type: text/csv; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $outFilename . '"');
                
                // ใส่ BOM ป้องกันสระภาษาไทยเพี้ยนใน MS Excel
                echo "\xEF\xBB\xBF";

                $f = fopen('php://output', 'w');
                // บรรทัด Header
                fputcsv($f, ['student_id', 'fullname', 'birth_date', 'group_code', 'group_name', 'advisor_name']);
                foreach ($output_data as $row) {
                    fputcsv($f, $row);
                }
                fclose($f);
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เครื่องมือตัดแปลงไฟล์ RMS สู่ Template มาตรฐาน | DVT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 12px; padding: 12px 15px; margin-bottom: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        
        .convert-card { border: none; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); background: white; }
        .upload-dropzone {
            border: 2px dashed #0d6efd;
            background: #f0f7ff;
            border-radius: 20px;
            padding: 40px 20px;
            cursor: pointer;
            transition: 0.3s;
        }
        .upload-dropzone:hover { background: #e2efff; border-color: #0b5ed7; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-4 text-center">
                <div class="bg-white p-2 rounded-circle d-inline-block mb-3 shadow-sm">
                    <img src="https://upload.wikimedia.org/wikipedia/th/d/d4/Vec_Logo.png" width="45" alt="Logo" onerror="this.style.display='none'">
                </div>
                <h6 class="fw-bold mb-0">ระบบงานทวิภาคี</h6>
                <small class="opacity-50">วท.สุพรรณบุรี</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="bilateral_groups_summary.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <a class="nav-link active" href="convert_rms_tool.php"><i class="bi bi-arrow-repeat me-2"></i> เครื่องมือแปลงไฟล์ RMS</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">
                        <i class="bi bi-magic text-primary me-2"></i>แปลงไฟล์ RMS เป็น Template มาตรฐาน
                    </h2>
                    <p class="text-muted mb-0">นำไฟล์ .xlsx (82 คอลัมน์) จาก RMS มาตัดเลือกเฉพาะข้อมูลที่จำเป็น และดาวน์โหลดเป็นไฟล์ CSV ที่พร้อมนำเข้า</p>
                </div>
                <a href="import_excel.php" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> กลับหน้า นำเข้า Excel
                </a>
            </div>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card convert-card p-4 p-md-5">
                        <form action="" method="POST" enctype="multipart/form-data">
                            
                            <div class="upload-dropzone text-center mb-4" onclick="document.getElementById('rmsInput').click()">
                                <i class="bi bi-filetype-xlsx text-primary" style="font-size: 4rem;"></i>
                                <h4 class="fw-bold mt-2 text-dark">เลือกไฟล์ Excel จากระบบ RMS (.xlsx)</h4>
                                <p class="text-muted small mb-0">คลิกที่นี่เพื่ออัปโหลดไฟล์ เช่น <code>student682010102.xlsx</code></p>
                                <input type="file" name="rms_file" id="rmsInput" class="d-none" accept=".xlsx" required onchange="showSelectedFile()">
                                <div id="selectedFileName" class="badge bg-primary text-white p-2 mt-3 d-none font-monospace fs-6"></div>
                            </div>

                            <!-- กล่องสรุปการจับคู่ข้อมูล (Column Mapping) -->
                            <div class="bg-light p-3 rounded-4 mb-4 border">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-check2-all text-success me-2"></i>สิ่งที่ระบบจะตัดและแปลงให้อัตโนมัติ:</h6>
                                <div class="row g-2 small text-muted">
                                    <div class="col-sm-6">✅ รหัสนักศึกษา (คอลัมน์ B)</div>
                                    <div class="col-sm-6">✅ รวมชื่อเต็ม: คำนำหน้า + ชื่อ + สกุล (คอลัมน์ C, D, E)</div>
                                    <div class="col-sm-6">✅ วันเกิด (คอลัมน์ AJ)</div>
                                    <div class="col-sm-6">✅ รหัสกลุ่มการเรียน (คอลัมน์ AM)</div>
                                    <div class="col-sm-6">✅ ชื่อย่อกลุ่ม (ตัดจากชื่อเต็ม คอลัมน์ AN)</div>
                                    <div class="col-sm-6">✅ ชื่อครูที่ปรึกษา (คอลัมน์ BW)</div>
                                </div>
                            </div>

                            <button type="submit" name="btn_convert" class="btn btn-primary btn-lg w-100 rounded-pill py-3 fw-bold shadow">
                                <i class="bi bi-arrow-down-circle me-2"></i>ประมวลผลและดาวน์โหลดไฟล์ CSV (UTF-8)
                            </button>
                        </form>
                    </div>

                    <!-- ขั้นตอนการนำไปใช้ -->
                    <div class="card border-0 rounded-4 shadow-sm p-4 mt-4 bg-white">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-lightbulb text-warning me-2"></i>ขั้นตอนการนำเข้าสู่ฐานข้อมูล:</h6>
                        <ol class="small text-muted mb-0 ps-3">
                            <li class="mb-1">อัปโหลดไฟล์ <code>.xlsx</code> ในหน้านี้ แล้วกดปุ่มประมวลผลเพื่อดาวน์โหลดไฟล์ <code>converted_students_xxxx.csv</code></li>
                            <li class="mb-1">กลับไปที่หน้า <a href="import_excel.php" class="fw-bold text-decoration-underline">นำเข้า Excel (import_excel.php)</a></li>
                            <li>อัปโหลดไฟล์ CSV ที่เพิ่งดาวน์โหลด ข้อมูลจะถูกบันทึกลงตาราง <code>students</code> โดยไม่มีปัญหาเรื่องฟอนต์เพี้ยน</li>
                        </ol>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function showSelectedFile() {
        const input = document.getElementById('rmsInput');
        const badge = document.getElementById('selectedFileName');
        if (input.files.length > 0) {
            badge.innerText = "ไฟล์ที่เลือก: " + input.files[0].name;
            badge.classList.remove('d-none');
        }
    }
</script>
</body>
</html>