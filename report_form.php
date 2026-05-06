<?php
date_default_timezone_set('Asia/Bangkok');
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) {
    header("location: index.php");
    exit();
}
$sid = $_SESSION['student_id'];
$sql = "SELECT fullname, student_id FROM students WHERE student_id = '$sid'";
$query = mysqli_query($conn, $sql);
$user = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บันทึกรายงาน - วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f2f5; padding-bottom: 80px; }
        .header-section { background-color: #800000; color: white; padding: 20px 0; border-radius: 0 0 20px 20px; }
        .btn-save { background-color: #800000; color: white; border-radius: 10px; font-weight: 600; }
        #imagePreview { width: 100%; max-height: 300px; object-fit: cover; border-radius: 10px; display: none; margin-top: 10px; border: 1px solid #ddd; }
    </style>
</head>
<body>

    <div class="header-section shadow-sm mb-4">
        <div class="container d-flex align-items-center">
            <a href="dashboard.php" class="text-white me-3 text-decoration-none"><i class="bi bi-arrow-left-circle fs-3"></i></a>
            <div>
                <h5 class="mb-0">บันทึกรายงานฝึกงาน</h5>
                <small class="opacity-75"><?php echo $user['fullname']; ?></small>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form id="reportForm">
                    <div class="mb-3">
                        <label class="form-label fw-bold">วันที่ปฏิบัติงาน</label>
                        <input type="date" name="report_date" id="report_date" class="form-control form-control-lg" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">รายละเอียดงาน</label>
                        <textarea name="job_details" class="form-control" rows="5" placeholder="ระบุหน้าที่ในวันนี้..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ปัญหา/อุปสรรค</label>
                        <textarea name="problems" class="form-control" rows="2" placeholder="ถ้าไม่มีให้ใส่ -"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">รูปภาพ (ไม่บังคับ)</label>
                        <input type="file" id="imgInput" class="form-control" accept="image/*">
                        <img id="imagePreview" src="#">
                    </div>

                    <input type="hidden" name="lat" id="lat">
                    <input type="hidden" name="lng" id="lng">

                    <div id="gpsStatus" class="text-center mb-3 small">
                        <span class="badge bg-secondary" id="gpsBadge"><i class="bi bi-geo-alt"></i> กำลังรอพิกัด...</span>
                    </div>

                    <button type="submit" class="btn btn-save w-100 py-3 shadow" id="btnSubmit">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i> บันทึกรายงาน
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const imgInput = document.getElementById('imgInput');
        const imagePreview = document.getElementById('imagePreview');
        let resizedBlob = null;

        // ระบบย่อรูปฝั่ง Client
        imgInput.onchange = e => {
            const file = e.target.files[0];
            if (!file) { resizedBlob = null; imagePreview.style.display = 'none'; return; }
            
            const reader = new FileReader();
            reader.readAsDataURL(file);
            reader.onload = event => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 800;
                    let width = img.width, height = img.height;
                    if (width > MAX_WIDTH) { height *= MAX_WIDTH / width; width = MAX_WIDTH; }
                    canvas.width = width; canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob(blob => {
                        resizedBlob = blob;
                        imagePreview.src = URL.createObjectURL(blob);
                        imagePreview.style.display = 'block';
                    }, 'image/jpeg', 0.6);
                }
            }
        };

        // ระบบ Geolocation
        function getGps() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    document.getElementById("lat").value = pos.coords.latitude;
                    document.getElementById("lng").value = pos.coords.longitude;
                    document.getElementById("gpsBadge").className = "badge bg-success";
                    document.getElementById("gpsBadge").innerHTML = "ตรวจพบพิกัดแล้ว";
                }, null, { enableHighAccuracy: true });
            }
        }
        window.onload = () => {
            getGps();
            const dateInput = document.getElementById('report_date');
            const now = new Date();
            const lastMonth = new Date(); lastMonth.setDate(now.getDate() - 30);
            dateInput.max = now.toISOString().split('T')[0];
            dateInput.min = lastMonth.toISOString().split('T')[0];
            dateInput.value = dateInput.max;
        };

        // ส่งข้อมูลแบบ Fetch
        document.getElementById('reportForm').onsubmit = function(e) {
            e.preventDefault();
            if (!document.getElementById("lat").value) { alert("รอพิกัด GPS สักครู่..."); return; }
            
            const formData = new FormData(this);
            if (resizedBlob) formData.set('report_image', resizedBlob, 'report.jpg');

            fetch('save_report.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(data => { document.open(); document.write(data); document.close(); });
        };
    </script>
</body>
</html>