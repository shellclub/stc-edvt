<?php
session_start();
include "../config.php";
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

$status =$_GET['status'] ?? '';
$msg =$_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นำเข้าข้อมูลนักศึกษา | งานทวิภาคี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --primary: #1e3a8a; 
            --primary-hover: #172554;
            --surface: #ffffff;
            --bg-page: #f8fafc;
        }
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: var(--bg-page); 
            color: #334155;
        }
        .sidebar { 
            background: linear-gradient(180deg, #1e3a8a 0%, #0f172a 100%); 
            min-height: 100vh; 
            color: white; 
            padding: 24px 16px; 
        }
        .nav-link { 
            color: rgba(255,255,255,0.75); 
            border-radius: 12px; 
            padding: 12px 16px; 
            margin-bottom: 6px; 
            font-weight: 500;
            transition: all 0.2s ease; 
        }
        .nav-link:hover { 
            background: rgba(255,255,255,0.1); 
            color: #ffffff; 
            transform: translateX(3px);
        }
        .nav-link.active { 
            background: #2563eb; 
            color: white; 
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        
        /* UI Cards */
        .card-custom { 
            background: var(--surface);
            border-radius: 20px; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04); 
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            font-size: 14px;
            font-weight: 700;
        }

        /* Upload Dropzone */
        .upload-zone {
            border: 2px dashed #93c5fd;
            border-radius: 16px;
            padding: 40px 20px;
            text-align: center;
            background: #f0f7ff;
            transition: all 0.25s ease;
            cursor: pointer;
        }
        .upload-zone:hover, .upload-zone.dragover { 
            border-color: #2563eb; 
            background: #e0f2fe; 
            transform: scale(1.005);
        }

        .col-mapping-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 14px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-3 text-center border-bottom border-white border-opacity-10 mb-4">
                <div class="bg-white p-2 rounded-circle d-inline-block mb-3 shadow-sm">
                    <img src="https://upload.wikimedia.org/wikipedia/th/d/d4/Vec_Logo.png" width="46" alt="Logo" onerror="this.style.display='none'">
                </div>
                <h6 class="fw-bold mb-1 text-white">ระบบงานทวิภาคี</h6>
                <small class="text-white-50">วท.สุพรรณบุรี</small>
            </div>
            <nav class="nav flex-column">
                <a class="nav-link" href="bilateral_dashboard.php"><i class="bi bi-grid me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="bilateral_groups_summary.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link active" href="import_excel.php"><i class="bi bi-file-earmark-arrow-up me-2"></i> นำเข้า Excel</a>
                <a class="nav-link " href="import_excel_old.php"><i class="bi bi-file-earmark-arrow-up me-2"></i> นำเข้า CSV </a>
                <a class="nav-link" href="convert_rms_tool.php"><i class="bi bi-magic me-2"></i> แปลงไฟล์ RMS</a>
                <hr class="border-white border-opacity-10 my-3">
                <a class="nav-link text-danger-emphasis" href="admin_logout.php"><i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            
            <!-- Page Title Bar -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-spreadsheet text-primary"></i> นำเข้าข้อมูลนักศึกษา
                    </h2>
                    <p class="text-muted mb-0">ระบบแปลงและนำเข้ารายชื่อนักศึกษาเข้าสู่ฐานข้อมูลงานทวิภาคี</p>
                </div>
                <div>
                    <a href="upload_csv_process.php?download_template=1" class="btn btn-outline-secondary rounded-pill px-4 py-2 shadow-sm fw-medium">
                        <i class="bi bi-download me-2 text-success"></i>ดาวน์โหลด Template มาตรฐาน (.csv)
                    </a>
                </div>
            </div>

            <!-- แจ้งเตือนสถานะ -->
            <?php if (!empty($msg)): ?>
                <div class="alert alert-<?php echo ($status === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-<?php echo ($status === 'success') ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> fs-5"></i>
                        <span><?php echo htmlspecialchars($msg); ?></span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ลำดับขั้นตอนการใช้งาน (Quick Process Bar) -->
            <div class="card card-custom p-3 mb-4 bg-white">
                <div class="row g-2 text-center text-md-start align-items-center">
                    <div class="col-md-4 d-flex align-items-center gap-3 p-2">
                        <span class="step-badge bg-primary-subtle text-primary">1</span>
                        <div>
                            <div class="fw-bold small text-dark">ดาวน์โหลดจาก RMS</div>
                            <div class="text-muted small">ดึงไฟล์ดิบ .xlsx 82 คอลัมน์</div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-3 p-2 border-start-md">
                        <span class="step-badge bg-warning-subtle text-warning-emphasis">2</span>
                        <div>
                            <div class="fw-bold small text-dark">แปลงโครงสร้างไฟล์</div>
                            <div class="text-muted small">ใช้เครื่องมือตัดหัวตารางและชื่อกลุ่ม</div>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-3 p-2 border-start-md">
                        <span class="step-badge bg-success-subtle text-success">3</span>
                        <div>
                            <div class="fw-bold small text-dark">อัปโหลดไฟล์เข้าระบบ</div>
                            <div class="text-muted small">บันทึกข้อมูลนักศึกษาทันที</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="row g-4">
                
                <!-- ซ้าย: ดาวน์โหลดจาก RMS และ ลิงก์เครื่องมือแปลง -->
                <div class="col-lg-4 d-flex flex-column gap-4">
                    
                    <!-- การ์ดดึงไฟล์ RMS -->
                    <div class="card card-custom p-4 h-100">
                        <div class="d-flex align-items-center mb-3">
                            <div class="p-3 bg-primary-subtle text-primary rounded-4 me-3">
                                <i class="bi bi-cloud-arrow-down fs-4"></i>
                            </div>
                            <div>
                                <span class="badge bg-primary-subtle text-primary mb-1">ขั้นตอนที่ 1</span>
                                <h5 class="fw-bold mb-0">ดึงไฟล์จาก RMS STC</h5>
                            </div>
                        </div>
                        <p class="text-muted small mb-4">
                            กรอกรหัสกลุ่มการเรียนเพื่อเปิดลิงก์ดาวน์โหลดไฟล์รายชื่อนักศึกษาจากระบบ RMS สุพรรณบุรีโดยตรง
                        </p>
                        
                        <div class="mt-auto">
                            <label class="form-label fw-bold text-dark small">รหัสกลุ่มการเรียน (Group ID)</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-upc-scan text-muted"></i></span>
                                <input type="text" id="rms_group_id" class="form-control form-control-lg border-start-0 fs-6" placeholder="เช่น 693190902" value="693190902">
                            </div>
                            <button type="button" onclick="exportFromRMS()" class="btn btn-outline-primary w-100 py-2 rounded-pill fw-medium">
                                <i class="bi bi-box-arrow-up-right me-2"></i>เปิดดาวน์โหลดจาก RMS
                            </button>
                        </div>
                    </div>

                    <!-- การ์ดพาไปหน้าแปลงไฟล์ -->
                    <div class="card card-custom p-4 bg-gradient border-0 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div>
                                <span class="badge bg-white text-primary mb-2">ขั้นตอนที่ 2</span>
                                <h5 class="fw-bold mb-1 text-black">เครื่องมือแปลงไฟล์ RMS</h5>
                            </div>
                            <i class="bi bi-magic fs-2 text-white-50"></i>
                        </div>
                        <p class="text-black-50 small mb-3">
                            นำไฟล์ <code>.xlsx</code> 82 คอลัมน์ที่ดาวน์โหลดมาจาก RMS มาตัดเฉพาะคอลัมน์ที่ระบบต้องการ
                        </p>
                        <a href="convert_rms_tool.php" class="btn btn-light rounded-pill py-2 w-100 text-primary fw-bold shadow-sm">
                            <i class="bi bi-arrow-repeat me-1"></i> ไปยังหน้าแปลงไฟล์ RMS
                        </a>
                    </div>

                </div>

                <!-- ขวา: กล่องอัปโหลดไฟล์เข้าระบบ -->
                <div class="col-lg-8">
                    <div class="card card-custom p-4 p-md-5">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <span class="badge bg-success-subtle text-success mb-1">ขั้นตอนที่ 3</span>
                                <h4 class="fw-bold text-dark mb-0">อัปโหลดไฟล์นำเข้าฐานข้อมูล</h4>
                            </div>
                            <i class="bi bi-file-earmark-check text-success fs-3"></i>
                        </div>

                        <form action="upload_csv_process.php" method="POST" enctype="multipart/form-data">
                            
                            <!-- Dropzone อัปโหลด -->
                            <div class="upload-zone mb-4" onclick="document.getElementById('fileInput').click()">
                                <div class="mb-3">
                                    <i class="bi bi-file-earmark-arrow-up text-primary" style="font-size: 3.2rem;"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</h5>
                                <p class="text-muted small mb-0">รองรับไฟล์ที่แปลงแล้วสกุล <code>.csv</code>, <code>.xlsx</code>, <code>.xls</code></p>
                                <input type="file" name="student_file" id="fileInput" class="d-none" accept=".csv, .xlsx, .xls" required onchange="displayFileName()">
                                
                                <div id="fileNameDisplay" class="badge bg-primary text-white p-2 px-3 mt-3 d-none font-monospace fs-6 shadow-sm"></div>
                            </div>

                            <!-- คำอธิบายคอลัมน์ -->
                            <div class="col-mapping-box p-3 mb-4">
                                <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold small">
                                    <i class="bi bi-layout-three-columns text-primary"></i>
                                    <span>ลำดับคอลัมน์ในไฟล์ที่ถูกต้อง (6 คอลัมน์):</span>
                                </div>
                                <div class="row g-2 small text-muted">
                                    <div class="col-sm-6"><b>A:</b> รหัสนักศึกษา (11 หลัก)</div>
                                    <div class="col-sm-6"><b>B:</b> ชื่อ-นามสกุล</div>
                                    <div class="col-sm-6"><b>C:</b> วันเกิด (วว/ดด/ปปปป)</div>
                                    <div class="col-sm-6"><b>D:</b> รหัสกลุ่ม (group_code)</div>
                                    <div class="col-sm-6"><b>E:</b> ชื่อกลุ่ม (group_name)</div>
                                    <div class="col-sm-6"><b>F:</b> ครูที่ปรึกษา (advisor_name)</div>
                                </div>
                            </div>

                            <!-- ปุ่ม Submit นำเข้า -->
                            <button type="submit" name="btn_upload" class="btn btn-success btn-lg w-100 rounded-pill py-3 shadow fw-bold">
                                <i class="bi bi-cloud-arrow-up-fill me-2"></i>นำเข้าข้อมูลนักศึกษาลงระบบ
                            </button>

                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    // แสดงชื่อไฟล์เมื่อผู้ใช้เลือก
    function displayFileName() {
        const input = document.getElementById('fileInput');
        const display = document.getElementById('fileNameDisplay');
        if (input.files.length > 0) {
            display.innerHTML = '<i class="bi bi-file-earmark-check me-2"></i>' + input.files[0].name;
            display.classList.remove('d-none');
        }
    }

    // ลิงก์ดาวน์โหลด RMS ตามรหัสกลุ่ม
    function exportFromRMS() {
        const groupId = document.getElementById('rms_group_id').value.trim();
        if (!groupId) {
            alert('กรุณากรอกรหัสกลุ่มก่อนครับ');
            return;
        }
        const rmsUrl = `https://rms.stc.ac.th/sms_std_export.php?ex=print&group_id=${encodeURIComponent(groupId)}#ok`;
        window.open(rmsUrl, '_blank');
    }

    // Drag & Drop visual effect
    const dropZone = document.querySelector('.upload-zone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        }, false);
    });
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            document.getElementById('fileInput').files = files;
            displayFileName();
        }
    });
</script>
</body>
</html>