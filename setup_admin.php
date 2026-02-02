<?php
include "includes/connection.php";

echo "<h2>กำลังติดตั้งระบบ Admin...</h2>";

// 1. สร้างตาราง Admin
$sql = "CREATE TABLE IF NOT EXISTS admins (
    id int(11) NOT NULL AUTO_INCREMENT,
    username varchar(50) NOT NULL,
    password varchar(255) NOT NULL,
    name varchar(100) NOT NULL,
    created_at timestamp DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    UNIQUE KEY username (username)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

if (mysqli_query($conn, $sql)) {
    echo "✅ ตาราง admins พร้อมใช้งาน<br>";
} else {
    echo "❌ สร้างตารางไม่สำเร็จ: " . mysqli_error($conn) . "<br>";
}

// 2. เคลียร์ข้อมูลเก่า (ถ้ามี) แล้วเพิ่ม User Admin
mysqli_query($conn, "TRUNCATE TABLE admins");

$pass = password_hash("1234", PASSWORD_DEFAULT);
$sql_insert = "INSERT INTO admins (username, password, name) VALUES ('admin', '$pass', 'Administrator')";

if (mysqli_query($conn, $sql_insert)) {
    echo "✅ เพิ่ม User: admin / Pass: 1234 เรียบร้อย<br>";
    echo "<hr>";
    echo "<h3>🎉 เสร็จสิ้น!</h3>";
    echo "<a href='admin/login.php'>👉 ไปหน้าเข้าสู่ระบบ</a>";
} else {
    echo "❌ เพิ่มข้อมูลไม่สำเร็จ: " . mysqli_error($conn);
}
?>
