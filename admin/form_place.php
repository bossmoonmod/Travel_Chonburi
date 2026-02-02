<?php
session_start();
include "../includes/connection.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id = "";
$name = "";
$detail = "";
$map_link = "";
$image = "";
$is_edit = false;

// ถ้ามี ID ส่งมา แปลว่าเป็นการแก้ไข
if (isset($_GET['id'])) {
    $is_edit = true;
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM places WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    $name = $row['name'];
    $detail = $row['detail'];
    $map_link = $row['map_link'];
    $image = $row['image'];
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'แก้ไข' : 'เพิ่ม'; ?>ข้อมูล - เที่ยวศรีราชา</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Prompt', sans-serif; }
        body { 
            background: #f7fafc; 
            color: #2d3748; 
            padding-bottom: 50px; 
            background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=2073&auto=format&fit=crop');
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh;
        }

         /* Overlay */
         body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }
        
        .container { max-width: 600px; margin: 60px auto; padding: 0 20px; }
        
        .form-card {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        h1 { margin-bottom: 30px; font-size: 1.5em; text-align: center; color: #2d3748; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            background: #f8fafc;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        textarea.form-control { height: 120px; resize: vertical; }
        
        .btn-submit {
            background: #667eea;
            color: white;
            border: none;
            padding: 14px;
            width: 100%;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.3s;
        }
        .btn-submit:hover { background: #5a67d8; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(90, 103, 216, 0.3); }
        
        .btn-back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #cbd5e0;
            text-decoration: none;
            transition: color 0.3s;
        }
        .btn-back:hover { color: white; }
        
        .helper-text {
            font-size: 0.85em;
            color: #718096;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-card">
        <h1><?php echo $is_edit ? '✏️ แก้ไขข้อมูล' : '➕ เพิ่มสถานที่ใหม่'; ?></h1>
        
        <form action="db_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
            <?php if($is_edit): ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="old_image" value="<?php echo $image; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>ชื่อสถานที่</label>
                <input type="text" name="name" class="form-control" value="<?php echo $name; ?>" required placeholder="เช่น เกาะลอย ศรีราชา">
            </div>

            <div class="form-group">
                <label>รายละเอียด</label>
                <textarea name="detail" class="form-control" placeholder="บรรยายรายละเอียดของสถานที่..."><?php echo $detail; ?></textarea>
            </div>

            <div class="form-group">
                <label>📍 ลิงก์แผนที่ Google Maps</label>
                <input type="text" name="map_link" class="form-control" value="<?php echo $map_link; ?>" placeholder="เช่น https://goo.gl/maps/...">
                <div class="helper-text">ไปที่ Google Maps > เลือกสถานที่ > กดแชร์ > คัดลอกลิงก์มาวาง</div>
            </div>

            <div class="form-group">
                <label>🖼️ รูปภาพ <?php echo $is_edit && $image ? '(ว่าง=ใช้รูปเดิม)' : ''; ?></label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <?php if($is_edit && $image): ?>
                    <div style="margin-top: 10px; font-size: 0.9em; color: #718096;">
                        <img src="../assets/images/<?php echo $image; ?>" style="height: 100px; border-radius: 8px; object-fit: cover;">
                        <br>รูปปัจจุบัน
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit">บันทึกข้อมูล</button>
        </form>
        
        <a href="index.php" class="btn-back">← ยกเลิกและกลับไปหน้าหลัก</a>
    </div>
</div>

</body>
</html>
