// ส่วนของการ Update หลังจากรับค่าจาก Form
$new_password = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);
$student_id = $_SESSION['student_id'];

$sql = "UPDATE students SET password = ?, is_first_login = 0 WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $new_password, $student_id);
if($stmt->execute()) {
    echo "เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบอีกครั้ง";
}