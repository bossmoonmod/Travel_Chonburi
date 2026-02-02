<?php
include "includes/connection.php";

$term = isset($_GET['term']) ? $_GET['term'] : '';
$suggestions = [];

if ($term) {
    $term = mysqli_real_escape_string($conn, $term);
    // ค้นหาชื่อสถานที่ที่ตรงกับคำค้น (จำกัด 4 รายการ)
    $sql = "SELECT id, name, image FROM places WHERE name LIKE '%$term%' LIMIT 4";
    $result = mysqli_query($conn, $sql);
    
    while($row = mysqli_fetch_assoc($result)) {
        $suggestions[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($suggestions);
?>
