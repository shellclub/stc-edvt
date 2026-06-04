<?php
session_start();
include "../config.php";

$gname = isset($_GET['gname']) ? mysqli_real_escape_string($conn, $_GET['gname']) : '';

if (empty($gname)) {
    header("Location: teacher_search.php");
    exit();
}

// SQL ดึงข้อมูลนักศึกษาในกลุ่ม พร้อมนับจำนวนรายงานจากตาราง internship_reports
$sql = "SELECT s.*, COUNT(r.report_id) AS total_reports 
        FROM students s 
        LEFT JOIN internship_reports r ON s.student_id = r.student_id 
        WHERE s.group_name = '$gname' 
        GROUP BY s.student_id 
        ORDER BY s.student_id ASC";
$result = mysqli_query($conn, $sql);

// สร้าง Array พักข้อมูลไว้สำหรับนำไปวนลูปแสดงในตาราง Modal หน้ารวม
$summary_data = [];
$count = 0; 
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อนักศึกษา กลุ่ม: <?php echo $gname; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --accent-color: #4361ee;
            --bg-color: #f8faff;
            --stc-gold: #d4af37;
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
            overflow: visible;
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
            margin: -25px auto 15px;
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
            margin-bottom: 10px;
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

        /* Buttons Header */
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
        .btn-back:hover { background: white; color: var(--accent-color); }

        .btn-summary {
            background: linear-gradient(135deg, #ffca28 0%, #ff9800 100%);
            border: none;
            color: #fff;
            font-weight: bold;
            border-radius: 50px;
            padding: 8px 25px;
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
            transition: 0.3s;
        }
        .btn-summary:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(255, 152, 0, 0.5); color: white; }
        
        .report-count-badge {
            font-size: 0.85rem;
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 600;
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
            <button class="btn btn-summary kanit" data-bs-toggle="modal" data-bs-target="#summaryGroupModal">
                <i class="bi bi-bar-chart-line-fill me-2"></i> ดูสรุปภาพรวมกลุ่ม
            </button>
        </div>
        <h1 class="kanit fw-bold display-5 mb-1"><?php echo $gname; ?></h1>
        <p class="mb-0 fs-5 opacity-75">พบนักศึกษาทั้งหมด <?php echo mysqli_num_rows($result); ?> ราย</p>
    </div>
</div>

<div class="container">
    <div class="row g-5">
        <?php while($row = mysqli_fetch_assoc($result)): 
            $count++; 
            $summary_data[] = $row; // บันทึกข้อมูลลง Array เพื่อส่งต่อให้ตารางสรุปในโครงสร้างด้านล่าง
        ?>
        <div class="col-xl-3 col-lg-4 col-md-6 mb-5">
            <div class="card student-card">
                <div class="img-wrapper">
                    <div class="index-badge"><?php echo $count; ?></div>
                    <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $row['student_id']; ?>.jpg&x=200&f=0" 
                         class="std-img" 
                         alt="Student Photo"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($row['fullname']); ?>&background=random'">
                </div>
                
                <div class="card-body">
                    <span class="std-id fw-bold kanit"><?php echo $row['student_id']; ?></span>
                    <h5 class="std-name kanit text-truncate"><?php echo $row['fullname']; ?></h5>
                    
                    <div class="mb-3">
                        <?php if($row['total_reports'] > 0): ?>
                            <span class="badge bg-success-subtle text-success report-count-badge">
                                <i class="bi bi-check-circle-fill me-1"></i> รายงานแล้ว <?php echo $row['total_reports']; ?> ครั้ง
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger report-count-badge">
                                <i class="bi bi-x-circle-fill me-1"></i> ยังไม่มีรายงาน
                            </span>
                        <?php endif; ?>
                    </div>
                    
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

<div class="modal fade" id="summaryGroupModal" tabindex="-1" aria-labelledby="summaryGroupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header border-0 bg-light p-4" style="border-radius: 25px 25px 0 0;">
                <div class="d-flex align-items-center">
                    <div class="p-2 bg-primary bg-opacity-10 text-primary rounded-3 me-3">
                        <i class="bi bi-clipboard-data-fill fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title kanit fw-bold text-dark" id="summaryGroupModalLabel">ตารางสรุปการส่งรายงาน</h5>
                        <small class="text-muted">กลุ่มการเรียน: <?php echo $gname; ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="kanit text-secondary" style="font-size: 0.9rem;">
                                <th class="text-center" style="width: 70px;">ลำดับ</th>
                                <th style="width: 140px;">รหัสนักศึกษา</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th class="text-center" style="width: 150px;">จำนวนครั้งที่รายงาน</th>
                                <th class="text-center" style="width: 100px;">ลิงก์ตรวจ</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 0.95rem;">
                            <?php 
                            $sum_idx = 0;
                            foreach($summary_data as $std): 
                                $sum_idx++;
                            ?>
                            <tr>
                                <td class="text-center fw-bold text-secondary"><?php echo $sum_idx; ?></td>
                                <td><span class="badge bg-light text-dark border px-2 py-2 fw-normal"><?php echo $std['student_id']; ?></span></td>
                                <td class="fw-bold text-dark"><?php echo $std['fullname']; ?></td>
                                <td class="text-center">
                                    <?php if($std['total_reports'] > 0): ?>
                                        <span class="badge bg-success px-3 py-2 rounded-pill fw-bold">
                                            <?php echo $std['total_reports']; ?> ครั้ง
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger px-3 py-2 rounded-pill fw-bold">
                                            0 ครั้ง
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="teacher_view_report.php?sid=<?php echo $std['student_id']; ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary rounded-3 px-2">
                                        <i class="bi bi-search"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light p-3" style="border-radius: 0 0 25px 25px;">
                <button type="button" class="btn btn-secondary rounded-3 px-4 shadow-sm" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
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