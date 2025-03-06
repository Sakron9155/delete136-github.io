<?php
define('DB_HOST', '172.18.111.42');
define('DB_NAME', '6520310022_EliteTix');
define('DB_USER', '6520310022');
define('DB_PASS', '6520310022');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// ตั้งค่า charset เป็น utf8
$conn->set_charset("utf8");

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
?>
