<?php
session_start();
include "../config.php";

// ต้องล็อกอินและติดเงื่อนไขบังคับเปลี่ยนรหัสผ่านเท่านั้น
if (!isset($_SESSION['admin_id']) || empty($_SESSION['force_change_pwd'])) {
    header("location: admin_login.php");
    exit();
}

$error_msg = "";
$admin_id = $_SESSION['admin_id'];

if (isset($_POST['btn_change_pwd'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // กฎความปลอดภัย: อย่างน้อย 8 ตัว, มีพิมพ์ใหญ่, พิมพ์เล็ก, ตัวเลข
    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';

    if ($new_password !== $confirm_password) {
        $error_msg = "รหัสผ่านยืนยันไม่ตรงกัน กรุณากรอกใหม่อีกครั้ง";
    } else if (!preg_match($password_pattern, $new_password)) {
        $error_msg = "รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร และประกอบด้วยตัวพิมพ์ใหญ่ (A-Z), พิมพ์เล็ก (a-z) และตัวเลข (0-9)";
    } else {
        // เข้ารหัสด้วย password_hash
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $hashed_password_clean = mysqli_real_escape_string($conn, $hashed_password);

        // อัปเดตลงตาราง admins
        $update_sql = "UPDATE admins SET password = '$hashed_password_clean' WHERE admin_id = '$admin_id'";
        if (mysqli_query($conn, $update_sql)) {
            // ปลดล็อกเงื่อนไขบังคับเปลี่ยนรหัสผ่าน
            unset($_SESSION['force_change_pwd']);

            echo "<script>
                alert('เปลี่ยนรหัสผ่านปลอดภัยสำเร็จแล้ว กำลังเข้าสู่หน้าหลัก');
                window.location.href = '" . ($_SESSION['role'] === 'bilateral_officer' ? 'bilateral_dashboard.php' : 'teacher_dashboard.php') . "';
            </script>";
            exit();
        } else {
            $error_msg = "เกิดข้อผิดพลาดในการบันทึกข้อมูล: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บังคับเปลี่ยนรหัสผ่านเพื่อความปลอดภัย | DVT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; }
        .rule-item { font-size: 13px; color: #6c757d; }
        .rule-item.valid { color: #198754; font-weight: bold; }
    </style>
</head>
<body class="d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow border-0 rounded-4 p-4">
                <div class="text-center mb-4">
                    <div class="bg-warning-subtle text-warning d-inline-block p-3 rounded-circle mb-2">
                        <i class="bi bi-shield-lock-fill fs-1"></i>
                    </div>
                    <h4 class="fw-bold text-dark">ยกระดับความปลอดภัยของบัญชี</h4>
                    <p class="text-muted small">ระบบตรวจพบว่าบัญชีของคุณยังใช้รหัสผ่านแบบเดิมที่ยังไม่ได้เข้ารหัส กรุณาตั้งรหัสผ่านใหม่เพื่อความปลอดภัยในการเข้าใช้งาน</p>
                </div>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_msg; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="mb-3">
                        <label class="form-label fw-bold">รหัสผ่านใหม่ (New Password)</label>
                        <input type="password" name="new_password" id="new_password" class="form-control form-control-lg" required>
                    </div>

                    <!-- แถบแจ้งเตือนกฎความปลอดภัยแบบเรียลไทม์ -->
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <small class="fw-bold d-block mb-1 text-dark">ข้อกำหนดความปลอดภัยของรหัสผ่าน:</small>
                        <div id="rule-length" class="rule-item"><i class="bi bi-circle me-1"></i> ความยาวอย่างน้อย 8 ตัวอักษร</div>
                        <div id="rule-upper" class="rule-item"><i class="bi bi-circle me-1"></i> มีตัวอักษรพิมพ์ใหญ่ (A-Z) อย่างน้อย 1 ตัว</div>
                        <div id="rule-lower" class="rule-item"><i class="bi bi-circle me-1"></i> มีตัวอักษรพิมพ์เล็ก (a-z) อย่างน้อย 1 ตัว</div>
                        <div id="rule-number" class="rule-item"><i class="bi bi-circle me-1"></i> มีตัวเลข (0-9) อย่างน้อย 1 ตัว</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">ยืนยันรหัสผ่านใหม่ (Confirm Password)</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control form-control-lg" required>
                    </div>

                    <button type="submit" name="btn_change_pwd" id="submitBtn" class="btn btn-primary btn-lg w-100 shadow-sm" disabled>
                        บันทึกรหัสผ่านใหม่และเข้าสู่ระบบ
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('new_password');
    const confirmInput = document.getElementById('confirm_password');
    const submitBtn = document.getElementById('submitBtn');

    function checkRules() {
        const val = passwordInput.value;
        const confirmVal = confirmInput.value;

        const isLength = val.length >= 8;
        const isUpper = /[A-Z]/.test(val);
        const isLower = /[a-z]/.test(val);
        const isNumber = /[0-9]/.test(val);

        updateRule('rule-length', isLength);
        updateRule('rule-upper', isUpper);
        updateRule('rule-lower', isLower);
        updateRule('rule-number', isNumber);

        // ตรวจสอบว่าผ่านเงื่อนไขและรหัสผ่านตรงกันหรือไม่
        const isValid = isLength && isUpper && isLower && isNumber && (val === confirmVal && val !== '');
        submitBtn.disabled = !isValid;
    }

    function updateRule(elementId, isValid) {
        const el = document.getElementById(elementId);
        if (isValid) {
            el.className = 'rule-item valid';
            el.innerHTML = '<i class="bi bi-check-circle-fill me-1 text-success"></i>' + el.innerText.trim();
        } else {
            el.className = 'rule-item';
            el.innerHTML = '<i class="bi bi-circle me-1"></i>' + el.innerText.trim();
        }
    }

    passwordInput.addEventListener('input', checkRules);
    confirmInput.addEventListener('input', checkRules);
</script>
</body>
</html>