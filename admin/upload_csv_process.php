<?php
session_start();
include "../config.php";

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

// -------------------------------------------------------------------------
// 1. ฟังก์ชันช่วยอ่านไฟล์ .XLSX โดยตรงด้วย ZipArchive (ไม่ต้องใช้ Library เสริม)
// -------------------------------------------------------------------------
function parseXLSX($filePath) {
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== TRUE) {
        return false;
    }

    // อ่าน Shared Strings (ข้อความภาษาไทยใน Excel)
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

    // อ่านข้อมูล Sheet1
    $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
    if (!$sheetXmlContent) {
        // ลองหา sheet อื่นถ้าไม่มี sheet1
        $sheetXmlContent = $zip->getFromIndex($zip->locateName('xl/worksheets/sheet1.xml'));
    }
    $zip->close();

    if (!$sheetXmlContent) return false;

    $sheet = simplexml_load_string($sheetXmlContent);
    $rows = [];

    foreach ($sheet->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $c) {
            // คำนวณ Index ของคอลัมน์จาก cell reference เช่น A1, B1, AM2
            $cellRef = (string)$c['r'];
            preg_match('/([A-Z]+)(\d+)/', $cellRef, $matches);
            $colLetters = $matches[1];
            
            // แปลงอักษรคอลัมน์เป็นตัวเลข index (A=0, B=1, ...)
            $colIndex = 0;
            for ($i = 0; $i < strlen($colLetters); $i++) {
                $colIndex = $colIndex * 26 + (ord($colLetters[$i]) - ord('A') + 1);
            }
            $colIndex -= 1;

            $val = (string)$c->v;
            // ถ้าเป็นชนิด String ที่อ้างอิงจาก sharedStrings
            if (isset($c['t']) && (string)$c['t'] === 's') {
                $val = $sharedStrings[(int)$val] ?? '';
            }
            $rowData[$colIndex] = $val;
        }
        
        // เติมช่องว่างให้ครบตามขนาดคอลัมน์สูงสุดของแถว
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

// -------------------------------------------------------------------------
// 2. ดาวน์โหลดไฟล์ตัวอย่าง Template (.csv)
// -------------------------------------------------------------------------
if (isset($_GET['download_template'])) {
    $filename = "student_template_" . date('Ymd') . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['student_id', 'fullname', 'birth_date', 'group_code', 'group_name', 'advisor_name']);
    fputcsv($output, ['68201010023', 'นายอังกูร วงษ์พันธ์', '13/09/2552', '682010102', 'ชย.2/2', 'นายทิวากร อินทรประสิทธิ์']);
    fclose($output);
    exit();
}

// -------------------------------------------------------------------------
// 3. ระบบนำเข้าไฟล์ (รองรับทั้ง .xlsx ตรงๆ และ .csv)
// -------------------------------------------------------------------------
if (isset($_POST['btn_upload']) && isset($_FILES['student_file']['tmp_name'])) {
    $fileTmp  = $_FILES['student_file']['tmp_name'];
    $fileName = $_FILES['student_file']['name'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!is_uploaded_file($fileTmp)) {
        header("location: import_excel.php?status=error&msg=" . urlencode("ไม่พบไฟล์ที่อัปโหลด"));
        exit();
    }

    $allRows = [];

    // กรณีเป็นไฟล์ Excel .xlsx
    if ($fileExt === 'xlsx') {
        $parsed = parseXLSX($fileTmp);
        if ($parsed === false) {
            header("location: import_excel.php?status=error&msg=" . urlencode("ไม่สามารถอ่านไฟล์ .xlsx ได้ กรุณาลองบันทึกเป็นไฟล์ .csv แล้วอัปโหลดใหม่"));
            exit();
        }
        $allRows = $parsed;
    } 
    // กรณีเป็นไฟล์ .csv
    else {
        $raw = file_get_contents($fileTmp);
        
        // ตรวจสอบภาษาไทย UTF-8 / CP874
        if (!preg_match('//u', $raw)) {
            $raw = iconv('CP874', 'UTF-8//IGNORE', $raw);
        }
        file_put_contents($fileTmp, $raw);

        $handle = fopen($fileTmp, "r");
        while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
            $allRows[] = $data;
        }
        fclose($handle);
    }

    $success_count = 0;
    $updated_count = 0;

    foreach ($allRows as $data) {
        $cols = count($data);
        if ($cols == 0) continue;

        $student_id   = '';
        $fullname     = '';
        $birth_date   = '';
        $group_code   = '';
        $group_name   = '';
        $advisor_name = '';

        // -----------------------------------------------------------------
        // โครงสร้างไฟล์ RMS สุพรรณบุรี (เช่น student682010102.xlsx มี 82 คอลัมน์)
        // -----------------------------------------------------------------
        if ($cols >= 30) {
            // คอลัมน์ B (Index 1) = รหัสนักศึกษา
            $student_id = trim($data[1] ?? '');

            // ข้ามแถวหัวตารางหรือแถวที่ไม่ใช่ตัวเลขรหัส 8 หลักขึ้นไป
            if (!is_numeric($student_id) || strlen($student_id) < 8) {
                continue;
            }

            // คอลัมน์ C(2) = คำนำหน้า, D(3) = ชื่อ, E(4) = นามสกุล
            $prefix   = trim($data[2] ?? '');
            $fname    = trim($data[3] ?? '');
            $lname    = trim($data[4] ?? '');
            $fullname = trim("{$prefix}{$fname} {$lname}");

            // คอลัมน์ AJ (Index 35) = วัน/เดือน/ปีเกิด
            $birth_date = trim($data[35] ?? '');

            // คอลัมน์ AM (Index 38) = รหัสกลุ่ม
            $group_code = trim($data[38] ?? '');

            // คอลัมน์ AN (Index 39) = ชื่อเต็มกลุ่ม ดึงชื่อย่อในวงเล็บ เช่น ชย.2/2
            $full_group = trim($data[39] ?? '');
            if (preg_match('/\((.*?)\)/', $full_group, $matches)) {
                $group_name = trim($matches[1]);
            } else {
                $group_name = $full_group;
            }

            // คอลัมน์ BW (Index 74) = ชื่อครูที่ปรึกษา
            $advisor_name = trim($data[74] ?? '');
        } 
        // -----------------------------------------------------------------
        // โครงสร้างไฟล์ Template ธรรมดา (6 คอลัมน์ A-F)
        // -----------------------------------------------------------------
        else {
            $student_id   = trim($data[0] ?? '');
            $fullname     = trim($data[1] ?? '');
            $birth_date   = trim($data[2] ?? '');
            $group_code   = trim($data[3] ?? '');
            $group_name   = trim($data[4] ?? '');
            $advisor_name = trim($data[5] ?? '');

            if (!is_numeric($student_id) || strlen($student_id) < 8) {
                continue;
            }
        }

        if (empty($student_id)) continue;

        // รหัสผ่านเริ่มต้นใช้วันเกิด หรือรหัสนักศึกษา[cite: 2]
        $default_pwd = !empty($birth_date) ? $birth_date : $student_id;
        $hashed_pwd  = password_hash($default_pwd, PASSWORD_DEFAULT);

        $student_id   = mysqli_real_escape_string($conn, $student_id);
        $fullname     = mysqli_real_escape_string($conn, $fullname);
        $birth_date   = mysqli_real_escape_string($conn, $birth_date);
        $group_code   = mysqli_real_escape_string($conn, $group_code);
        $group_name   = mysqli_real_escape_string($conn, $group_name);
        $advisor_name = mysqli_real_escape_string($conn, $advisor_name);
        $hashed_pwd   = mysqli_real_escape_string($conn, $hashed_pwd);

        // ตรวจสอบและบันทึกลงตาราง students[cite: 2]
        $check = mysqli_query($conn, "SELECT student_id FROM students WHERE student_id = '$student_id'");
        if (mysqli_num_rows($check) > 0) {
            $sql_update = "UPDATE students SET 
                            fullname = '$fullname',
                            birth_date = '$birth_date',
                            group_code = '$group_code',
                            group_name = '$group_name',
                            advisor_name = '$advisor_name'
                           WHERE student_id = '$student_id'";
            mysqli_query($conn, $sql_update);
            $updated_count++;
        } else {
            $sql_insert = "INSERT INTO students (student_id, fullname, birth_date, group_code, group_name, advisor_name, password, is_first_login) 
                           VALUES ('$student_id', '$fullname', '$birth_date', '$group_code', '$group_name', '$advisor_name', '$hashed_pwd', 1)";
            mysqli_query($conn, $sql_insert);
            $success_count++;
        }
    }

    $msg = "นำเข้าสำเร็จ: เพิ่มข้อมูลใหม่ {$success_count} คน, อัปเดตข้อมูลเดิม {$updated_count} คน";
    header("location: import_excel.php?status=success&msg=" . urlencode($msg));
    exit();

} else {
    header("location: import_excel.php");
    exit();
}
?>