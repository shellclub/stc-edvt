<?php
session_start();
// ลบข้อมูล Session ทั้งหมด
session_destroy();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ออกจากระบบเรียบร้อย - วท.สุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: #f4f7fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .logout-card {
            background: white;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: #fff5f5;
            color: #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .loader {
            width: 100%;
            background-color: #f3f3f3;
            border-radius: 10px;
            height: 6px;
            margin-top: 25px;
            overflow: hidden;
        }
        .loader-bar {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #800000, #dc3545);
            transition: width 2.5s linear;
        }
    </style>
</head>
<body>

<div class="logout-card">
    <div class="icon-circle">
        <i class="bi bi-box-arrow-right"></i>
    </div>
    <h4 class="fw-bold text-dark">ออกจากระบบสำเร็จ</h4>
    <p class="text-muted">ขอบคุณสำหรับการปฏิบัติงานในวันนี้<br>ระบบกำลังนำคุณกลับไปยังหน้าเข้าใช้งาน...</p>
    
    <div class="loader">
        <div class="loader-bar" id="loaderBar"></div>
    </div>
    
    <div class="mt-4">
        <a href="index.php" class="btn btn-link text-decoration-none text-muted small">คลิกที่นี่หากไม่เปลี่ยนหน้าอัตโนมัติ</a>
    </div>
</div>

<script>
    // แสดงแอนิเมชันแถบดาวน์โหลด
    window.onload = function() {
        setTimeout(function() {
            document.getElementById('loaderBar').style.width = '100%';
        }, 100);

        // Redirect หลังจาก 2.5 วินาที
        setTimeout(function() {
            window.location.href = "index.php";
        }, 2600);
    };
</script>

</body>
</html>