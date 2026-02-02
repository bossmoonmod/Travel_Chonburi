<?php
session_start();
include "../includes/connection.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM places ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูล - เที่ยวศรีราชา</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* =========================================
           RESET & GLOBALS
        ========================================= */
        :root {
            --primary: #667eea;
            --primary-dark: #5a67d8;
            --secondary: #764ba2;
            --accent: #ed64a6;
            --text-main: #2d3748;
            --text-light: #718096;
            --danger: #e53e3e;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Prompt', sans-serif; }
        
        body {
            background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=2073&auto=format&fit=crop');
            background-size: cover; background-position: center; background-attachment: fixed;
            min-height: 100vh; color: var(--text-main); overflow-x: hidden; position: relative;
        }
        body::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.85) 0%, rgba(118, 75, 162, 0.85) 100%);
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); padding: 15px 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 100; border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        .brand {
            font-weight: 700; font-size: 1.4em; background: linear-gradient(to right, #667eea, #764ba2);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; display: flex; align-items: center; gap: 10px;
        }
        .user-info { display: flex; align-items: center; gap: 20px; }
        .admin-badge { background: #edf2f7; padding: 6px 15px; border-radius: 20px; font-size: 0.9em; color: var(--secondary); font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .btn-logout { color: var(--danger); text-decoration: none; font-size: 0.95em; font-weight: 500; display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 8px; transition: 0.3s; }
        .btn-logout:hover { background: rgba(229, 62, 62, 0.1); }

        /* Content Container */
        .container { 
            max-width: 1100px; margin: 40px auto; padding: 0 20px; position: relative; 
            height: calc(100vh - 120px); display: flex; flex-direction: column; 
        }
        
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-shrink: 0; }
        .page-title { font-size: 2em; font-weight: 700; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
        .btn-add {
            background: white; color: var(--primary); padding: 12px 24px; border-radius: 50px; text-decoration: none;
            display: inline-flex; align-items: center; gap: 10px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .btn-add:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.25); }

        /* Table Card */
        .table-card {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2); overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.5);
            display: flex; flex-direction: column; flex: 1; min-height: 0;
        }
        .table-responsive { overflow-y: auto; flex: 1; }
        .table-responsive::-webkit-scrollbar { width: 6px; height: 6px; }
        .table-responsive::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }

        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th {
            background: #f1f5f9; padding: 18px 20px; text-align: left; font-weight: 600; color: var(--text-light);
            text-transform: uppercase; font-size: 0.85em; letter-spacing: 1px; border-bottom: 2px solid #e2e8f0;
            position: sticky; top: 0; z-index: 10;
        }
        td { padding: 20px; border-bottom: 1px solid #edf2f7; vertical-align: middle; transition: 0.3s; }
        tr:hover td { background: rgba(247, 250, 252, 0.8); }

        .thumb-wrapper { width: 60px; height: 60px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .thumb { width: 100%; height: 100%; object-fit: cover; }
        .no-thumb { width: 100%; height: 100%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #a0aec0; }
        
        .action-flex { display: flex; gap: 8px; }
        .action-btn { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; color: white; text-decoration: none; transition: 0.3s; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .btn-edit { background: linear-gradient(135deg, #4299e1, #3182ce); }
        .btn-delete { background: linear-gradient(135deg, #f56565, #c53030); }

        .empty-state { text-align: center; padding: 60px; color: var(--text-light); }

        /* =========================================
           MOBILE RESPONSIVE
        ========================================= */
        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 15px; padding: 15px 20px; }
            .user-info { width: 100%; justify-content: space-between; background: rgba(0,0,0,0.03); padding: 8px 15px; border-radius: 15px; }
            .container { padding: 0 15px; margin-top: 20px; height: auto; display: block; padding-bottom: 40px; }
            
            .header-section { flex-direction: column; gap: 15px; text-align: center; margin-bottom: 30px; }
            .page-title { font-size: 1.8em; }
            .btn-add { width: 100%; justify-content: center; }

            .table-card { height: 500px; /* Fixed height for scroll on mobile */ }
            th, td { padding: 12px 15px; font-size: 0.95em; }
            .thumb-wrapper { width: 50px; height: 50px; }
            
            /* Hide coordinates on very small screens to save space */
            th:nth-child(3), td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="brand"><i class="fas fa-umbrella-beach"></i> Admin Panel</div>
    <div class="user-info">
        <div class="admin-badge"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['admin_name']; ?></div>
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> ออก</a>
    </div>
</div>

<div class="container">
    <div class="header-section">
        <h1 class="page-title"><i class="fas fa-map-marked-alt"></i> จัดการสถานที่</h1>
        <a href="form_place.php" class="btn-add"><i class="fas fa-plus"></i> เพิ่มสถานที่ใหม่</a>
    </div>

    <div class="table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th width="80">รูปภาพ</th>
                        <th>ข้อมูลสถานที่</th>
                        <th>พิกัด</th>
                        <th width="100">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <div class="thumb-wrapper">
                                    <?php if($row['image']): ?>
                                        <img src="../assets/images/<?php echo $row['image']; ?>" class="thumb">
                                    <?php else: ?>
                                        <div class="no-thumb"><i class="far fa-image"></i></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight:600; color:var(--text-main); margin-bottom:4px;"><?php echo $row['name']; ?></div>
                                <div style="font-size:0.85em; color:var(--text-light); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width: 150px;">
                                    <?php echo $row['detail']; ?>
                                </div>
                            </td>
                            <td style="font-size: 0.85em; color: #718096; font-family: monospace;">
                                <?php echo $row['latitude']; ?>,<br><?php echo $row['longitude']; ?>
                            </td>
                            <td>
                                <div class="action-flex">
                                    <a href="form_place.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit"><i class="fas fa-pen"></i></a>
                                    <a href="db_process.php?action=delete&id=<?php echo $row['id']; ?>" onclick="return confirm('ยืนยันลบ?');" class="action-btn btn-delete"><i class="fas fa-trash-alt"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4"><div class="empty-state"><p>ยังไม่มีข้อมูล</p></div></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
