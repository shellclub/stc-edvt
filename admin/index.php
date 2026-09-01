<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการ - วิทยาลัยเทคนิคสุพรรณบุรี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
            background: #f0f2f5;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .admin-icon {
            width: 70px;
            height: 70px;
            background: #2c3e50;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 35px;
            margin: 0 auto 20px;
        }
        .btn-admin {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-admin:hover {
            background: #1a252f;
            color: white;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="admin-icon">
        <i class="bi bi-shield-lock-fill"></i>
    </div>
    <div class="text-center mb-4">
        <h4 class="fw-bold mb-1">เจ้าหน้าที่ & ครู</h4>
        <p class="text-muted small">ระบบบริหารจัดการการฝึกงาน</p>
    </div>

    <form action="admin_login_process.php" method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">ชื่อผู้ใช้งาน (Username)</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-secondary"></i></span>
                <input type="text" name="username" class="form-control border-start-0" placeholder="ระบุชื่อผู้ใช้งาน" required>
            </div>
        </div>
        
        <div class="mb-4">
            <label class="form-label small fw-bold">รหัสผ่าน (Password)</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-key text-secondary"></i></span>
                <input type="password" name="password" class="form-control border-start-0" placeholder="ระบุรหัสผ่าน" required>
            </div>
        </div>

        <button type="submit" class="btn btn-admin w-100 shadow-sm">
            <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
        </button>
    </form>

     
</div>

</body>
</html>