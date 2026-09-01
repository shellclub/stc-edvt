<?php
session_start();
include "../config.php";
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>นำเข้าข้อมูล Excel | งานทวิภาคี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        .upload-zone {
            border: 2px dashed #cbd5e0;
            border-radius: 25px;
            padding: 60px;
            text-align: center;
            background: white;
            transition: 0.3s;
            cursor: pointer;
        }
        .upload-zone:hover { border-color: #1a237e; background: #f0f4ff; }
        .sidebar { background: #1a237e; min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 12px; padding: 12px 15px; margin-bottom: 8px; }
        .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar เดิม -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-4 text-center">
                <h6 class="fw-bold">ระบบงานทวิภาคี</h6>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="bilateral_groups.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link active" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            <div class="mb-5">
                <h2 class="fw-bold text-dark">นำเข้าข้อมูลนักศึกษา</h2>
                <p class="text-muted">อัปโหลดไฟล์ CSV เพื่อเพิ่มรายชื่อนักศึกษาจำนวนมากเข้าสู่ระบบ</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <form action="upload_csv_process.php" method="POST" enctype="multipart/form-data">
                            <div class="upload-zone mb-4" onclick="document.getElementById('fileInput').click()">
                                <i class="bi bi-cloud-arrow-up-fill text-primary" style="font-size: 4rem;"></i>
                                <h4 class="fw-bold mt-3">คลิกเพื่อเลือกไฟล์ CSV</h4>
                                <p class="text-muted">หรือลากไฟล์มาวางในบริเวณนี้</p>
                                <input type="file" name="student_file" id="fileInput" class="d-none" accept=".csv" required onchange="displayFileName()">
                                <div id="fileNameDisplay" class="badge bg-primary-subtle text-primary p-2 mt-2 d-none"></div>
                            </div>

                            <div class="bg-light p-3 rounded-3 mb-4">
                                <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>คำแนะนำรูปแบบไฟล์:</h6>
                                <small class="text-muted d-block">• คอลัมน์ A: รหัสนักศึกษา (11 หลัก)</small>
                                <small class="text-muted d-block">• คอลัมน์ B: ชื่อ-นามสกุล</small>
                                <small class="text-muted d-block">• คอลัมน์ C: วันเกิด (พ.ศ. รูปแบบ วัน/เดือน/ปีเกิด)</small>
                                <small class="text-muted d-block">• คอลัมน์ D: รหัสกลุ่มการเรียน</small>
                                <small class="text-muted d-block">• คอลัมน์ E: ชื่อกลุ่มการเรียน</small>
                                <small class="text-muted d-block">• คอลัมน์ F: ครูที่ปรึกษา</small>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill py-3">
                                    <i class="bi bi-check-circle me-2"></i>เริ่มดำเนินการนำเข้า
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function displayFileName() {
        const input = document.getElementById('fileInput');
        const display = document.getElementById('fileNameDisplay');
        if (input.files.length > 0) {
            display.innerText = "ไฟล์ที่เลือก: " + input.files[0].name;
            display.classList.remove('d-none');
        }
    }
</script>
</body>
</html>