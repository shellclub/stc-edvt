<?php
include "../config.php";

if(isset($_POST["query"])){
    $q = mysqli_real_escape_string($conn, trim($_POST["query"]));
    $output = '';

    // ตรวจสอบว่าเป็นตัวเลขล้วนหรือไม่ (รหัสนักศึกษา)
    if(ctype_digit($q)){
        // --- กรณีพิมพ์รหัส: ค้นหาเฉพาะนักศึกษา ---
        $sql_std = "SELECT student_id, fullname, group_name FROM students 
                    WHERE student_id LIKE '%$q%' 
                    LIMIT 8";
        $res_std = mysqli_query($conn, $sql_std);

        if(mysqli_num_rows($res_std) > 0){
            while($row = mysqli_fetch_array($res_std)){
                $output .= '<a href="teacher_view_report.php?sid='.$row["student_id"].'" class="list-group-item list-group-item-action">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle bg-primary text-white me-3" style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">'.$row["fullname"].'</div>
                                        <div class="small text-muted">รหัส: '.$row["student_id"].' | กลุ่ม: '.$row["group_name"].'</div>
                                    </div>
                                </div>
                            </a>';
            }
        } else {
            $output .= '<div class="list-group-item text-center py-3 text-muted small">ไม่พบรหัสนักศึกษานี้</div>';
        }

    } else {
        // --- กรณีพิมพ์ตัวอักษร: ค้นหาเฉพาะกลุ่มเรียน ---
        $sql_group = "SELECT DISTINCT group_name FROM students 
                      WHERE group_name LIKE '%$q%' 
                      LIMIT 5";
        $res_group = mysqli_query($conn, $sql_group);

        if(mysqli_num_rows($res_group) > 0){
            while($row = mysqli_fetch_array($res_group)){
                $output .= '<a href="teacher_student_list.php?gname='.urlencode($row["group_name"]).'" class="list-group-item list-group-item-action py-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="bi bi-collection-fill text-primary me-2"></i> 
                                        กลุ่มเรียน: <strong>'.$row["group_name"].'</strong>
                                    </span>
                                    <span class="badge bg-soft-primary text-primary rounded-pill border border-primary px-3">ดูรายชื่อทั้งกลุ่ม</span>
                                </div>
                            </a>';
            }
        } else {
            $output .= '<div class="list-group-item text-center py-3 text-muted small">ไม่พบกลุ่มเรียนที่ค้นหา</div>';
        }
    }
    
    echo $output;
}
?>