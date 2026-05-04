<?php
include "../config.php";

if(isset($_POST["query"])){
    $output = '';
    $query = mysqli_real_escape_string($conn, $_POST["query"]);
    $sql = "SELECT DISTINCT group_name FROM students WHERE group_name LIKE '%$query%' LIMIT 5";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) > 0){
        while($row = mysqli_fetch_array($result)){
            $output .= '<a href="teacher_student_list.php?gname='.urlencode($row["group_name"]).'" class="list-group-item list-group-item-action text-start p-3">
                            <i class="bi bi-people-fill me-2"></i> '.$row["group_name"].'
                        </a>';
        }
    } else {
        $output .= '<div class="list-group-item text-muted">ไม่พบข้อมูลกลุ่มนี้</div>';
    }
    echo $output;
}
?>