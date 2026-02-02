<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sriracha_travel_db";   ##ให้กด NEW ใน SQL แล้วตั้งชื่อตามใน "" เพื่อให้ SQL Connect

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>
