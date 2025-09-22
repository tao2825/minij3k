<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "class";
// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);
// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
 die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
// กําหนด charset เป็น utf8
$conn->set_charset("utf8");
?>