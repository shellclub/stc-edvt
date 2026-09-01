<?php
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) { header("location: index.php"); exit(); }
$sid = $_SESSION['student_id'];

// ดึงข้อมูลสถานที่เดิม (ถ้ามี)
$sql = "SELECT s.place_id, p.* FROM students s 
        LEFT JOIN internship_places p ON s.place_id = p.place_id 
        WHERE s.student_id = '$sid'";
$query = mysqli_query($conn, $sql);
$data = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งค่าสถานที่ฝึกงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7fa; font-family: 'Sarabun', sans-serif; }
        .card-setup { border: none; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .btn-gps { border-radius: 10px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container p-3">
        <div class="d-flex align-items-center mb-3">
            <a href="profile.php" class="text-dark me-2"><i class="bi bi-chevron-left fs-4"></i></a>
            <h4 class="mb-0 fw-bold">ข้อมูลที่พัก/ที่ฝึกงาน</h4>
        </div>

        <div class="card card-setup">
            <div class="card-body p-4">
                <form action="save_place.php" method="POST">
                    <input type="hidden" name="place_id" value="<?php echo $data['place_id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อสถานประกอบการ</label>
                        <input type="text" name="company_name" class="form-control" value="<?php echo $data['company_name']; ?>" placeholder="เช่น บจก. สุพรรณมอเตอร์" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อครูฝึก/ผู้ดูแล</label>
                        <input type="text" name="mentor_name" class="form-control" value="<?php echo $data['mentor_name']; ?>" placeholder="ชื่อ-นามสกุล ครูฝึก">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">วันฝึกงานต่อสัปดาห์</label>
                        <input type="text" name="training_days" class="form-control" value="<?php echo $data['training_days']; ?>" placeholder="เช่น จันทร์ - ศุกร์">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">เบอร์โทรติดต่อ</label>
                        <input type="tel" name="company_phone" class="form-control" value="<?php echo $data['company_phone']; ?>" placeholder="08x-xxxxxxx">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">ที่อยู่สถานประกอบการ</label>
                        <textarea name="company_address" class="form-control" rows="3"><?php echo $data['company_address']; ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">พิกัดสถานที่ฝึกงาน (GPS)</label>
                        <div class="row g-2">
                            <div class="col-6"><input type="text" name="w_lat" id="w_lat" class="form-control" value="<?php echo $data['workplace_lat']; ?>" readonly placeholder="Lat"></div>
                            <div class="col-6"><input type="text" name="w_lng" id="w_lng" class="form-control" value="<?php echo $data['workplace_lng']; ?>" readonly placeholder="Lng"></div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-gps w-100 mt-2" onclick="getLocation()">
                            <i class="bi bi-geo-alt"></i> ดึงพิกัดจากตำแหน่งปัจจุบัน
                        </button>
                        <small class="text-muted d-block mt-1">* กรุณากดปุ่มนี้ขณะอยู่ที่สถานประกอบการ</small>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" style="background:#800000; border:none; border-radius:15px;">
                        บันทึกข้อมูลสถานที่ฝึกงาน
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    document.getElementById('w_lat').value = pos.coords.latitude;
                    document.getElementById('w_lng').value = pos.coords.longitude;
                    alert("ดึงพิกัดเรียบร้อยแล้ว");
                }, () => { alert("กรุณาอนุญาตการเข้าถึงพิกัด"); });
            }
        }
    </script>
</body>
</html>