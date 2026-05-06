<?php
session_start();
include "config.php";

if (!isset($_SESSION['student_id'])) { 
    header("location: index.php"); 
    exit(); 
}
$sid = $_SESSION['student_id'];

// ตั้งค่าตัวแปรเบื้องต้น
$log_file = "logs/checkin_" . $sid . ".json";
$today_str = date('Y-m-d');
$today_in = "-";
$today_out = "-";

// ดึงข้อมูลวันนี้จาก JSON
if (file_exists($log_file)) {
    $json_temp = json_decode(file_get_contents($log_file), true);
    if (isset($json_temp[$today_str])) {
        $today_in = isset($json_temp[$today_str]['check_in']['time']) ? substr($json_temp[$today_str]['check_in']['time'], 0, 5) : "-";
        $today_out = isset($json_temp[$today_str]['check_out']['time']) ? substr($json_temp[$today_str]['check_out']['time'], 0, 5) : "-";
    }
}

// ฟังก์ชันแปลงวันที่เป็นไทย
function dateThaiTable($strDate) {
    $strYear = substr(date("Y", strtotime($strDate)) + 543, 2);
    $strMonth = date("n", strtotime($strDate));
    $strDay = date("j", strtotime($strDate));
    $strMonthCut = Array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
    return "$strDay $strMonthCut[$strMonth] $strYear";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงเวลาปฏิบัติงาน - วท.สุพรรณบุรี</title>
    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root { --stc-crimson: #800000; --stc-gold: #d4af37; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f2f5; padding-bottom: 30px; }
        .kanit { font-family: 'Kanit', sans-serif; }
        
        /* Header Section */
        .header-box { background: linear-gradient(135deg, var(--stc-crimson) 0%, #4a0000 100%); color: white; padding: 40px 0 90px 0; border-radius: 0 0 40px 40px; position: relative; }
        
        /* Profile Image from RMS */
        .profile-img-box { position: absolute; right: 20px; top: 35px; width: 60px; height: 75px; border: 3px solid white; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 15px rgba(0,0,0,0.2); }
        .profile-img-box img { width: 100%; height: 100%; object-fit: cover; }

        /* Main Card Layout */
        .main-card { background: white; border-radius: 30px; margin-top: -75px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.08); overflow: hidden; position: relative; z-index: 10; }
        #map { height: 220px; width: 100%; filter: saturate(1.2); }

        /* Time & Status */
        .status-pill { display: inline-block; padding: 6px 18px; border-radius: 50px; font-size: 0.75rem; margin-bottom: 15px; background: #fff5f5; border: 1px solid #ffebeb; color: var(--stc-crimson); font-weight: 600; }
        .time-display { font-size: 3.5rem; font-weight: 700; color: #2d3436; letter-spacing: -2px; margin: 0; }

        /* Modern Action Buttons */
        .btn-action-group { padding: 30px; background: #fff; }
        .btn-custom { border-radius: 20px; padding: 18px; font-weight: 600; font-size: 1.1rem; border: none; transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: flex; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.05); color: white; margin-bottom: 15px; width: 100%; }
        .btn-custom:active { transform: scale(0.95); }
        .btn-check-in { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
        .btn-check-out { background: linear-gradient(135deg, #dc3545 0%, #b21f2d 100%); }

        /* Summary Cards */
        .summary-box { background: white; border-radius: 20px; padding: 15px; border: 1px solid #eee; text-align: center; height: 100%; }
        .history-table th { font-size: 0.75rem; text-transform: uppercase; color: #888; background: #fcfcfd; }
    </style>
</head>
<body>

<div class="header-box">
    <div class="container d-flex align-items-center">
        <a href="dashboard.php" class="text-white me-3 text-decoration-none"><i class="bi bi-arrow-left-short fs-1"></i></a>
        <div>
            <h4 class="mb-0 kanit fw-bold">ลงเวลาปฏิบัติงาน</h4>
            <small class="opacity-75">รหัส: <?php echo $sid; ?></small>
        </div>
        <!-- รูปนักศึกษาจาก RMS -->
        <div class="profile-img-box">
            <img src="https://rms.stc.ac.th/image.php?src=files/importpicstd/01/<?php echo $sid; ?>.jpg&x=200&f=0" 
                 onerror="this.src='https://cdn-icons-png.flaticon.com/512/3135/3135715.png'">
        </div>
    </div>
</div>

<div class="container">
    <div class="card main-card mb-4">
        <div id="map"></div>
        <div class="card-body p-0 text-center">
            <div class="pt-4 px-4">
                <div id="location-status" class="status-pill">
                    <span class="spinner-border spinner-border-sm me-2"></span>กำลังค้นหาตำแหน่ง GPS...
                </div>
                <div class="time-display kanit" id="clock">00:00:00</div>
                <p class="text-muted mb-4 small"><i class="bi bi-calendar3 me-1"></i> <?php echo dateThaiTable($today_str); ?></p>
            </div>

            <div class="btn-action-group border-top">
                <button type="button" onclick="handleCheck('in')" class="btn-custom btn-check-in shadow">
                    <i class="bi bi-geo-alt-fill fs-4"></i> ลงชื่อเข้างาน
                </button>
                <button type="button" onclick="handleCheck('out')" class="btn-custom btn-check-out shadow">
                    <i class="bi bi-box-arrow-right fs-4"></i> ลงชื่อเลิกงาน
                </button>
                
                <button type="button" onclick="initLocation()" class="btn btn-link btn-sm text-secondary text-decoration-none">
                    <i class="bi bi-arrow-clockwise"></i> อัปเดตพิกัดตำแหน่งใหม่
                </button>
            </div>
        </div>
    </div>

    <!-- สรุปเวลาวันนี้ -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="summary-box shadow-sm">
                <small class="text-muted d-block mb-1">เวลาเข้า</small>
                <h5 class="fw-bold text-success mb-0"><?php echo $today_in; ?></h5>
            </div>
        </div>
        <div class="col-6">
            <div class="summary-box shadow-sm">
                <small class="text-muted d-block mb-1">เวลาออก</small>
                <h5 class="fw-bold text-danger mb-0"><?php echo $today_out; ?></h5>
            </div>
        </div>
    </div>

    <!-- ประวัติย้อนหลัง -->
    <h6 class="fw-bold mb-3 kanit ps-2">ประวัติการลงเวลา 7 วันล่าสุด</h6>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0 history-table">
                <thead>
                    <tr>
                        <th class="ps-3">วันที่</th>
                        <th class="text-center">เข้า</th>
                        <th class="text-center">ออก</th>
                        <th class="text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php
                    if (file_exists($log_file)) {
                        $json_data = json_decode(file_get_contents($log_file), true);
                        if($json_data) {
                            krsort($json_data);
                            $count = 0;
                            foreach ($json_data as $date => $data) {
                                if ($count >= 7) break;
                                $in = isset($data['check_in']['time']) ? substr($data['check_in']['time'], 0, 5) : "-";
                                $out = isset($data['check_out']['time']) ? substr($data['check_out']['time'], 0, 5) : "-";
                                $badge = ($in != "-" && $out != "-") ? '<span class="badge bg-success-subtle text-success rounded-pill">ครบ</span>' : '<span class="badge bg-warning-subtle text-warning rounded-pill">ไม่ครบ</span>';
                                echo "<tr><td class='ps-3 fw-bold'>".dateThaiTable($date)."</td><td class='text-center text-success'>$in</td><td class='text-center text-danger'>$out</td><td class='text-center'>$badge</td></tr>";
                                $count++;
                            }
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>ไม่มีประวัติการลงเวลา</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JS Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, marker, circle;
    let currentLat = null, currentLng = null;

    // ระบบนาฬิกา
    function updateClock() {
        document.getElementById('clock').innerText = new Date().toLocaleTimeString('th-TH', { hour12: false });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // เริ่มต้นแผนที่
    try {
        map = L.map('map', { zoomControl: false }).setView([14.4746, 100.1222], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    } catch (e) { console.error("Map error:", e); }

    // ดึงพิกัด GPS
    function initLocation() {
        const statusLabel = document.getElementById('location-status');
        if (!navigator.geolocation) {
            statusLabel.innerHTML = "<span class='text-danger'>ไม่รองรับ GPS</span>";
            return;
        }

        navigator.geolocation.getCurrentPosition((pos) => {
            currentLat = pos.coords.latitude;
            currentLng = pos.coords.longitude;
            const accuracy = pos.coords.accuracy;

            statusLabel.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> พิกัดแม่นยำ +/- ${Math.round(accuracy)} ม.`;
            statusLabel.style.background = "#e6fffa";
            statusLabel.style.color = "#047857";

            const latlng = [currentLat, currentLng];
            map.setView(latlng, 17);
            if (marker) map.removeLayer(marker);
            if (circle) map.removeLayer(circle);

            marker = L.marker(latlng).addTo(map);
            circle = L.circle(latlng, { radius: accuracy, color: '#28a745', fillOpacity: 0.1 }).addTo(map);
        }, (err) => {
            statusLabel.innerHTML = "<span class='text-danger'>กรุณาเปิด GPS และกดยอมรับสิทธิ์</span>";
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    initLocation();

    // ฟังก์ชันลงเวลา
    function handleCheck(type) {
        if (!currentLat) {
            alert("ระบบยังไม่ล็อคพิกัด GPS กรุณารอสักครู่...");
            initLocation();
            return;
        }

        const actName = (type === 'in') ? 'เข้างาน' : 'เลิกงาน';
        if (confirm(`คุณต้องการยืนยันการลงเวลา [${actName}] ใช่หรือไม่?`)) {
            fetch('save_checkin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, lat: currentLat, lng: currentLng })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if(data.status === 'success') location.reload();
            })
            .catch(e => alert("เชื่อมต่อฐานข้อมูลล้มเหลว"));
        }
    }
</script>
</body>
</html>