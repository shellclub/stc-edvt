<?php
session_start();
include "../config.php";

// ตรวจสอบสิทธิ์ Admin ทวิภาคี[cite: 2]
if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'bilateral_officer') {
    header("location: admin_login.php"); 
    exit();
}

// ดึงข้อมูลสถานประกอบการและรวมข้อมูลที่ซ้ำกัน พร้อมพิกัด[cite: 2]
$sql = "
    SELECT 
        p.place_id,
        p.company_name,
        p.mentor_name,
        p.company_phone,
        p.company_address,
        p.workplace_lat,
        p.workplace_lng,
        COUNT(s.student_id) AS total_std,
        GROUP_CONCAT(CONCAT(s.fullname, ' (', s.group_name, ')') SEPARATOR ', ') AS student_names
    FROM internship_places p
    LEFT JOIN students s ON p.place_id = s.place_id
    WHERE p.company_name IS NOT NULL AND TRIM(p.company_name) != ''
    GROUP BY p.company_name, p.mentor_name, p.company_phone, p.company_address, p.workplace_lat, p.workplace_lng
    ORDER BY total_std DESC, p.company_name ASC
";
$query = mysqli_query($conn,$sql);

$places = [];
while ($row = mysqli_fetch_assoc($query)) {
    $places[] =$row;
}

// แปลงข้อมูลเป็น JSON เพื่อส่งต่อให้แผนที่ JavaScript
$places_json = json_encode($places, JSON_UNESCAPED_UNICODE);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผนที่พิกัดรวมสถานประกอบการ | งานทวิภาคี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Leaflet CSS & JS (แผนที่ OpenStreetMap ใช้งานฟรี) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        :root { --primary: #1a237e; }
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fa; margin: 0; }

        @media screen {
            .app-container { height: 100vh; display: flex; flex-direction: column; overflow: hidden; }
            .content-area { flex-grow: 1; display: flex; height: calc(100vh - 65px); }
            
            /* แถบรายการสถานประกอบการด้านซ้าย */
            .sidebar-places {
                width: 380px;
                background: white;
                border-right: 1px solid #dee2e6;
                display: flex;
                flex-direction: column;
                height: 100%;
            }
            .places-list { flex-grow: 1; overflow-y: auto; }
            .place-card {
                cursor: pointer;
                transition: 0.2s;
                border-left: 4px solid transparent;
            }
            .place-card:hover, .place-card.active {
                background-color: #eef2ff;
                border-left-color: var(--primary);
            }
            
            /* ส่วนแผนที่ด้านขวา */
            #map { flex-grow: 1; height: 100%; }
            .print-table { display: none; }
        }

        /* สำหรับการสั่งพิมพ์ (Print to A4) */
        @media print {
            @page { size: A4 landscape; margin: 12mm; }
            body { background: white !important; }
            .no-print { display: none !important; }
            .print-table { display: block !important; width: 100%; }
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #000 !important; padding: 6px 8px; font-size: 12px; }
            th { background-color: #f0f0f0 !important; -webkit-print-color-adjust: exact; }
            tr { break-inside: avoid !important; }
        }
    </style>
</head>
<body>

<div class="app-container">
    <!-- Navbar แถบเครื่องมือด้านบน -->
    <div class="no-print bg-white border-bottom shadow-sm px-4 py-2 d-flex justify-content-between align-items-center" style="height: 65px;">
        <div class="d-flex align-items-center">
            <a href="bilateral_dashboard.php" class="btn btn-outline-secondary btn-sm me-3">
                <i class="bi bi-arrow-left"></i> หน้าหลัก
            </a>
            <h5 class="fw-bold mb-0 text-dark">
                <i class="bi bi-geo-alt-fill text-danger me-2"></i>แผนที่พิกัดรวมสถานประกอบการฝึกงาน
            </h5>
            <span class="badge bg-primary ms-3 rounded-pill"><?php echo count($places); ?> แห่ง</span>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-danger btn-sm shadow-sm">
                <i class="bi bi-printer me-1"></i> พิมพ์เอกสารพิกัด (PDF)
            </button>
            <a href="company_list.php" class="btn btn-light border btn-sm">
                <i class="bi bi-building me-1"></i> ตารางสถานประกอบการ
            </a>
        </div>
    </div>

    <!-- ส่วนเนื้อหาแผนที่และแถบสถานประกอบการ -->
    <div class="content-area no-print">
        <!-- แถบด้านซ้าย: ค้นหาและรายชื่อสถานประกอบการ -->
        <div class="sidebar-places">
            <div class="p-3 border-bottom bg-light">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" id="filterInput" class="form-control" placeholder="ค้นหาสถานประกอบการ...">
                </div>
            </div>
            
            <div class="places-list p-2" id="placesList">
                <?php foreach ($places as $index =>$p): ?>
                <div class="place-card p-3 mb-2 rounded border" 
                     id="card-<?php echo $index; ?>"
                     onclick="focusPlace(<?php echo $index; ?>)">
                    <div class="d-flex justify-content-between align-items-start">
                        <strong class="text-primary"><?php echo htmlspecialchars($p['company_name']); ?></strong>
                        <span class="badge bg-secondary"><?php echo $p['total_std']; ?> คน</span>
                    </div>
                    <small class="text-muted d-block mt-1">
                        <i class="bi bi-person me-1"></i>ครูฝึก: <?php echo htmlspecialchars($p['mentor_name'] ?: '-'); ?>[cite: 2]
                    </small>
                    <small class="text-muted d-block text-truncate">
                        <i class="bi bi-telephone me-1"></i>โทร: <?php echo htmlspecialchars($p['company_phone'] ?: '-'); ?>[cite: 2]
                    </small>
                    
                    <div class="mt-2 d-flex justify-content-between align-items-center">
                        <?php if (!empty($p['workplace_lat']) && !empty($p['workplace_lng'])): ?>
                            <span class="badge bg-success-subtle text-success border">
                                <i class="bi bi-check-circle me-1"></i>มีพิกัด
                            </span>
                            <a href="https://maps.google.com/?q=<?php echo $p['workplace_lat']; ?>,<?php echo$p['workplace_lng']; ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 11px;" onclick="event.stopPropagation();">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Google Maps
                            </a>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-danger border">
                                <i class="bi bi-exclamation-circle me-1"></i>ไม่มีพิกัด
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ฝั่งขวา: แผนที่ Leaflet -->
        <div id="map"></div>
    </div>
</div>

<!-- ================= ตารางที่จะปรากฏเฉพาะตอนสั่งพิมพ์ (Print View) ================= -->
<div class="print-table">
    <div class="text-center mb-3">
        <h4 class="fw-bold mb-1">สรุปรายชื่อและพิกัดแผนที่สถานประกอบการฝึกประสบการณ์วิชาชีพ</h4>
        <h5 class="fw-bold mb-1">วิทยาลัยเทคนิคสุพรรณบุรี</h5>
        <small class="text-muted">ข้อมูล ณ วันที่ <?php echo date('d/m/') . (date('Y') + 543); ?> (จำนวนทั้งหมด <?php echo count($places); ?> แห่ง)</small>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 25%;">ชื่อสถานประกอบการ</th>
                <th style="width: 25%;">ที่อยู่ / เบอร์โทรศัพท์</th>
                <th style="width: 15%;">พิกัด (Lat, Lng)</th>
                <th style="width: 10%;">จำนวนนักศึกษา</th>
                <th style="width: 20%;">ลิงก์แผนที่นำทาง</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($places as$p): 
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><strong><?php echo htmlspecialchars($p['company_name']); ?></strong></td>
                <td>
                    <?php echo htmlspecialchars($p['company_address'] ?: '-'); ?><br>
                    <small>โทร: <?php echo htmlspecialchars($p['company_phone'] ?: '-'); ?></small>[cite: 2]
                </td>
                <td class="text-center">
                    <?php if (!empty($p['workplace_lat']) && !empty($p['workplace_lng'])): ?>
                        <code><?php echo round($p['workplace_lat'], 5); ?>, <?php echo round($p['workplace_lng'], 5); ?></code>[cite: 2]
                    <?php else: ?>
                        <span class="text-danger">-</span>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?php echo $p['total_std']; ?> คน</td>
                <td style="word-break: break-all; font-size: 10px;">
                    <?php if (!empty($p['workplace_lat']) && !empty($p['workplace_lng'])): ?>
                        https://maps.google.com/?q=<?php echo $p['workplace_lat']; ?>,<?php echo$p['workplace_lng']; ?>[cite: 2]
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    const places = <?php echo $places_json; ?>;
    
    // ตั้งจุดกึ่งกลางเริ่มต้นที่ จ.สุพรรณบุรี (14.4745, 100.1177)
    const map = L.map('map').setView([14.4745, 100.1177], 11);

    // ใช้แผนที่ OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const markers = [];
    const bounds = [];

    // นำเข้า Marker ทั้งหมด
    places.forEach((p, index) => {
        if (p.workplace_lat && p.workplace_lng) {
            const lat = parseFloat(p.workplace_lat);
            const lng = parseFloat(p.workplace_lng);

            if (!isNaN(lat) && !isNaN(lng)) {
                const marker = L.marker([lat, lng]).addTo(map);
                
                const popupContent = `
                    <div style="font-family: 'Sarabun'; min-width: 200px;">
                        <h6 class="fw-bold text-primary mb-1">${p.company_name}</h6>
                        <small class="text-muted d-block mb-1">${p.company_address || '-'}</small>
                        <div class="small"><b>ครูฝึก:</b> ${p.mentor_name || '-'}</div>
                        <div class="small"><b>โทร:</b> ${p.company_phone || '-'}</div>
                        <div class="small"><b>นักศึกษา:</b> ${p.total_std} คน</div>
                        <div class="mt-2 text-end">
                            <a href="https://maps.google.com/?q=${lat},${lng}" target="_blank" class="btn btn-primary btn-sm text-white" style="font-size: 11px; padding: 2px 8px;">
                                นำทางผ่าน Google Maps
                            </a>
                        </div>
                    </div>
                `;
                marker.bindPopup(popupContent);
                
                markers[index] = marker;
                bounds.push([lat, lng]);
            }
        }
    });

    // ปรับ Zoom ให้ครอบคลุมทุก Marker ที่มีพิกัด
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [50, 50] });
    }

    // ฟังก์ชันเลื่อนหน้าจอไปยังสถานประกอบการที่เลือก
    function focusPlace(index) {
        // ไฮไลต์การ์ดที่เลือก
        document.querySelectorAll('.place-card').forEach(c => c.classList.remove('active'));
        const card = document.getElementById('card-' + index);
        if (card) card.classList.add('active');

        const p = places[index];
        if (p.workplace_lat && p.workplace_lng && markers[index]) {
            const lat = parseFloat(p.workplace_lat);
            const lng = parseFloat(p.workplace_lng);
            map.flyTo([lat, lng], 15, { animate: true, duration: 1.2 });
            markers[index].openPopup();
        } else {
            alert('สถานประกอบการนี้ยังไม่ได้ระบุพิกัดละติจูด/ลองจิจูด');
        }
    }

    // กรองค้นหาสถานประกอบการที่แถบด้านซ้าย
    document.getElementById('filterInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase().trim();
        const cards = document.querySelectorAll('.place-card');
        
        cards.forEach(card => {
            const text = card.textContent.toLowerCase();
            card.style.display = text.includes(filter) ? '' : 'none';
        });
    });
</script>

</body>
</html>