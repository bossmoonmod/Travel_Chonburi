<?php
session_start();
include "../includes/connection.php";

// ต้อง Login เท่านั้น
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create':
        createPlace($conn);
        break;
    case 'update':
        updatePlace($conn);
        break;
    case 'delete':
        deletePlace($conn);
        break;
    default:
        header("Location: index.php");
        break;
}

function createPlace($conn) {
    // --- SELF HEALING: ตรวจสอบและสร้างคอลัมน์ map_link อัตโนมัติ ---
    $checkCols = mysqli_query($conn, "SHOW COLUMNS FROM places LIKE 'map_link'");
    if (mysqli_num_rows($checkCols) == 0) {
        // ถ้าไม่มีคอลัมน์ map_link ให้สร้างใหม่ทันที
        mysqli_query($conn, "ALTER TABLE places ADD COLUMN map_link TEXT DEFAULT NULL COMMENT 'Google Maps URL'");
    }
    // -----------------------------------------------------------

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $detail = mysqli_real_escape_string($conn, $_POST['detail']);
    $map_link = mysqli_real_escape_string($conn, $_POST['map_link']);
    
    // จัดการรูปภาพ
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $imageName);
    }

    $sql = "INSERT INTO places (name, detail, map_link, image) 
            VALUES ('$name', '$detail', '$map_link', '$imageName')";
            
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
    } else {
        echo "<h2>เกิดข้อผิดพลาดในการบันทึกข้อมูล:</h2>";
        echo "<p>" . mysqli_error($conn) . "</p>";
        echo "<a href='form_place.php'>กลับไปหน้าฟอร์ม</a>";
    }
}

function updatePlace($conn) {
    // --- SELF HEALING: ตรวจสอบและสร้างคอลัมน์ map_link อัตโนมัติ ---
    $checkCols = mysqli_query($conn, "SHOW COLUMNS FROM places LIKE 'map_link'");
    if (mysqli_num_rows($checkCols) == 0) {
        mysqli_query($conn, "ALTER TABLE places ADD COLUMN map_link TEXT DEFAULT NULL COMMENT 'Google Maps URL'");
    }
    // -----------------------------------------------------------

    $id = intval($_POST['id']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $detail = mysqli_real_escape_string($conn, $_POST['detail']);
    $map_link = mysqli_real_escape_string($conn, $_POST['map_link']);
    $old_image = $_POST['old_image'];
    
    // เช็คว่ามีการอัปโหลดรูปใหม่ไหม
    $imageName = $old_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageName = uniqid() . "." . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $imageName);
    }

    $sql = "UPDATE places SET 
            name = '$name',
            detail = '$detail',
            map_link = '$map_link',
            image = '$imageName'
            WHERE id = $id";
            
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
    } else {
        echo "<h2>เกิดข้อผิดพลาดในการอัปเดตข้อมูล:</h2>";
        echo "<p>" . mysqli_error($conn) . "</p>";
        echo "<a href='form_place.php?id=$id'>กลับไปหน้าฟอร์ม</a>";
    }
}

function deletePlace($conn) {
    $id = intval($_GET['id']);
    // (Optional) ลบไฟล์รูปด้วยก็ได้ ถ้าต้องการ
    $sql = "DELETE FROM places WHERE id = $id";
    if (mysqli_query($conn, $sql)) {
        header("Location: index.php");
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
}
?>
