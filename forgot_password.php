<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน - ระบบรายงานฝึกงาน วท.สุพรรณบุรี</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --stc-crimson: #800000;
            --bg-gradient: linear-gradient(135deg, #800000 0%, #2a0000 100%);
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .kanit { font-family: 'Kanit', sans-serif; }

        .icon-header {
            width: 70px;
            height: 70px;
            background: #fff5f5;
            color: var(--stc-crimson);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 15px;
            background: #f8f9fa;
            border: 1px solid #e2e8f0;
        }

        .form-control:focus {
            box-shadow: 0 0 0 4px rgba(128, 0, 0, 0.1);
            border-color: var(--stc-crimson);
        }

        .btn-reset {
            background: var(--stc-crimson);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-reset:hover {
            background: #a00000;
            transform: translateY(-2px);
            color: white;
        }

        .back-to-login {
            text-decoration: none;
            color: #666;
            font-size: 0.9rem;
            transition: 0.3s;
        }

        .back-to-login:hover {
            color: var(--stc-crimson);
        }
    </style>
</head>
<body>

    <div class="container d-flex justify-content-center px-4">
        <div class="forgot-card text-center">
            <div class="icon-header">
                <i class="bi bi-key-fill"></i>
            </div>
            
            <h4 class="kanit fw-bold mb-2">ลืมรหัสผ่าน?</h4>
            <p class="text-muted small mb-4">ยืนยันตัวตนด้วยข้อมูลส่วนตัว เพื่อตั้งรหัสผ่านใหม่</p>

            <form action="forgot_password_process.php" method="POST" class="text-start">
                <div class="mb-3">
                    <label class="form-label fw-bold small">รหัสนักศึกษา</label>
                    <input type="text" name="student_id" class="form-control" placeholder="ระบุรหัส 11 หลักของคุณ" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">วัน/เดือน/ปีเกิด (พ.ศ.)</label>
                    <input type="text" name="birth_date" class="form-control" placeholder="วัน/เดือน/ปีเกิด" required>
                    <div class="small text-muted mt-2" style="font-size: 0.75rem;">
                        * ระบบจะตรวจสอบวันเกิดให้ตรงกับฐานข้อมูลก่อนอนุญาตให้เปลี่ยนรหัส
                    </div>
                </div>

                <button type="submit" class="btn btn-reset w-100 mt-3 kanit">
                    ตรวจสอบข้อมูล
                </button>
            </form>

            <div class="mt-4 pt-3 border-top">
                <a href="index.php" class="back-to-login">
                    <i class="bi bi-arrow-left me-1"></i> กลับไปหน้าเข้าสู่ระบบ
                </a>
            </div>
        </div>
    </div>

</body>
</html>