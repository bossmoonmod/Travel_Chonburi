<!----------------------------------------------------------------------------
    ไฟล์: place_detail.php (V3 - With View Counter)
    หน้ารายละเอียดสถานที่ - พร้อมระบบนับยอดวิว
----------------------------------------------------------------------------->

<?php
include "includes/connection.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    // ดึงข้อมูลสถานที่
    $sql = "SELECT * FROM places WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $place = mysqli_fetch_assoc($result);

    // เพิ่มยอดวิว +1
    if ($place) {
        mysqli_query($conn, "UPDATE places SET views = views + 1 WHERE id = $id");
    }
}

if (!isset($place)) {
    header("Location: index.php");
    exit;
}

// Prepare Data
$name = htmlspecialchars($place['name']);
$detail = htmlspecialchars($place['detail']);
$views = number_format($place['views']); // จัดรูปแบบตัวเลขสวยๆ
$map_link = !empty($place['map_link']) ? $place['map_link'] : null;
$image = !empty($place['image']) ? 'assets/images/' . $place['image'] : 'https://via.placeholder.com/1200x600.png?text=No+Image';
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?> - เที่ยวศรีราชา</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #FF416C;
            --primary-grad: linear-gradient(135deg, #FF6B6B 0%, #FF8E53 100%);
            --text-main: #2d3748;
            --text-muted: #718096;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Prompt', sans-serif;
            background-color: #f7fafc;
            color: var(--text-main);
            overflow-x: hidden;
        }

        /* =========================================
           HERO IMAGE (FULL WIDTH)
        ========================================= */
        .hero-banner {
            position: relative;
            height: 60vh;
            min-height: 400px;
            overflow: hidden;
        }

        .hero-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            animation: kenBurns 15s infinite alternate;
        }

        @keyframes kenBurns {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        .hero-overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.8) 100%);
        }

        .hero-content {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 40px 20px 60px;
            text-align: center;
            color: white;
            z-index: 10;
        }

        .place-title {
            font-size: 3.5em;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }

        .view-count {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(0,0,0,0.5);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
            color: #ffd700; /* สีทอง */
        }

        .breadcrumb {
            display: inline-flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.9em;
            margin-bottom: 20px;
        }
        .breadcrumb a { color: white; text-decoration: none; font-weight: 500; }
        .breadcrumb span { opacity: 0.7; }

        /* =========================================
           MAIN CONTENT
        ========================================= */
        .container {
            max-width: 1000px;
            margin: -50px auto 50px;
            position: relative;
            z-index: 20;
            padding: 0 20px;
        }

        .content-card {
            background: white;
            border-radius: 24px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            animation: slideUp 0.8s ease-out;
        }

        .section-heading {
            font-size: 1.5em;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-main);
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
        }
        .section-heading i { color: #FF416C; }

        .place-detail {
            font-size: 1.1em;
            line-height: 1.8;
            color: #4a5568;
            white-space: pre-line;
            margin-bottom: 40px;
        }

        /* =========================================
           MAP & ACTIONS
        ========================================= */
        .map-section {
            margin-top: 40px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #edf2f7;
        }

        .map-frame {
            width: 100%;
            height: 400px;
            border: none;
        }

        .action-bar {
            margin-top: 40px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 28px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary-grad);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(255, 107, 107, 0.5); }

        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover { background: #edf2f7; }

        .btn-share {
            margin-left: auto;
            color: var(--primary);
            background: rgba(255, 65, 108, 0.1);
        }
        .btn-share:hover { background: rgba(255, 65, 108, 0.2); }

        /* =========================================
           FOOTER
        ========================================= */
        footer {
            text-align: center;
            padding: 40px;
            color: var(--text-muted);
            border-top: 1px solid #edf2f7;
            margin-top: 60px;
            background: white;
        }

        /* Animation */
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .place-title { font-size: 2em; }
            .hero-banner { height: 50vh; }
            .content-card { padding: 30px 20px; }
            .action-bar { flex-direction: column; }
            .btn-share { margin-left: 0; }
        }
    </style>
</head>
<body>

    <!-- HERO IMAGE -->
    <div class="hero-banner">
        <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>" class="hero-img">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="breadcrumb">
                <a href="index.php">หน้าหลัก</a>
                <span>/</span>
                <a href="#">ข้อมูลสถานที่</a>
            </div>
            <h1 class="place-title"><?php echo $name; ?></h1>
            <div class="view-count">
                <i class="fas fa-eye"></i> เข้าชม <?php echo $views; ?> ครั้ง
            </div>
        </div>
    </div>

    <div class="container">
        <div class="content-card">
            
            <!-- DETAIL TEXT -->
            <h2 class="section-heading">
                <i class="fas fa-info-circle"></i> รายละเอียด
            </h2>
            <div class="place-detail"><?php echo $detail; ?></div>

            <!-- MAP SECTION -->
            <h2 class="section-heading">
                <i class="fas fa-map-marked-alt"></i> แผนที่ตำแหน่ง
            </h2>
            <div class="map-section">
                <!-- ใช้ชื่อสถานที่ใน Query Parameter เพื่อแสดงแผนที่โดยประมาณ -->
                <iframe class="map-frame" 
                    frameborder="0" 
                    src="https://maps.google.com/maps?q=<?php echo urlencode($name . ' ศรีราชา'); ?>&t=&z=15&ie=UTF8&iwloc=&output=embed">
                </iframe>
            </div>

            <!-- ACTIONS -->
            <div class="action-bar">
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> กลับหน้าหลัก
                </a>
                
                <?php if ($map_link): ?>
                <a href="<?php echo $map_link; ?>" target="_blank" class="btn btn-primary">
                    <i class="fas fa-location-arrow"></i> นำทางด้วย Google Maps
                </a>
                <?php endif; ?>

                <button class="btn btn-share" onclick="alert('คัดลอกลิงก์เรียบร้อย!'); navigator.clipboard.writeText(window.location.href);">
                    <i class="fas fa-share-alt"></i> แชร์หน้านี้
                </button>
            </div>

        </div>
    </div>

    <footer>
        <p>&copy; 2026 เที่ยวศรีราชา | Uncover the hidden gems.</p>
    </footer>

</body>
</html>