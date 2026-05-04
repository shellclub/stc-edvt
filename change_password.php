<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("location: index.php"); // ถ้าแอบเข้าหน้านี้โดยไม่ Login ให้เด้งกลับหน้าแรก
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เปลี่ยนรหัสผ่าน - ระบบฝึกงาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .setup-card { max-width: 450px; margin: auto; margin-top: 40px; border: none; border-radius: 20px; }
        .btn-save { background-color: #198754; color: white; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container p-3">
        <div class="card setup-card shadow">
            <div class="card-body p-4">
                <h4 class="text-center mb-4">ตั้งค่ารหัสผ่านใหม่</h4>
                <p class="text-muted small text-center">เนื่องจากเป็นการเข้าใช้งานครั้งแรก <br>กรุณากำหนดรหัสผ่านส่วนตัวเพื่อความปลอดภัย</p>
                
                <form action="update_pass_process.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">รหัสผ่านใหม่</label>
                        <input type="password" name="new_pass" class="form-control form-control-lg" placeholder="อย่างน้อย 6 ตัวอักษร" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" name="confirm_pass" class="form-control form-control-lg" placeholder="กรอกรหัสผ่านอีกครั้ง" required>
                    </div>
                    <button type="submit" class="btn btn-save w-100 py-3 mt-3">บันทึกและเริ่มใช้งาน</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>