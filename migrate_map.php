<?php
include "includes/connection.php";

echo "<h2>กำลังปรับปรุงโครงสร้างฐานข้อมูล...</h2>";

// 1. เพิ่มคอลัมน์ map_link (ถ้ายังไม่มี)
$sql = "SHOW COLUMNS FROM places LIKE 'map_link'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // ยังไม่มี: สร้างใหม่ และเอาข้อมูลเก่ามาแปลง (ถ้ามี)
    $sql_alter = "ALTER TABLE places ADD COLUMN map_link TEXT DEFAULT NULL AFTER detail";
    if (mysqli_query($conn, $sql_alter)) {
        echo "✅ เพิ่มคอลัมน์ map_link สำเร็จ<br>";
        
        // แปลงข้อมูลเก่า (Lat,Lng -> Link)
        $sql_select = "SELECT id, latitude, longitude FROM places";
        $res = mysqli_query($conn, $sql_select);
        while($row = mysqli_fetch_assoc($res)) {
            if($row['latitude'] && $row['longitude']) {
                $link = "https://www.google.com/maps?q=" . $row['latitude'] . "," . $row['longitude'];
                $id = $row['id'];
                mysqli_query($conn, "UPDATE places SET map_link = '$link' WHERE id = $id");
            }
        }
        echo "✅ แปลงข้อมูลพิกัดเก่าเป็นลิงก์ Google Maps เรียบร้อย<br>";
        
        // ลบคอลัมน์เก่าออก (Optional: เก็บไว้ก่อนก็ได้ แต่เพื่อความสะอาดเราลบเลย)
        mysqli_query($conn, "ALTER TABLE places DROP COLUMN latitude");
        mysqli_query($conn, "ALTER TABLE places DROP COLUMN longitude");
        echo "✅ ลบคอลัมน์ latitude/longitude เก่าออกแล้ว<br>";
        
    } else {
        echo "❌ เพิ่มคอลัมน์ map_link ไม่สำเร็จ: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "ℹ️ คอลัมน์ map_link มีอยู่แล้ว ไม่ต้องทำอะไร<br>";
}

echo "<hr>";
echo "<h3>เสร็จสิ้น! คุณสามารถกลับไปใช้งานได้เลย</h3>";
echo "<a href='admin/index.php'>👉 กลับหน้า Admin</a>";
?>
