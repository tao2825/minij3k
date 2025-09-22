<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    $sql = "INSERT INTO schedules (subject, date, time) 
            VALUES ('$subject', '$date', '$time')";

    if ($conn->query($sql) === TRUE) {
        echo "<p>✅ บันทึกเวลาเรียนสำเร็จ</p>";
        echo "<p><a href='dashboard.php'>กลับไปหน้า Dashboard</a></p>";
    } else {
        echo "❌ เกิดข้อผิดพลาด: " . $conn->error;
    }

    $conn->close();
}
?>
