<?php
include "includes/connection.php";

echo "<h2>🛠️ กำลังซ่อมแซมและอัปเดตฐานข้อมูล...</h2>";

// 1. ตรวจสอบว่ามีคอลัมน์ map_link หรือยัง
$check = mysqli_query($conn, "SHOW COLUMNS FROM places LIKE 'map_link'");
if (mysqli_num_rows($check) == 0) {
    // ถ้าไม่มี ให้สร้างใหม่
    $sql = "ALTER TABLE places ADD COLUMN map_link TEXT DEFAULT NULL AFTER detail";
    if (mysqli_query($conn, $sql)) {
        echo "<h3 style='color: green;'>✅ สร้างคอลัมน์ 'map_link' สำเร็จ!</h3>";
    } else {
        echo "<h3 style='color: red;'>❌ สร้างคอลัมน์ไม่สำเร็จ: " . mysqli_error($conn) . "</h3>";
    }
} else {
    echo "<h3 style='color: blue;'>ℹ️ คอลัมน์ 'map_link' มีอยู่แล้ว (โอเค)</h3>";
}

// 2. เคลียร์ค่าว่างให้เป็น NULL หรือค่าเริ่มต้น (ป้องกัน Error อื่นๆ) link เก่าๆ
mysqli_query($conn, "UPDATE places SET map_link = '' WHERE map_link IS NULL");

echo "<hr>";
echo "<h3>🎉 ซ่อมแซมเรียบร้อยแล้ว!</h3>";
echo "<p>กดปุ่มด้านล่างเพื่อกลับไปลองบันทึกข้อมูลใหม่ได้เลยครับ</p>";
echo "<a href='admin/form_place.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>👉 กลับไปหน้าเพิ่มข้อมูล</a>";
?>
