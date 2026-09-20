<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบผู้ดูแลและครู - วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <!-- Fonts: Kanit & Sarabun -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@500;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary-navy: #0d1b2a;
            --accent-blue: #1b4965;
            --highlight-cyan: #00b4d8;
            --glow-color: rgba(0, 180, 216, 0.35);
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: radial-gradient(circle at 20% 20%, #1e3c72 0%, #0d1b2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* เอฟเฟกต์แสงสะท้อนเบลอด้านหลัง (Aura Blobs) */
        .ambient-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            pointer-events: none;
            opacity: 0.6;
            animation: pulseGlow 8s infinite alternate ease-in-out;
        }
        .glow-1 {
            width: 380px;
            height: 380px;
            background: #0077b6;
            top: -50px;
            left: -80px;
        }
        .glow-2 {
            width: 320px;
            height: 320px;
            background: #00b4d8;
            bottom: -60px;
            right: -60px;
        }

        @keyframes pulseGlow {
            0% { transform: scale(1) translate(0, 0); opacity: 0.45; }
            100% { transform: scale(1.15) translate(20px, -20px); opacity: 0.7; }
        }

        /* การ์ดแก้วพรีเมียม (Glassmorphism Card) */
        .auth-card {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 28px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.4);
            width: 100%;
            max-width: 440px;
            padding: 42px 38px;
            position: relative;
            z-index: 1;
            animation: slideUpFade 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(30px) scale(0.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .kanit { font-family: 'Kanit', sans-serif; }

        /* ไอคอนหัวการ์ดพร้อมเงาเรืองแสง */
        .badge-icon {
            width: 78px;
            height: 78px;
            background: linear-gradient(135deg, #1b4965 0%, #0d1b2a 100%);
            color: #00b4d8;
            border-radius: 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 20px;
            box-shadow: 0 12px 24px rgba(13, 27, 42, 0.25), 0 0 20px var(--glow-color);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }
        .badge-icon:hover {
            transform: translateY(-4px) rotate(3deg);
        }

        .form-label {
            font-weight: 600;
            color: #2b2d42;
            font-size: 0.92rem;
            margin-bottom: 6px;
        }

        .input-group {
            border-radius: 14px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            transition: all 0.25s ease;
        }

        .input-group:focus-within {
            border-color: #0077b6;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(0, 119, 182, 0.12);
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #64748b;
            padding-left: 16px;
        }

        .form-control {
            border: none;
            background: transparent;
            padding: 13px 14px;
            font-size: 0.95rem;
            color: #1e293b;
        }
        .form-control:focus {
            box-shadow: none;
            background: transparent;
        }

        .btn-toggle-pwd {
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            padding-right: 14px;
            transition: color 0.2s;
        }
        .btn-toggle-pwd:hover {
            color: #1e293b;
        }

        /* ปุ่ม Login หลัก */
        .btn-submit {
            background: linear-gradient(135deg, #1b4965 0%, #0d1b2a 100%);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 13px;
            font-size: 1.05rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(13, 27, 42, 0.25);
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #0077b6 0%, #0d1b2a 100%);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(0, 119, 182, 0.35);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 22px 0;
            color: #94a3b8;
            font-size: 0.8rem;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }
        .divider:not(:empty)::before { margin-right: 12px; }
        .divider:not(:empty)::after { margin-left: 12px; }

        .btn-portal-link {
            border-radius: 12px;
            font-size: 0.88rem;
            padding: 9px 15px;
            text-decoration: none;
            color: #475569;
            background: #f1f5f9;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
        }
        .btn-portal-link:hover {
            background: #e2e8f0;
            color: #0d1b2a;
        }

        .footer-note {
            font-size: 0.78rem;
            color: #94a3b8;
            margin-top: 24px;
        }
    </style>
</head>
<body>

    <!-- แสงเรืองรองด้านหลัง (Ambient Lights) -->
    <div class="ambient-glow glow-1"></div>
    <div class="ambient-glow glow-2"></div>

    <div class="auth-card text-center">
        <!-- ไอคอนหัวข้อ -->
        <div class="badge-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <h3 class="kanit fw-bold text-dark mb-1">ระบบบริหารจัดการ</h3>
        <p class="text-muted small mb-4">สำหรับเจ้าหน้าที่งานทวิภาคี & ครูนิเทศก์</p>

        <!-- ฟอร์มเข้าสู่ระบบ -->
        <form action="admin_login_process.php" method="POST" class="text-start">
            <div class="mb-3">
                <label class="form-label">ชื่อผู้ใช้งาน (Username)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="กรอกชื่อผู้ใช้งาน" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">รหัสผ่าน (Password)</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                    <input type="password" name="password" id="adminPassword" class="form-control" placeholder="กรอกรหัสผ่าน" required>
                    <button type="button" class="btn-toggle-pwd" onclick="togglePasswordVisibility()" tabindex="-1">
                        <i class="bi bi-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-submit w-100 kanit">
                <i class="bi bi-box-arrow-in-right me-2"></i> เข้าสู่ระบบ
            </button>
        </form>

        <div class="divider">เมนูทางลัด</div>

        <!-- แถบปุ่มทางลัดด้านล่าง -->
        <div class="d-flex flex-column gap-2">
            <a href="teacher_search.php" class="btn-portal-link">
                <i class="bi bi-search me-2 text-primary"></i> ค้นหากลุ่มนักศึกษา (ไม่ต้องล็อกอิน)
            </a>
            <a href="../index.php" class="btn-portal-link">
                <i class="bi bi-mortarboard-fill me-2 text-danger"></i> ไปยังหน้านักศึกษาฝึกงาน
            </a>
        </div>

        <div class="footer-note">
            วิทยาลัยเทคนิคสุพรรณบุรี • แผนกอิเล็กทรอนิกส์<br>
            © 2026 Suphanburi Technical College
        </div>
    </div>

    <script>
        // ฟังก์ชันสลับแสดง/ซ่อนรหัสผ่าน
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('adminPassword');
            const icon = document.getElementById('toggleIcon');
            
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.className = 'bi bi-eye-slash-fill text-primary';
            } else {
                pwdInput.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    </script>
</body>
</html>