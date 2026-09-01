<?php
include "../config.php";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบสืบค้นข้อมูล | สำหรับครูนิเทศ</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;500;700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --primary: #4361ee;
            --bg-gradient: linear-gradient(135deg, #1e3a8a 0%, #4361ee 100%);
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: #f4f7ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .search-container {
            background: white;
            border-radius: 30px;
            padding: 50px 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.08);
            max-width: 600px;
            margin: auto;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .icon-box {
            width: 70px;
            height: 70px;
            background: #eef2ff;
            color: var(--primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 25px;
        }

        .search-input {
            height: 60px;
            border-radius: 15px;
            padding: 0 25px 0 55px;
            border: 2px solid #e2e8f0;
            font-size: 1.1rem;
            transition: 0.3s;
        }

        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        .search-wrapper { position: relative; }
        .search-wrapper .bi-search {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.3rem;
        }

        #results_box {
            margin-top: 10px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: none;
        }

        .list-group-item {
            padding: 15px 20px;
            border: none;
            border-bottom: 1px solid #f1f5f9;
            text-align: left;
            transition: 0.2s;
        }

        .list-group-item:hover {
            background: #f8faff;
            color: var(--primary);
            padding-left: 30px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="search-container">
        <div class="icon-box shadow-sm">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2 class="fw-bold kanit mb-2">Teacher Access</h2>
        <p class="text-muted mb-4">ระบุรหัสนักศึกษา หรือ ชื่อกลุ่มการเรียน</p>

        <div class="search-wrapper">
            <input type="text" id="main_search" class="form-control search-input" 
                   placeholder="เช่น 66309010001 หรือ อเล็กฯ 1" autocomplete="off">
            <i class="bi bi-search"></i>
            
            <div id="results_box" class="list-group position-absolute w-100 z-3">
                <!-- ข้อมูลแสดงผ่าน AJAX -->
            </div>
        </div>

        <div class="mt-4 row g-2 small text-muted">
            <div class="col-6 text-start"><i class="bi bi-check2-circle text-success me-1"></i> กรอกรหัส เพื่อดูรายงานทันที</div>
            <div class="col-6 text-end"><i class="bi bi-check2-circle text-success me-1"></i> กรอกชื่อกลุ่ม เพื่อดูทั้งห้อง</div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $('#main_search').on('keyup', function(){
        let val = $(this).val().trim(); // ตัดช่องว่างออก
        
        if(val.length >= 2){ // พิมพ์ 2 ตัวขึ้นไปเริ่มค้นหาทันที
            $.ajax({
                url: "fetch_hybrid.php",
                method: "POST",
                data: {query: val},
                success: function(data){
                    $('#results_box').fadeIn(100).html(data);
                }
            });
        } else {
            $('#results_box').fadeOut(100);
        }
    });

    // ปิดกล่องผลลัพธ์เมื่อคลิกที่อื่น
    $(document).on('click', function(e){
        if(!$(e.target).closest('.search-wrapper').length){
            $('#results_box').fadeOut(100);
        }
    });
});
</script>

</body>
</html>