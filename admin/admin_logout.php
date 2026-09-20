<?php
session_start();

// 1. ล้างตัวแปรทั้งหมดใน Session
$_SESSION = array();

// 2. ลบคุกกี้ของ Session (ถ้ามี) เพื่อความปลอดภัยสูงสุด
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 3. ทำลาย Session
session_destroy();

// 4. ส่งกลับไปยังหน้าเข้าสู่ระบบของผู้ดูแลระบบ
header("location: index.php");
exit();
?>