<?php
session_start();
include "../config.php";

// ตรวจสอบสิทธิ์ Admin ทวิภาคี[cite: 2]
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

// ดึงสรุปกลุ่มการเรียน, รหัสกลุ่ม, ครูที่ปรึกษา พร้อมจำนวนนักศึกษา[cite: 2]
$sql = "SELECT 
            group_code, 
            group_name, 
            advisor_name,
            COUNT(student_id) as total_students 
        FROM students 
        WHERE group_name IS NOT NULL AND group_name != ''
        GROUP BY group_name, group_code, advisor_name
        ORDER BY group_code ASC, group_name ASC";
$query = mysqli_query($conn, $sql);

$total_groups = mysqli_num_rows($query);
$sum_all_students = 0;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปกลุ่มการเรียน | งานทวิภาคี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; --secondary: #3f51b5; }
        body { 
            font-family: 'Sarabun', sans-serif; 
            background-color: #f4f7fa; 
            color: #000;
        }

        /* ------------------ หน้าจอปกติ (Screen View) ------------------ */
        @media screen {
            body {
                height: 100vh; 
                overflow: hidden;
            }
            .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; }
            .nav-link { color: rgba(255,255,255,0.7); border-radius: 10px; padding: 10px 14px; margin-bottom: 6px; }
            .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }

            .main-container {
                height: 100vh;
                display: flex;
                flex-direction: column;
                padding: 20px 30px;
            }

            .table-responsive-box {
                flex-grow: 1;
                overflow-y: auto;
                border-radius: 12px;
                background: #fff;
            }

            .table-sticky thead th {
                position: sticky;
                top: 0;
                background-color: #f8f9fa !important;
                z-index: 2;
                border-bottom: 2px solid #dee2e6;
            }

            .print-only-header, .print-only-footer {
                display: none;
            }
        }

        /* ------------------ โหมดสั่งพิมพ์ (Print View) ------------------ */
        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm 12mm 15mm 12mm;
            }
            body { 
                background: white !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                overflow: visible !important;
                height: auto !important;
            }
            .no-print { 
                display: none !important; 
            }
            .main-container {
                padding: 0 !important;
                height: auto !important;
                display: block !important;
            }
            .table-responsive-box {
                overflow: visible !important;
                border: none !important;
                box-shadow: none !important;
            }
            .table-custom {
                width: 100%;
                border-collapse: collapse;
            }
            .table-custom th, .table-custom td {
                border: 1px solid #000 !important;
                padding: 6px 8px;
                font-size: 13px;
            }
            .table-custom th {
                background-color: #f2f2f2 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                text-align: center;
            }
            tr { 
                page-break-inside: avoid !important; 
                break-inside: avoid !important; 
            }
            thead { 
                display: table-header-group; 
            }
            .print-only-header {
                display: block !important;
                text-align: center;
                margin-bottom: 20px;
            }
            .print-only-footer {
                display: block !important;
                margin-top: 30px;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }
        }

        .stat-badge {
            background-color: #e8eaf6;
            color: var(--primary);
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar (ซ่อนเมื่อสั่งพิมพ์) -->
        <div class="col-md-2 sidebar d-none d-md-block no-print">
            <div class="py-3 text-center">
                <div class="bg-white p-2 rounded-circle d-inline-block mb-2 shadow-sm">
                    <img src="https://upload.wikimedia.org/wikipedia/th/d/d4/Vec_Logo.png" width="45" alt="Logo">
                </div>
                <h6 class="fw-bold mb-0">ระบบงานทวิภาคี</h6>
                <small class="opacity-50">วท.สุพรรณบุรี</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link active" href="bilateral_groups_summary.php"><i class="bi bi-table me-2"></i> สรุปกลุ่มการเรียน</a>
                <a class="nav-link" href="bilateral_groups.php"><i class="bi bi-grid me-2"></i> มุมมองการ์ดกลุ่ม</a>
                <a class="nav-link" href="company_list.php"><i class="bi bi-building me-2"></i> สถานประกอบการ</a>
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <hr class="mx-2 my-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10">
            <div class="main-container">
                
                <!-- หัวเรื่องเฉพาะตอนสั่งพิมพ์ (แสดงผลตอน Print เท่านั้น) -->
                <div class="print-only-header">
                    <h4 class="fw-bold mb-1">รายงานสรุปกลุ่มนักศึกษาฝึกประสบการณ์วิชาชีพ</h4>
                    <h5 class="fw-bold mb-1">วิทยาลัยเทคนิคสุพรรณบุรี</h5>
                    <div class="small text-muted">
                        ข้อมูล ณ วันที่ <?php echo date('d/m/') . (date('Y') + 543); ?> | จำนวนกลุ่มทั้งหมด: <?php echo number_format($total_groups); ?> กลุ่ม
                    </div>
                </div>

                <!-- Header หน้าจอปกติ (ซ่อนตอนพิมพ์) -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 no-print">
                    <div>
                        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-card-checklist me-2 text-primary"></i>สรุปกลุ่มนักศึกษาฝึกงาน</h4>
                        <small class="text-muted">ตรวจสอบรายชื่อกลุ่ม ค้นหา และพิมพ์รายงานสรุป</small>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="stat-badge">
                            <i class="bi bi-collection me-1"></i> <?php echo number_format($total_groups); ?> กลุ่ม
                        </span>
                        <!-- ปุ่มสั่งพิมพ์ -->
                        <button onclick="window.print()" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm">
                            <i class="bi bi-printer-fill me-1"></i> สั่งพิมพ์รายงาน (PDF)
                        </button>
                        <a href="bilateral_groups.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="bi bi-grid-fill me-1"></i> แบบการ์ด
                        </a>
                    </div>
                </div>

                <!-- กล่องค้นหา (ซ่อนตอนพิมพ์) -->
                <div class="card shadow-sm border-0 rounded-4 mb-3 no-print">
                    <div class="card-body p-2 px-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-search fs-5"></i></span>
                            <input type="text" id="searchInput" class="form-control border-0 shadow-none ps-1" 
                                   placeholder="พิมพ์ค้นหาด้วย รหัสกลุ่ม, ชื่อกลุ่ม หรือชื่อครูที่ปรึกษา...">
                            <button class="btn btn-light border-0 text-muted" type="button" onclick="document.getElementById('searchInput').value=''; filterGroups();">
                                <i class="bi bi-x-circle"></i> ล้างคำค้น
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ตารางสรุปข้อมูล -->
                <div class="table-responsive-box shadow-sm border">
                    <table class="table table-hover align-middle mb-0 table-sticky table-custom" id="groupTable">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 6%;">ลำดับ</th>
                                <th style="width: 18%;">รหัสกลุ่ม</th>
                                <th style="width: 32%;">ชื่อกลุ่มการเรียน</th>
                                <th style="width: 26%;">ครูที่ปรึกษา</th>
                                <th class="text-center" style="width: 18%;">จำนวนนักศึกษา</th>
                                <th class="text-center no-print" style="width: 15%;">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($total_groups > 0):
                                while ($row = mysqli_fetch_assoc($query)): 
                                    $sum_all_students += $row['total_students'];
                            ?>
                            <tr class="group-row">
                                <td class="text-center text-muted"><?php echo $no++; ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1">
                                        <?php echo htmlspecialchars($row['group_code'] ?: '-'); ?>
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-dark group-name-text"><?php echo htmlspecialchars($row['group_name']); ?></strong>
                                </td>
                                <td>
                                    <small class="text-muted"><i class="bi bi-person me-1 no-print"></i><?php echo htmlspecialchars($row['advisor_name'] ?: '-'); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold">
                                        <?php echo number_format($row['total_students']); ?> คน
                                    </span>
                                </td>
                                <td class="text-center no-print">
                                    <a href="bilateral_student_list.php?gname=<?php echo urlencode($row['group_name']); ?>" 
                                       class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        รายชื่อ <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile;
                            ?>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="4" class="text-end pe-3">รวมนักศึกษาทั้งหมด:</td>
                                <td class="text-center text-primary"><?php echo number_format($sum_all_students); ?> คน</td>
                                <td class="no-print"></td>
                            </tr>
                            <?php else: ?>
                            <tr id="noDataRow">
                                <td colspan="6" class="text-center text-muted py-5">
                                    ไม่พบข้อมูลกลุ่มการเรียนในระบบ
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr id="noMatchRow" style="display: none;">
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-search me-1"></i> ไม่พบกลุ่มที่ตรงกับคำค้นหา
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ท้ายกระดาษเฉพาะตอนสั่งพิมพ์ (ช่องลงชื่อ) -->
                <div class="print-only-footer">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td style="width: 50%; text-align: center; border: none !important;"></td>
                            <td style="width: 50%; text-align: center; border: none !important; font-size: 13px;">
                                ลงชื่อ......................................................................<br>
                                ( ...................................................................... )<br>
                                เจ้าหน้าที่งานทวิภาคี / ผู้จัดทำรายงาน<br>
                                วันที่ ........ / ........ / ................
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- สรุปผลล่างตารางหน้าจอปกติ (ซ่อนตอนพิมพ์) -->
                <div class="d-flex justify-content-between align-items-center mt-2 px-2 small text-muted no-print">
                    <div>แสดงผล <span id="visibleCount" class="fw-bold text-dark"><?php echo $total_groups; ?></span> กลุ่ม</div>
                    <div>รวมทั้งหมด: <strong class="text-primary"><?php echo number_format($sum_all_students); ?></strong> คน</div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function filterGroups() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase().trim();
        const rows = document.querySelectorAll('#groupTable tbody tr.group-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (text.includes(filter)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        document.getElementById('visibleCount').innerText = visibleCount;
        const noMatchRow = document.getElementById('noMatchRow');
        if (noMatchRow) {
            noMatchRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    document.getElementById('searchInput').addEventListener('input', filterGroups);
</script>
</body>
</html>