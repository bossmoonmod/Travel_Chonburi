<?php
include "includes/connection.php";

echo "<h2>🛠️ กำลังอัปเกรดระบบจัดอันดับ...</h2>";

// 1. ตรวจสอบคอลัมน์ views
$check = mysqli_query($conn, "SHOW COLUMNS FROM places LIKE 'views'");
if (mysqli_num_rows($check) == 0) {
    // เพิ่มคอลัมน์ views
    $sql = "ALTER TABLE places ADD COLUMN views INT DEFAULT 0";
    if (mysqli_query($conn, $sql)) {
        echo "<h3 style='color: green;'>✅ เพิ่มระบบนับยอดวิวสำเร็จ!</h3>";
        
        // สุ่มยอดวิวให้สถานที่เดิม (มีข้อมูลจะได้ดูไม่โล่ง)
        mysqli_query($conn, "UPDATE places SET views = FLOOR(RAND() * 100)");
        echo "✅ จำลองยอดวิวเริ่มต้นเรียบร้อย";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . mysqli_error($conn);
    }
} else {
    echo "<h3 style='color: blue;'>ℹ️ ระบบนับยอดวิวพร้อมใช้งานอยู่แล้ว</h3>";
}

echo "<hr>";
echo "<a href='index.php' style='background: #FF416C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 20px;'>👉 กลับไปหน้าแรก</a>";
?>
