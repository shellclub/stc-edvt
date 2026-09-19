<?php
session_start();
include "../config.php";

// ตรวจสอบสิทธิ์ Admin ทวิภาคี
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

if (!empty($_SESSION['force_change_pwd'])) {
    header("location: force_change_password.php");
    exit();
}

$alert_msg = "";
$alert_type = "";
$current_admin_id = $_SESSION['admin_id'];

// -------------------------------------------------------------
// 1. ระบบเปลี่ยนรหัสผ่าน (แก้ของตนเอง หรือ แก้ของครูเท่านั้น)
// -------------------------------------------------------------
if (isset($_POST['btn_reset_password'])) {
    $target_id = intval($_POST['target_admin_id']);
    $new_password = trim($_POST['new_password']);

    // ดึงข้อมูลเป้าหมายเพื่อตรวจสอบสิทธิ์
    $check_target = mysqli_query($conn, "SELECT admin_id, role, fullname FROM admins WHERE admin_id = '$target_id'");
    $target_data = mysqli_fetch_assoc($check_target);

    if ($target_data) {
        // เงื่อนไขความปลอดภัย: อนุญาตถ้าเป็นบัญชีของตนเอง หรือ บัญชีนั้นเป็นครู (teacher)
        // ห้ามแก้บัญชีที่เป็น Admin คนอื่น[cite: 2]
        if ($target_id == $current_admin_id || $target_data['role'] === 'teacher') {
            if (strlen($new_password) >= 6) {
                $hashed_new_pwd = password_hash($new_password, PASSWORD_DEFAULT);
                $hashed_new_pwd = mysqli_real_escape_string($conn, $hashed_new_pwd);

                $sql_update = "UPDATE admins SET password = '$hashed_new_pwd' WHERE admin_id = '$target_id'";
                if (mysqli_query($conn, $sql_update)) {
                    $alert_type = "success";
                    $alert_msg = "เปลี่ยนรหัสผ่านของ " . htmlspecialchars($target_data['fullname']) . " เรียบร้อยแล้ว";
                } else {
                    $alert_type = "danger";
                    $alert_msg = "เกิดข้อผิดพลาดในการบันทึก: " . mysqli_error($conn);
                }
            } else {
                $alert_type = "warning";
                $alert_msg = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
            }
        } else {
            $alert_type = "danger";
            $alert_msg = "ปฏิเสธการทำงาน: คุณไม่มีสิทธิ์เปลี่ยนรหัสผ่านของผู้ดูแลระบบท่านอื่น!";
        }
    }
}

// -------------------------------------------------------------
// 2. ระบบเพิ่มบัญชี Admin หรือ ครู
// -------------------------------------------------------------
if (isset($_POST['btn_add_user'])) {
    $fullname = mysqli_real_escape_string($conn, trim($_POST['fullname']));
    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = trim($_POST['password']);
    $role     = mysqli_real_escape_string($conn, trim($_POST['role']));

    if (!empty($fullname) && !empty($username) && !empty($password) && !empty($role)) {
        $check_user = mysqli_query($conn, "SELECT admin_id FROM admins WHERE username = '$username'");
        if (mysqli_num_rows($check_user) > 0) {
            $alert_type = "danger";
            $alert_msg = "ชื่อผู้ใช้งานนี้มีอยู่ในระบบแล้ว กรุณาใช้ชื่ออื่น";
        } else if (strlen($password) < 6) {
            $alert_type = "warning";
            $alert_msg = "รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $hashed_password = mysqli_real_escape_string($conn, $hashed_password);

            $sql_insert = "INSERT INTO admins (username, password, fullname, role) 
                           VALUES ('$username', '$hashed_password', '$fullname', '$role')";
            if (mysqli_query($conn, $sql_insert)) {
                $alert_type = "success";
                $role_th = ($role === 'bilateral_officer') ? 'เจ้าหน้าที่ทวิภาคี (Admin)' : 'ครูนิเทศก์/อาจารย์';
                $alert_msg = "เพิ่มบัญชีผู้ใช้ {$role_th} เรียบร้อยแล้ว!";
            } else {
                $alert_type = "danger";
                $alert_msg = "เกิดข้อผิดพลาด: " . mysqli_error($conn);
            }
        }
    }
}

// -------------------------------------------------------------
// ดึงข้อมูลสถิติ
// -------------------------------------------------------------
$row_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM students"));
$row_groups = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT group_name) as total_g FROM students WHERE group_name != ''"));
$total_officers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM admins WHERE role = 'bilateral_officer'"))['total'];
$total_teachers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM admins WHERE role = 'teacher'"))['total'];

$query_users = mysqli_query($conn, "SELECT admin_id, username, fullname, role FROM admins ORDER BY role ASC, admin_id DESC");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DVT Dashboard | วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #1a237e; --secondary: #3f51b5; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; color: #333; }
        
        .sidebar { background: var(--primary); min-height: 100vh; color: white; padding: 20px; }
        .nav-link { color: rgba(255,255,255,0.7); border-radius: 12px; padding: 12px 15px; margin-bottom: 8px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.15); color: white; }
        
        .stat-card { border: none; border-radius: 20px; transition: 0.3s; }
        .icon-box { width: 50px; height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        
        .action-card { border: 1px dashed #cbd5e0; border-radius: 20px; cursor: pointer; transition: 0.3s; height: 100%; background: #fff; }
        .action-card:hover { border-color: var(--secondary); background: #f0f4ff; transform: translateY(-3px); }

        .profile-avatar-img { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
        .avatar-admin-frame { border: 2px solid #0d6efd; background-color: #e7f1ff; }
        .avatar-teacher-frame { border: 2px solid #198754; background-color: #e8f5e9; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar d-none d-md-block sticky-top">
            <div class="py-4 text-center">
                <div class="bg-white p-2 rounded-circle d-inline-block mb-3 shadow-sm border" style="width: 75px; height: 75px; display: inline-flex; align-items: center; justify-content: center;">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'bilateral_officer'): ?>
                        <img src="https://api.dicebear.com/7.x/bottts/svg?seed=AdminOfficer&backgroundColor=b6e3f4" 
                             width="55" height="55" class="rounded-circle" alt="Admin Logo"
                             onerror="this.outerHTML='<i class=\'bi bi-shield-shaded text-primary fs-1\'></i>';">
                    <?php else: ?>
                        <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=TeacherProfile&backgroundColor=c0aede" 
                             width="55" height="55" class="rounded-circle" alt="Teacher Logo"
                             onerror="this.outerHTML='<i class=\'bi bi-mortarboard-fill text-success fs-1\'></i>';">
                    <?php endif; ?>
                </div>
                <h6 class="fw-bold mb-0">ระบบงานทวิภาคี</h6>
                <small class="badge bg-primary mt-1">ผู้ดูแลระบบ (Admin)</small>
            </div>
            <nav class="nav flex-column mt-3">
                <a class="nav-link active" href="bilateral_dashboard.php"><i class="bi bi-house-door me-2"></i> หน้าหลัก</a>
                <a class="nav-link" href="bilateral_groups_summary.php"><i class="bi bi-people me-2"></i> จัดการกลุ่ม</a>
                <a class="nav-link" href="company_list.php"><i class="bi bi-building me-2"></i> สถานประกอบการ</a>
                <a class="nav-link" href="company_map_overview.php"><i class="bi bi-geo-alt me-2"></i> แผนที่พิกัดรวม</a>
                <a class="nav-link" href="import_excel.php"><i class="bi bi-file-earmark-excel me-2"></i> นำเข้า Excel</a>
                <a class="nav-link text-danger" href="delete_students_by_group.php"><i class="bi bi-trash3 me-2"></i> ลบข้อมูลกลุ่ม</a>
                <hr class="mx-2">
                <a class="nav-link text-warning" href="admin_logout.php"><i class="bi bi-power me-2"></i> ออกจากระบบ</a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 p-md-5 p-4">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=<?php echo urlencode($_SESSION['admin_name']); ?>&backgroundColor=b6e3f4" 
                         class="profile-avatar-img avatar-admin-frame me-3" alt="Admin Profile">
                    <div>
                        <h2 class="fw-bold mb-0 text-dark">ภาพรวมระบบทวิภาคี</h2>
                        <span class="text-muted">
                            ผู้ดูแลระบบ: <strong class="text-primary"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></strong>
                        </span>
                    </div>
                </div>
                <div class="text-end d-none d-sm-block">
                    <div class="fw-bold h5 mb-0" id="liveTime">00:00:00</div>
                    <small class="text-muted"><?php echo date('d/m/') . (date('Y') + 543); ?></small>
                </div>
            </div>

            <?php if (!empty($alert_msg)): ?>
                <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i> <?php echo $alert_msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Overview -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-primary-subtle text-primary me-3"><i class="bi bi-person-workspace"></i></div>
                            <div>
                                <small class="text-muted d-block">นักศึกษาฝึกงานทั้งหมด</small>
                                <span class="h4 fw-bold mb-0"><?php echo number_format($row_total['total']); ?> คน</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success-subtle text-success me-3"><i class="bi bi-grid-3x3-gap"></i></div>
                            <div>
                                <small class="text-muted d-block">กลุ่มการเรียนในระบบ</small>
                                <span class="h4 fw-bold mb-0"><?php echo number_format($row_groups['total_g']); ?> กลุ่ม</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-info-subtle text-info me-3"><i class="bi bi-shield-lock-fill"></i></div>
                            <div>
                                <small class="text-muted d-block">เจ้าหน้าที่ Admin</small>
                                <span class="h4 fw-bold mb-0"><?php echo number_format($total_officers); ?> คน</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card p-3 shadow-sm">
                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-success-subtle text-success me-3"><i class="bi bi-mortarboard-fill"></i></div>
                            <div>
                                <small class="text-muted d-block">ครูนิเทศก์/อาจารย์</small>
                                <span class="h4 fw-bold mb-0"><?php echo number_format($total_teachers); ?> คน</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <h5 class="fw-bold mb-3">เข้าถึงเมนูอย่างรวดเร็ว</h5>
            <div class="row g-3 mb-5">
                <div class="col-md-3">
                    <div class="card action-card p-3 text-center" onclick="location.href='bilateral_groups_summary.php'">
                        <i class="bi bi-table fs-2 mb-2 text-primary"></i>
                        <h6 class="fw-bold mb-1">สรุปข้อมูลกลุ่ม</h6>
                        <small class="text-muted">ค้นหาและพิมพ์รายงานกลุ่ม</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card action-card p-3 text-center" onclick="location.href='company_map_overview.php'">
                        <i class="bi bi-geo-alt-fill fs-2 mb-2 text-danger"></i>
                        <h6 class="fw-bold mb-1">แผนที่พิกัดรวม</h6>
                        <small class="text-muted">ดูตำแหน่งสถานประกอบการ</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card action-card p-3 text-center" onclick="location.href='company_list.php'">
                        <i class="bi bi-building fs-2 mb-2 text-info"></i>
                        <h6 class="fw-bold mb-1">สถานประกอบการ</h6>
                        <small class="text-muted">ข้อมูลและพิมพ์รายงาน PDF/Excel</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card action-card p-3 text-center border-primary bg-primary-subtle" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus-fill fs-2 mb-2 text-primary"></i>
                        <h6 class="fw-bold mb-1 text-primary">เพิ่ม Admin / ครู</h6>
                        <small class="text-muted">สร้างบัญชีผู้ใช้งานใหม่</small>
                    </div>
                </div>
            </div>

            <!-- ตารางรายชื่อ Admin และ ครูในระบบ พร้อมปุ่มจัดการรหัสผ่าน -->
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-person-badge-fill me-2 text-primary"></i>บัญชีผู้ใช้งานระบบ
                    </h5>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-lg me-1"></i> เพิ่มผู้ใช้งาน
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4" style="width: 8%;">รูป</th>
                                    <th style="width: 32%;">ชื่อ - นามสกุล</th>
                                    <th style="width: 25%;">ชื่อผู้ใช้งาน (Username)</th>
                                    <th class="text-center" style="width: 20%;">บทบาท (Role)</th>
                                    <th class="text-center" style="width: 15%;">จัดการรหัสผ่าน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($u = mysqli_fetch_assoc($query_users)): ?>
                                <tr>
                                    <td class="ps-4">
                                        <?php if ($u['role'] === 'bilateral_officer'): ?>
                                            <img src="https://api.dicebear.com/7.x/bottts/svg?seed=<?php echo urlencode($u['username']); ?>&backgroundColor=b6e3f4" 
                                                 class="profile-avatar-img avatar-admin-frame" alt="Admin Avatar">
                                        <?php else: ?>
                                            <img src="https://api.dicebear.com/7.x/adventurer/svg?seed=<?php echo urlencode($u['username']); ?>&backgroundColor=c0aede" 
                                                 class="profile-avatar-img avatar-teacher-frame" alt="Teacher Avatar">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['fullname']); ?></div>
                                        <?php if ($u['admin_id'] == $current_admin_id): ?>
                                            <span class="badge bg-secondary-subtle text-secondary small">บัญชีของคุณ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($u['username']); ?></code>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($u['role'] === 'bilateral_officer'): ?>
                                            <span class="badge bg-primary rounded-pill px-3 py-1">
                                                <i class="bi bi-shield-fill-check me-1"></i> Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success rounded-pill px-3 py-1">
                                                <i class="bi bi-mortarboard-fill me-1"></i> ครู
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <!-- เช็คเงื่อนไข: แสดงปุ่มเปลี่ยนรหัสเฉพาะตนเอง หรือบัญชีที่เป็นครู (ห้ามแก้ Admin คนอื่น) -->
                                        <?php if ($u['admin_id'] == $current_admin_id || $u['role'] === 'teacher'): ?>
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" 
                                                    onclick="openResetModal('<?php echo $u['admin_id']; ?>', '<?php echo htmlspecialchars($u['fullname']); ?>', '<?php echo ($u['admin_id'] == $current_admin_id ? 'ตนเอง' : 'ครู'); ?>')">
                                                <i class="bi bi-key-fill me-1"></i> เปลี่ยนรหัส
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small" title="ไม่มีสิทธิ์แก้ไขรหัสผ่านของผู้ดูแลท่านอื่น">
                                                <i class="bi bi-lock-fill"></i> ล็อก
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= Modal: เปลี่ยนรหัสผ่าน ================= -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-key-fill me-2"></i>เปลี่ยนรหัสผ่าน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post">
                <input type="hidden" name="target_admin_id" id="modal_target_id">
                <div class="modal-body p-4">
                    <p class="mb-3">กำลังเปลี่ยนรหัสผ่านให้: <strong id="modal_target_name" class="text-dark"></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">กำหนดรหัสผ่านใหม่</label>
                        <input type="password" name="new_password" class="form-control form-control-lg" placeholder="อย่างน้อย 6 ตัวอักษร" required minlength="6">
                    </div>
                    <small class="text-muted d-block">
                        <i class="bi bi-shield-check text-success me-1"></i>รหัสผ่านใหม่จะถูกเข้ารหัสด้วย Hash เพื่อความปลอดภัย
                    </small>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="btn_reset_password" class="btn btn-danger px-4 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> บันทึกรหัสผ่านใหม่
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= Modal: เพิ่มบัญชี Admin หรือ ครู ================= -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>เพิ่มบัญชีผู้ใช้งาน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="post">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ระดับสิทธิ์ (Role)</label>
                        <select name="role" class="form-select form-select-lg" required>
                            <option value="">-- กรุณาเลือกบทบาท --</option>
                            <option value="teacher">👨‍🏫 ครูนิเทศก์ / อาจารย์ (Teacher)</option>
                            <option value="bilateral_officer">🛡️ เจ้าหน้าที่งานทวิภาคี (Admin)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อ - นามสกุล</label>
                        <input type="text" name="fullname" class="form-control" placeholder="เช่น อ.สมศักดิ์ รักเรียน" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ชื่อผู้ใช้งาน (Username)</label>
                        <input type="text" name="username" class="form-control" placeholder="เช่น teacher01" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">รหัสผ่านเริ่มต้น</label>
                        <input type="password" name="password" class="form-control" placeholder="อย่างน้อย 6 ตัวอักษร" required minlength="6">
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" name="btn_add_user" class="btn btn-primary px-4 shadow-sm">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function updateTime() {
        const now = new Date();
        document.getElementById('liveTime').innerText = now.toLocaleTimeString('th-TH');
    }
    setInterval(updateTime, 1000);
    updateTime();

    // เปิด Modal เปลี่ยนรหัสผ่านพร้อมส่งค่า ID และชื่อ
    function openResetModal(id, name, type) {
        document.getElementById('modal_target_id').value = id;
        document.getElementById('modal_target_name').innerText = name + ' (' + type + ')';
        new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
    }
</script>
</body>
</html>