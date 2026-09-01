<?php
session_start();
include "config.php";

// 1. ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['student_id'])) { 
    header("location: index.php"); 
    exit(); 
}

$sid = $_SESSION['student_id'];

// 2. ดึงข้อมูลนักศึกษาพร้อมข้อมูลสถานที่ฝึกงาน (เชื่อมโยงผ่าน place_id)
$sql = "SELECT s.*, p.company_name, p.mentor_name, p.company_address, 
               p.company_phone, p.training_days, p.workplace_lat, p.workplace_lng
        FROM students s
        LEFT JOIN internship_places p ON s.place_id = p.place_id
        WHERE s.student_id = '$sid'";

$query = mysqli_query($conn, $sql);
$user = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์นักศึกษา - วท.สุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; padding-bottom: 90px; }
        .profile-header { 
            background: linear-gradient(135deg, #800000 0%, #b30000 100%); 
            color: white; 
            padding: 50px 0 70px 0; 
            border-radius: 0 0 40px 40px; 
        }
        .avatar-container { margin-top: -60px; position: relative; display: inline-block; }
        .profile-img { 
            width: 120px; height: 120px; 
            border-radius: 50%; 
            border: 5px solid white; 
            object-fit: cover; 
            background: white;
        }
        .card-profile { border: none; border-radius: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .section-header { 
            font-size: 0.95rem; 
            font-weight: 600; 
            color: #800000; 
            border-left: 5px solid #800000; 
            padding-left: 12px; 
            margin-bottom: 15px; 
        }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #7f8c8d; font-size: 0.85rem; }
        .info-value { color: #2c3e50; font-weight: 600; font-size: 0.9rem; text-align: right; }
        
        /* Navbar Bottom Custom */
        .nav-bottom { position: fixed; bottom: 0; width: 100%; background: white; display: flex; justify-content: space-around; padding: 12px 0; box-shadow: 0 -5px 15px rgba(0,0,0,0.05); z-index: 1000; }
        .nav-item-custom { text-align: center; color: #95a5a6; text-decoration: none; font-size: 0.7rem; }
        .nav-item-custom.active { color: #800000; font-weight: bold; }
        .nav-item-custom i { font-size: 1.5rem; display: block; }
    </style>
</head>
<body>

    <div class="profile-header text-center">
        <h4 class="fw-bold mb-1">ข้อมูลนักศึกษา</h4>
        <p class="opacity-75 small">วิทยาลัยเทคนิคสุพรรณบุรี</p>
    </div>

    <div class="container text-center mb-4">
        <div class="avatar-container shadow-sm">
            <!-- ดึงรูปจากระบบ RMS -->
            <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $user['student_id']; ?>.jpg&x=200&f=0" 
                 class="profile-img" onerror="this.src='https://cdn-icons-png.flaticon.com/512/149/149071.png';">
        </div>
        <h5 class="mt-3 fw-bold"><?php echo $user['fullname']; ?></h5>
        <span class="badge bg-light text-dark rounded-pill px-3 py-2 border shadow-xs">
            <i class="bi bi-person-vcard me-1"></i> <?php echo $user['student_id']; ?>
        </span>
    </div>

    <div class="container">
        <!-- การเรียน -->
        <div class="card card-profile mb-3">
            <div class="card-body p-4">
                <div class="section-header">ข้อมูลหลักสูตร</div>
                <div class="info-row">
                    <span class="info-label">กลุ่มเรียน</span>
                    <span class="info-value"><?php echo $user['group_name']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">รหัสกลุ่ม</span>
                    <span class="info-value"><?php echo $user['group_code']; ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">ครูที่ปรึกษา</span>
                    <span class="info-value text-muted"><?php echo $user['advisor_name']; ?></span>
                </div>
            </div>
        </div>

        <!-- สถานที่ฝึกงาน -->
        <div class="card card-profile mb-4">
            <div class="card-body p-4">
                <div class="section-header d-flex justify-content-between align-items-center">
    <span>ข้อมูลสถานที่ฝึกงาน</span>
    <a href="edit_place.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i> แก้ไข</a>
</div>
                
                <?php if($user['company_name']): ?>
                    <div class="info-row">
                        <span class="info-label">สถานประกอบการ</span>
                        <span class="info-value text-primary"><?php echo $user['company_name']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">ครูฝึก (Mentor)</span>
                        <span class="info-value"><?php echo $user['mentor_name']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">วันฝึกงาน</span>
                        <span class="info-value text-success"><?php echo $user['training_days']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">เบอร์ติดต่อ</span>
                        <span class="info-value"><?php echo $user['company_phone']; ?></span>
                    </div>
                    <div class="mt-3 bg-light p-3 rounded-3">
                        <p class="info-label mb-1">ที่อยู่:</p>
                        <p class="mb-0 small fw-normal"><?php echo $user['company_address']; ?></p>
                    </div>

                    <?php if($user['workplace_lat']): ?>
                    <a href="https://www.google.com/maps?q=<?php echo $user['workplace_lat']; ?>,<?php echo $user['workplace_lng']; ?>" 
                       target="_blank" class="btn btn-outline-danger w-100 mt-3 py-2" style="border-radius:12px;">
                        <i class="bi bi-geo-alt-fill me-1"></i> แผนที่สถานประกอบการ
                    </a>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-3">
                        <i class="bi bi-buildings fs-1 text-muted opacity-25"></i>
                        <p class="text-muted small mt-2">ยังไม่มีข้อมูลการจับคู่สถานที่ฝึกงาน</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- ในหน้า profile.php ส่วนของข้อมูลสถานที่ฝึกงาน -->
          
        <a href="logout.php" class="btn btn-white w-100 py-3 text-danger fw-bold shadow-sm mb-4 border" 
           style="border-radius:15px;" onclick="return confirm('ต้องการออกจากระบบหรือไม่?')">
            <i class="bi bi-power me-2"></i> ออกจากระบบ
        </a>
    </div>

    <!-- Nav Bottom -->
    <nav class="nav-bottom">
        <a href="dashboard.php" class="nav-item-custom">
            <i class="bi bi-house-door"></i>
            หน้าหลัก
        </a>
        <a href="report_form.php" class="nav-item-custom">
            <i class="bi bi-plus-circle"></i>
            รายงาน
        </a>
        <a href="report_history.php" class="nav-item-custom">
            <i class="bi bi-clock-history"></i>
            ประวัติ
        </a>
        <a href="profile.php" class="nav-item-custom active">
            <i class="bi bi-person-fill"></i>
            โปรไฟล์
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>