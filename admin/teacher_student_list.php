<?php
include "../config.php";
$gname = isset($_GET['gname']) ? mysqli_real_escape_string($conn, $_GET['gname']) : '';

if (empty($gname)) {
    header("Location: teacher_search.php");
    exit();
}

$sql = "SELECT * FROM students WHERE group_name = '$gname' ORDER BY student_id ASC";
$result = mysqli_query($conn, $sql);
$count = 0; // ตัวแปรสำหรับรันเลขลำดับ
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อนักศึกษา กลุ่ม: <?php echo $gname; ?></title>
    <!-- Fonts: Kanit & Sarabun -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-color: #4361ee;
            --bg-color: #f8faff;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg-color);
            color: #2d3436;
        }

        .kanit { font-family: 'Kanit', sans-serif; }

        /* Header Styling */
        .page-header {
            background: var(--primary-gradient);
            padding: 50px 0;
            color: white;
            border-radius: 0 0 40px 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        /* Card Styling */
        .student-card {
            background: white;
            border: none;
            border-radius: 25px;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            position: relative;
            overflow: visible; /* ให้ Badge ล้นออกมาได้เล็กน้อย */
            height: 100%;
        }

        .student-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        }

        /* Image Wrapper & Index Badge */
        .img-wrapper {
            position: relative;
            width: 100px;
            height: 120px;
            margin: -25px auto 15px; /* ดันรูปขึ้นไปด้านบน */
        }

        .std-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
            border: 4px solid white;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .index-badge {
            position: absolute;
            top: -10px;
            left: -10px;
            background: var(--accent-color);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Kanit', sans-serif;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.4);
            z-index: 2;
        }

        /* Content Styling */
        .card-body {
            padding: 10px 20px 25px;
            text-align: center;
        }

        .std-id {
            font-size: 0.85rem;
            color: #636e72;
            background: #f1f2f6;
            padding: 4px 12px;
            border-radius: 50px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .std-name {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: #2d3436;
        }

        .btn-action {
            background: #f8f9ff;
            color: var(--accent-color);
            border: 1px solid #e0e7ff;
            border-radius: 15px;
            padding: 10px 20px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }

        .btn-action:hover {
            background: var(--accent-color);
            color: white;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }

        /* Back Button */
        .btn-back {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 50px;
            padding: 8px 25px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: white;
            color: var(--accent-color);
        }
    </style>
</head>
<body>

<div class="page-header text-center">
    <div class="container px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="teacher_search.php" class="btn-back">
                <i class="bi bi-arrow-left me-2"></i> ค้นหาใหม่
            </a>
            <div class="text-white opacity-75 small">ระบบแสดงรายงานรายบุคคล</div>
        </div>
        <h1 class="kanit fw-bold display-5 mb-1"><?php echo $gname; ?></h1>
        <p class="mb-0 fs-5 opacity-75">พบนักศึกษาทั้งหมด <?php echo mysqli_num_rows($result); ?> ราย</p>
    </div>
</div>

<div class="container">
    <div class="row g-5">
        <?php while($row = mysqli_fetch_assoc($result)): $count++; ?>
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card student-card">
                <div class="img-wrapper">
                    <!-- แสดงเลขลำดับ -->
                    <div class="index-badge"><?php echo $count; ?></div>
                    <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $row['student_id']; ?>.jpg&x=200&f=0" 
                         class="std-img" 
                         alt="Student Photo"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['fullname']); ?>&background=random'">
                </div>
                
                <div class="card-body">
                    <span class="std-id fw-bold kanit"><?php echo $row['student_id']; ?></span>
                    <h5 class="std-name kanit"><?php echo $row['fullname']; ?></h5>
                    
                    <a href="teacher_view_report.php?sid=<?php echo $row['student_id']; ?>" 
                       target="_blank" 
                       class="btn btn-action">
                        <i class="bi bi-journal-text me-2"></i> ดูรายงานฝึกงาน
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<footer class="text-center py-5 mt-5 text-muted small">
    <div class="container">
        © 2026 วิทยาลัยเทคนิคสุพรรณบุรี | ระบบนิเทศฝึกงานอิเล็กทรอนิกส์
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>