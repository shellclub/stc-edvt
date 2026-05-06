<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบรายงานฝึกงาน วท.สุพรรณบุรี</title>
    <!-- Fonts: Kanit & Sarabun -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --stc-crimson: #800000;
            --stc-gold: #d4af37;
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
            overflow: hidden;
        }

        /* ตกแต่งพื้นหลังด้วยวงกลมเบลอๆ (Modern Blur) */
        .bg-circle {
            position: fixed;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            filter: blur(50px);
            z-index: -1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 30px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInDown 0.8s ease-out;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-box {
            background: white;
            width: 90px;
            height: 90px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .kanit { font-family: 'Kanit', sans-serif; }

        .form-label {
            font-weight: 600;
            color: #444;
            font-size: 0.9rem;
            margin-left: 5px;
        }

        .input-group-text {
            background: transparent;
            border-right: none;
            color: #888;
            border-radius: 12px 0 0 12px;
        }

        .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 12px;
            background: #f8f9fa;
        }

        .form-control:focus {
            background: white;
            box-shadow: none;
            border-color: #dee2e6;
        }

        .btn-login {
            background: var(--stc-crimson);
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: 0.3s;
            color: white;
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #a00000;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(128, 0, 0, 0.2);
            color: white;
        }

        .forgot-password {
            text-decoration: none;
            color: #666;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .forgot-password:hover {
            color: var(--stc-crimson);
        }

        .footer-text {
            font-size: 0.75rem;
            color: #888;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>

    <div class="bg-circle" style="top: -100px; left: -100px;"></div>
    <div class="bg-circle" style="bottom: -100px; right: -100px;"></div>

    <div class="container d-flex justify-content-center px-4">
        <div class="login-card shadow-lg text-center">
            <div class="logo-box">
                <img src="image/icon_stc.jpg" width="70" alt="STC Logo">
            </div>
            
            <h4 class="kanit fw-bold mb-1">ระบบรายงานฝึกงาน</h4>
            <p class="text-muted small mb-4">วิทยาลัยเทคนิคสุพรรณบุรี</p>

            <form action="login_process.php" method="POST" class="text-start">
                <div class="mb-3">
                    <label class="form-label">รหัสนักศึกษา</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="ระบุรหัส 11 หลัก" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label">รหัสผ่าน / วันเกิด</label>
                        <a href="forgot_password.php" class="forgot-password">ลืมรหัสผ่าน?</a>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="ป้อนรหัสผ่านของคุณ" required>
                    </div>
                    <div class="small text-muted mt-2" style="font-size: 0.75rem;">
                        <i class="bi bi-info-circle me-1"></i> เข้าใช้ครั้งแรก ใช้ปีเกิด พ.ศ. (วัน/เดือน/ปีเกิด)
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100 mt-2 kanit">
                    <i class="bi bi-box-arrow-in-right me-2"></i> เข้าสู่ระบบ
                </button>
            </form>
                <a href="EDVT-STC.pdf" target="_blank" class="btn btn-outline-secondary w-100 mt-3 kanit shadow-sm" 
   style="border-radius: 12px; padding: 10px; border: 1px dashed #d4af37; color: #b8860b; background: #fffdf5;">
    <div class="d-flex align-items-center justify-content-center">
        <i class="bi bi-book-half me-2 fs-5"></i>
        <div class="text-start">
            <div class="fw-bold" style="font-size: 0.85rem; line-height: 1;">คู่มือการใช้งานระบบ</div>
            <small style="font-size: 0.7rem; opacity: 0.8;">คลิกเพื่อเปิดไฟล์เอกสาร PDF</small>
        </div>
    </div>
</a>
            <div class="footer-text">
                <div class="fw-bold mb-1">แผนกอิเล็กทรอนิกส์</div>
                © 2026 Suphanburi Technical College
            </div>
        </div>
    </div>

</body>
</html>