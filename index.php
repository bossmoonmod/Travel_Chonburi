<?php
include "includes/connection.php";

// --- CONFIG ---
$limit = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Search Logic
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$search_query = "";
if ($search) {
    $search_query = " WHERE name LIKE '%$search%' OR detail LIKE '%$search%' ";
}

// Count Total
$sql_count = "SELECT COUNT(*) as total FROM places" . $search_query;
$result_count = mysqli_query($conn, $sql_count);
$row_count = mysqli_fetch_assoc($result_count);
$total_items = $row_count['total'];
$total_pages = ceil($total_items / $limit);

// Fetch Items
$sql = "SELECT * FROM places" . $search_query . " ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Top 3 Logic
$display_podium = [];
if (!$search) {
    $sql_top = "SELECT * FROM places ORDER BY views DESC LIMIT 3";
    $q_top = mysqli_query($conn, $sql_top);
    $all_tops = [];
    $rank_counter = 1;
    while($row = mysqli_fetch_assoc($q_top)) {
        $row['real_rank'] = $rank_counter++;
        $all_tops[] = $row;
    }
    if (isset($all_tops[1])) $display_podium[] = $all_tops[1];
    if (isset($all_tops[0])) $display_podium[] = $all_tops[0];
    if (isset($all_tops[2])) $display_podium[] = $all_tops[2];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เที่ยวศรีราชา - Sriracha Travel Guide</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /* =========================================
           RESET & VARIABLES
        ========================================= */
        :root {
            --primary: #FF416C;
            --primary-grad: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            --text-main: #2d3748;
            --text-muted: #718096;
            --gold: #FFD700;
            --silver: #C0C0C0;
            --bronze: #CD7F32;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Prompt', sans-serif; background-color: #f7fafc; color: var(--text-main); overflow-x: hidden; }

        /* =========================================
           HERO SECTION
        ========================================= */
        .hero-section {
            position: relative; height: 75vh; min-height: 550px;
            display: flex; align-items: center; justify-content: center; overflow: hidden;
        }
        .hero-bg {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: url('assets/images/Background-top.png') no-repeat center center;
            background-size: cover; z-index: 0;
            animation: kenBurns 20s ease-in-out infinite alternate;
        }
        @keyframes kenBurns { 0% { transform: scale(1); } 100% { transform: scale(1.1); } }
        .hero-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.6) 100%); z-index: 1;
        }
        .hero-content {
            position: relative; z-index: 10; text-align: center; color: white;
            padding: 20px; max-width: 800px; margin-top: -60px;
            animation: fadeInUp 1s ease-out; width: 100%;
        }
        .hero-title { font-size: 3.5em; font-weight: 800; margin-bottom: 10px; line-height: 1.2; text-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        .hero-subtitle { font-size: 1.2em; font-weight: 300; margin-bottom: 40px; opacity: 0.95; }

        /* SEARCH */
        .search-container { position: relative; max-width: 600px; margin: 0 auto; z-index: 50; width: 90%; }
        .search-input { 
            width: 100%; height: 65px; padding: 0 30px; padding-right: 75px; 
            border-radius: 60px; border: none; font-size: 1.1rem; 
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); 
            box-shadow: 0 10px 40px rgba(0,0,0,0.25); color: #333; 
        }
        .search-input:focus { outline: none; box-shadow: 0 15px 50px rgba(0,0,0,0.35); }
        .search-btn { 
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            width: 50px; height: 50px; border-radius: 50%; background: var(--primary-grad); 
            color: white; border: none; font-size: 1.1rem; cursor: pointer; transition: all 0.3s; 
            display: flex; align-items: center; justify-content: center; 
        }
        .suggestions-list { position: absolute; top: 110%; left: 20px; right: 20px; background: white; border-radius: 20px; box-shadow: 0 15px 40px rgba(0,0,0,0.2); overflow: hidden; display: none; z-index: 100; text-align: left; }
        .suggestion-item { padding: 15px 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; border-bottom: 1px solid #f7fafc; color: var(--text-main); }
        .suggestion-item:hover { background: #f0f4f8; }
        .suggestion-img { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; border: 1px solid #edf2f7; }

        /* =========================================
           PODIUM SECTION
        ========================================= */
        .podium-container {
            position: relative; margin-top: -100px; margin-bottom: 30px; z-index: 20;
            display: flex; justify-content: center; align-items: flex-end; gap: 20px; padding: 0 20px;
        }
        .rank-card {
            background: white; border-radius: 20px; overflow: visible; box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            transition: transform 0.3s; width: 300px; text-align: center; position: relative; cursor: pointer; 
            display: flex; flex-direction: column;
        }
        .rank-card:hover { transform: translateY(-10px) !important; z-index: 30; }
        .rank-card[data-rank="1"] { transform: scale(1.15) translateY(-20px); z-index: 10; border: 4px solid var(--gold); }
        .rank-card[data-rank="2"] { transform: scale(1); z-index: 5; border-top: 6px solid var(--silver); }
        .rank-card[data-rank="3"] { transform: scale(1); z-index: 5; border-top: 6px solid var(--bronze); }
        
        .rank-img-box { width: 100%; height: 200px; border-radius: 16px 16px 0 0; overflow: hidden; }
        .rank-img { width: 100%; height: 100%; object-fit: cover; }
        .rank-badge {
            position: absolute; top: 15px; left: 15px; width: 45px; height: 45px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; font-size: 1.4em; color: white; font-weight: 800;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2); z-index: 15;
        }
        .rank-1 .rank-badge { background: var(--gold); }
        .rank-2 .rank-badge { background: var(--silver); }
        .rank-3 .rank-badge { background: var(--bronze); }
        .crown-icon {
            position: absolute; top: -35px; left: 50%; transform: translateX(-50%); font-size: 3rem; color: var(--gold); 
            text-shadow: 0 4px 10px rgba(0,0,0,0.4); animation: float 2s infinite ease-in-out; z-index: 20;
        }
        @keyframes float { 0%,100%{transform:translate(-50%,0);} 50%{transform:translate(-50%,-10px);} }
        .rank-body { padding: 20px; }
        .rank-name { font-size: 1.1em; font-weight: 700; margin-bottom: 5px; color: var(--text-main); }
        .rank-views { font-size: 0.9em; color: var(--text-muted); }

        /* =========================================
           MAP SECTION
        ========================================= */
        .location-section { max-width: 1100px; margin: 0 auto 60px; padding: 0 20px; position: relative; z-index: 10; }
        .location-card { background: white; border-radius: 30px; box-shadow: 0 20px 60px rgba(0,0,0,0.12); border: 1px solid rgba(255,255,255,0.5); overflow: hidden; }
        .location-header { padding: 25px 35px; display: flex; justify-content: space-between; align-items: center; background: linear-gradient(to right, #ffffff, #fdfdfd); border-bottom: 1px solid #f0f0f0; }
        .loc-title { font-size: 1.4rem; font-weight: 800; display: flex; align-items: center; gap: 12px; }
        .loc-title i { color: #FF416C; background: rgba(255, 65, 108, 0.1); padding: 10px; border-radius: 12px; }
        .btn-locate { 
            background: var(--primary-grad); border: none; color: white; padding: 12px 24px; 
            border-radius: 50px; font-weight: 600; cursor: pointer; box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4); 
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
        }
        #map { width: 100%; height: 400px; }

        /* =========================================
           MAIN GRID & ANIMATION
        ========================================= */
        .main-container { max-width: 1200px; margin: 0 auto; padding: 40px 20px 80px; }
        .section-center { text-align: center; margin-bottom: 50px; }
        .section-title { font-size: 2.2em; font-weight: 800; display: inline-block; position: relative; margin-bottom: 15px; }
        .section-title::after { content: ''; display: block; width: 60px; height: 5px; background: var(--primary-grad); margin: 10px auto 0; border-radius: 3px; }
        
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; transition: opacity 0.3s; }
        .card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); cursor: pointer; transition: transform 0.3s; }
        .card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .card-img-container { height: 220px; position: relative; }
        .card-img { width: 100%; height: 100%; object-fit: cover; }
        .card-badge { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.95); padding: 5px 12px; border-radius: 20px; font-weight: 700; color: #FF6B6B; font-size: 0.85em; }
        .card-body { padding: 25px; }
        .card-title { font-size: 1.25em; font-weight: 700; margin-bottom: 8px; color: #333; }
        .card-text { color: #666; font-size: 0.95em; height: 45px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
        .card-footer { display: flex; justify-content: space-between; margin-top: 20px; color: #a0aec0; font-size: 0.9em; }
        .view-more { color: var(--primary); font-weight: 600; display: flex; align-items: center; gap: 5px; }

        .pagination { display: flex; justify-content: center; margin-top: 50px; gap: 8px; }
        .page-link { 
            width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; 
            background: white; border-radius: 50%; text-decoration: none; color: #555; 
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; 
        }
        .page-link.active { background: var(--primary-grad); color: white; box-shadow: 0 6px 15px rgba(255, 107, 107, 0.4); }
        
        /* Animations */
        .fade-out { opacity: 0; transform: translateY(20px); transition: 0.3s; }
        .fade-in { opacity: 1; transform: translateY(0); transition: 0.5s; animation: slideUp 0.5s ease-out; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        .footer { background: white; text-align: center; padding: 40px; margin-top: 60px; border-top: 1px solid #edf2f7; color: #718096; }

        /* =========================================
           MOBILE RESPONSIVE
        ========================================= */
        @media (max-width: 900px) {
            .hero-section { height: auto; min-height: 500px; padding: 120px 0 60px; }
            .hero-title { font-size: 2.5em; }
            .hero-content { margin-top: 0; }
            
            /* Podium Stack */
            .podium-container { flex-direction: column; align-items: center; margin-top: -30px; gap: 40px; }
            .rank-card { width: 100%; max-width: 320px; }
            .rank-card[data-rank="1"] { order: -1; transform: scale(1.05); border-width: 4px; }
            .rank-card[data-rank="2"] { order: 0; transform: scale(1); }
            .rank-card[data-rank="3"] { order: 1; transform: scale(1); }

            /* Map */
            .location-header { flex-direction: column; align-items: flex-start; gap: 15px; padding: 20px; }
            .btn-locate { width: 100%; justify-content: center; }
            
            /* Grid */
            .grid { grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        }

        @media (max-width: 480px) {
            .hero-title { font-size: 2em; }
            .hero-subtitle { font-size: 1rem; padding: 0 10px; }
            .search-input { height: 55px; font-size: 1rem; padding-right: 60px; }
            .search-btn { width: 40px; height: 40px; right: 8px; }
            .rank-card { max-width: 100%; }
            .section-title { font-size: 1.8em; }
            #map { height: 300px; }
        }
    </style>
</head>
<body>

    <header class="hero-section">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">เที่ยวศรีราชา</h1>
            <p class="hero-subtitle">ค้นพบมุมมองใหม่ของการพักผ่อน ริมทะเลที่คุณหลงรัก</p>
            <form action="" method="GET" class="search-container" autocomplete="off">
                <input type="text" id="searchInput" name="search" class="search-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="วันนี้คุณอยากไปเที่ยวไหน...">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                <div id="suggestions" class="suggestions-list"></div>
            </form>
        </div>
    </header>

    <?php if (!$search && count($display_podium) >= 1): ?>
    <div class="podium-container">
        <?php foreach ($display_podium as $place): 
            $rank = $place['real_rank']; 
            $img = !empty($place['image']) ? 'assets/images/'.$place['image'] : 'https://via.placeholder.com/400x300.png?text=No+Image';
            $badgeClass = ($rank == 1) ? 'rank-1' : (($rank == 2) ? 'rank-2' : 'rank-3');
        ?>
        <div class="rank-card <?php echo $badgeClass; ?>" data-rank="<?php echo $rank; ?>" onclick="window.location.href='place_detail.php?id=<?php echo $place['id']; ?>'">
            <?php if($rank == 1): ?><div class="crown-icon"><i class="fas fa-crown"></i></div><?php endif; ?>
            <div class="rank-badge"><?php echo $rank; ?></div>
            <div class="rank-img-box"><img src="<?php echo $img; ?>" class="rank-img"></div>
            <div class="rank-body">
                <div class="rank-name"><?php echo $place['name']; ?></div>
                <div class="rank-views"><i class="fas fa-eye"></i> <?php echo number_format($place['views']); ?> เข้าชม</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!$search): ?>
    <div class="location-section">
        <div class="location-card">
            <div class="location-header">
                <div>
                    <div class="loc-title"><i class="fas fa-map-location-dot"></i> ตำแหน่งปัจจุบันของคุณ</div>
                    <div style="font-size:0.9em; color:#777; margin-top:5px; margin-left:45px;">สำรวจสถานที่ท่องเที่ยวใกล้ตัวคุณได้ง่ายๆ</div>
                </div>
                <button class="btn-locate" onclick="getLocation()"><i class="fas fa-location-crosshairs"></i> ระบุพิกัดของฉัน</button>
            </div>
            <div id="map"></div>
        </div>
    </div>
    <?php endif; ?>

    <div class="main-container">
        <div class="section-center">
            <h2 class="section-title"><?php echo $search ? 'ผลการค้นหา' : 'สถานที่ท่องเที่ยว'; ?></h2>
            <?php if($search): ?>
                <div style="margin-top: 15px;"><a href="index.php" class="reset-btn" style="color:var(--primary); text-decoration:none;"><i class="fas fa-undo"></i> ดูทั้งหมด</a></div>
            <?php endif; ?>
        </div>
        
        <div id="dynamic-content">
            <div class="grid">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): 
                        $img = !empty($row['image']) ? 'assets/images/'.$row['image'] : 'https://via.placeholder.com/400x300.png?text=No+Image';
                        $short = mb_strlen($row['detail']) > 150 ? mb_substr($row['detail'], 0, 150).'...' : $row['detail'];
                    ?>
                    <div class="card" onclick="window.location.href='place_detail.php?id=<?php echo $row['id']; ?>'">
                        <div class="card-img-container">
                            <img src="<?php echo $img; ?>" class="card-img">
                            <div class="card-badge"><i class="fas fa-eye"></i> <?php echo number_format($row['views']); ?></div>
                        </div>
                        <div class="card-body">
                            <div class="card-title"><?php echo $row['name']; ?></div>
                            <div class="card-text"><?php echo $short; ?></div>
                            <div class="card-footer">
                                <div class="view-more">รายละเอียด <i class="fas fa-arrow-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align:center; padding:50px; grid-column: 1/-1; color:#999;">
                        <h3>ไม่พบข้อมูล</h3>
                        <?php if($search): ?><a href="index.php" style="color:var(--primary);">กลับหน้าแรก</a><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if($total_pages > 1): ?>
            <div class="pagination">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>" class="page-link <?php echo (($i==$page)?'active':''); ?>" onclick="event.preventDefault(); loadPage(<?php echo $i; ?>);"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <footer class="footer"><p>&copy; 2026 <strong>เที่ยวศรีราชา</strong> | Travel To Chonburi.</p></footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Pagination
        function loadPage(pageNum) {
            const container = document.getElementById('dynamic-content');
            const searchParams = new URLSearchParams(window.location.search);
            const searchTerm = searchParams.get('search') || '';
            container.classList.add('fade-out');
            setTimeout(() => {
                fetch(`fetch_places.php?page=${pageNum}&search=${encodeURIComponent(searchTerm)}`)
                    .then(response => response.text())
                    .then(html => {
                        container.innerHTML = html;
                        const newUrl = `?page=${pageNum}` + (searchTerm ? `&search=${encodeURIComponent(searchTerm)}` : '');
                        window.history.pushState({path: newUrl}, '', newUrl);
                        const headerOffset = document.querySelector('.section-center').getBoundingClientRect().top + window.scrollY - 100;
                        window.scrollTo({top: headerOffset, behavior: 'smooth'});
                        container.classList.remove('fade-out');
                        container.classList.add('fade-in');
                        setTimeout(() => container.classList.remove('fade-in'), 500);
                    });
            }, 300);
        }

        // Suggestions
        const searchInput = document.getElementById('searchInput');
        const suggestionsBox = document.getElementById('suggestions');
        if(searchInput){
            searchInput.addEventListener('input', function() {
                const term = this.value;
                if(term.length < 1) { suggestionsBox.style.display = 'none'; return; }
                fetch('search_api.php?term=' + encodeURIComponent(term))
                    .then(rs => rs.json())
                    .then(data => {
                        suggestionsBox.innerHTML = '';
                        if(data.length > 0) {
                            data.forEach(p => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                let imgHtml = p.image ? `<img src="assets/images/${p.image}" class="suggestion-img">` : '';
                                div.innerHTML = `${imgHtml}<span>${p.name}</span>`;
                                div.addEventListener('click', () => window.location.href = 'place_detail.php?id=' + p.id);
                                suggestionsBox.appendChild(div);
                            });
                            suggestionsBox.style.display = 'block';
                        } else { suggestionsBox.style.display = 'none'; }
                    });
            });
            document.addEventListener('click', (e) => { if(!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) suggestionsBox.style.display='none'; });
        }

        // Map
        var map, marker;
        function initMap() {
            if(document.getElementById('map')) {
                map = L.map('map').setView([13.1723, 100.9317], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
                getLocation();
            }
        }
        function getLocation() {
            if (navigator.geolocation) {
                var btn = document.querySelector('.btn-locate');
                if(btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> กำลังระบุ...';
                navigator.geolocation.getCurrentPosition(showPosition, showError);
            }
        }
        function showPosition(position) {
            var lat = position.coords.latitude;
            var lng = position.coords.longitude;
            map.setView([lat, lng], 16);
            if(marker) marker.setLatLng([lat, lng]);
            else {
                var icon = L.divIcon({ className: 'custom-div-icon', html: "<div style='background-color:#4285F4; width:15px; height:15px; border-radius:50%; border:2px solid white; box-shadow:0 0 0 5px rgba(66, 133, 244, 0.3);'></div>", iconSize: [20, 20], iconAnchor: [10, 10] });
                marker = L.marker([lat, lng], {icon: icon}).addTo(map).bindPopup("<b>คุณอยู่นี่!</b>").openPopup();
            }
            var btn = document.querySelector('.btn-locate');
            if(btn) btn.innerHTML = '<i class="fas fa-location-crosshairs"></i> ระบุพิกัดแล้ว';
        }
        function showError() { alert("ไม่สามารถระบุตำแหน่งได้"); }
        window.onload = initMap;
    </script>
</body>
</html>