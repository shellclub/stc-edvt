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

// ดึงข้อมูลวันนี้จาก JSON มาโชว์ที่ Card บน
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; padding-bottom: 50px; }
        .header-box { background: linear-gradient(135deg, #800000 0%, #5d0000 100%); color: white; padding: 35px 0 70px 0; border-radius: 0 0 35px 35px; }
        .main-card { background: white; border-radius: 25px; margin-top: -55px; border: none; box-shadow: 0 12px 35px rgba(0,0,0,0.1); overflow: hidden; position: relative; z-index: 10; }
        #map { height: 260px; width: 100%; border-bottom: 1px solid #eee; z-index: 1; }
        .btn-check { padding: 18px; border-radius: 18px; font-weight: 600; font-size: 1.1rem; transition: all 0.2s; border: none; }
        .btn-check:active { transform: scale(0.96); }
        .btn-in { background-color: #28a745; color: white; }
        .btn-out { background-color: #dc3545; color: white; }
        .time-display { font-size: 2.8rem; font-weight: 600; color: #333; margin: 10px 0; }
        .accuracy-text { font-size: 0.75rem; color: #666; }
        .table-custom { font-size: 0.9rem; }
    </style>
</head>
<body>

<div class="header-box shadow-sm">
    <div class="container d-flex align-items-center">
        <a href="dashboard.php" class="text-white me-3 text-decoration-none"><i class="bi bi-arrow-left-circle-fill fs-1"></i></a>
        <div>
            <h4 class="mb-0 fw-bold">ระบบลงเวลาฝึกงาน</h4>
            <small class="opacity-75">ID: <?php echo $sid; ?></small>
        </div>
    </div>
</div>

<div class="container">
    <div class="card main-card mb-4">
        <div id="map"></div>
        <div class="card-body p-4 text-center">
            <div id="location-status" class="mb-2 accuracy-text">
                <div class="spinner-border spinner-border-sm text-primary"></div> กำลังล็อคพิกัด GPS...
            </div>

            <div class="time-display" id="clock">00:00:00</div>
            <p class="text-muted mb-4 small"><i class="bi bi-calendar3 me-1"></i> วันนี้ <?php echo dateThaiTable($today_str); ?></p>

            <div class="row g-3">
                <div class="col-6">
                    <button type="button" onclick="handleCheck('in')" class="btn btn-in w-100 btn-check shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i> เข้างาน
                    </button>
                </div>
                <div class="col-6">
                    <button type="button" onclick="handleCheck('out')" class="btn btn-out w-100 btn-check shadow-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> เลิกงาน
                    </button>
                </div>
            </div>
            
            <button type="button" onclick="initLocation()" class="btn btn-link btn-sm mt-3 text-secondary text-decoration-none small">
                <i class="bi bi-arrow-clockwise"></i> อัปเดตตำแหน่งใหม่ (Refresh GPS)
            </button>
        </div>
    </div>

    <!-- ส่วนสรุป 2 ฝั่ง -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="bg-white p-3 rounded-4 border text-center shadow-sm">
                <small class="text-muted d-block mb-1">เวลาเข้างาน</small>
                <span class="fw-bold text-success fs-5"><?php echo $today_in; ?></span>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-white p-3 rounded-4 border text-center shadow-sm">
                <small class="text-muted d-block mb-1">เวลาเลิกงาน</small>
                <span class="fw-bold text-danger fs-5"><?php echo $today_out; ?></span>
            </div>
        </div>
    </div>

    <!-- ส่วนตารางสรุปรายวัน -->
    <h6 class="fw-bold mb-3 ms-2"><i class="bi bi-clock-history me-2 text-primary"></i>ประวัติการลงเวลา (7 วันล่าสุด)</h6>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0 table-custom">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">วันที่</th>
                        <th class="text-center">เข้า</th>
                        <th class="text-center">ออก</th>
                        <th class="text-center">ผล</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (file_exists($log_file)) {
                        $json_data = json_decode(file_get_contents($log_file), true);
                        krsort($json_data);
                        $count = 0;
                        foreach ($json_data as $date => $data) {
                            if ($count >= 7) break;
                            $in = isset($data['check_in']['time']) ? substr($data['check_in']['time'], 0, 5) : "-";
                            $out = isset($data['check_out']['time']) ? substr($data['check_out']['time'], 0, 5) : "-";
                            $is_ok = ($in != "-" && $out != "-");
                            $badge = $is_ok ? '<span class="badge bg-success-subtle text-success">ครบ</span>' : '<span class="badge bg-warning-subtle text-warning">ไม่ครบ</span>';

                            echo "<tr>";
                            echo "<td class='ps-3 fw-bold'>".dateThaiTable($date)."</td>";
                            echo "<td class='text-center text-success'>$in</td>";
                            echo "<td class='text-center text-danger'>$out</td>";
                            echo "<td class='text-center'>$badge</td>";
                            echo "</tr>";
                            $count++;
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center py-4 text-muted'>ไม่มีข้อมูลการลงเวลา</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map, marker, circle;
    let currentLat = null, currentLng = null;

    // นาฬิกา
    function updateClock() {
        document.getElementById('clock').innerText = new Date().toLocaleTimeString('th-TH', { hour12: false });
    }
    setInterval(updateClock, 1000);
    updateClock();

    // เริ่มต้นแผนที่
    map = L.map('map', { zoomControl: false }).setView([14.4746, 100.1222], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    function initLocation() {
        const statusLabel = document.getElementById('location-status');
        if (!navigator.geolocation) {
            statusLabel.innerHTML = "เบราว์เซอร์ไม่รองรับ GPS";
            return;
        }

        navigator.geolocation.getCurrentPosition((pos) => {
            currentLat = pos.coords.latitude;
            currentLng = pos.coords.longitude;
            const accuracy = pos.coords.accuracy;

            let accColor = (accuracy < 50) ? 'text-success' : (accuracy < 100 ? 'text-warning' : 'text-danger');
            statusLabel.innerHTML = `<i class="bi bi-geo-alt-fill ${accColor}"></i> แม่นยำ: +/- ${Math.round(accuracy)} ม.`;

            const latlng = [currentLat, currentLng];
            map.setView(latlng, 17);
            if (marker) map.removeLayer(marker);
            if (circle) map.removeLayer(circle);

            marker = L.marker(latlng).addTo(map);
            circle = L.circle(latlng, { radius: accuracy, color: (accuracy < 100 ? '#28a745' : '#dc3545'), fillOpacity: 0.1 }).addTo(map);
        }, (err) => {
            statusLabel.innerHTML = "กรุณาเปิด GPS และอนุญาตการเข้าถึง";
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    initLocation();

    function handleCheck(type) {
        if (!currentLat) {
            alert("กรุณารอระบบดึงพิกัด GPS...");
            return;
        }
        const actName = (type === 'in') ? 'เข้างาน' : 'เลิกงาน';
        if (confirm(`ยืนยันการลงเวลา ${actName}?`)) {
            fetch('save_checkin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ type: type, lat: currentLat, lng: currentLng })
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                location.reload();
            })
            .catch(e => alert("เชื่อมต่อเซิร์ฟเวอร์ล้มเหลว"));
        }
    }
</script>
</body>
</html>