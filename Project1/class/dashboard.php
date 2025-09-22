<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_members.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $subject = $_POST['subject'];
    $class_date = $_POST['class_date'];
    $class_time = $_POST['class_time'];

    if (!empty($subject) && !empty($class_date) && !empty($class_time)) {
        $stmt = $conn->prepare("INSERT INTO class_schedule (user_id, subject, class_date, class_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $subject, $class_date, $class_time);
        if ($stmt->execute()) {
            $message = "✅ บันทึกวิชาและเวลาเรียนเรียบร้อยแล้ว!";
        } else {
            $error = "❌ เกิดข้อผิดพลาด: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "⚠️ กรุณาเลือกวิชา วันที่ และเวลาเรียน";
    }
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, subject, class_date, class_time, created_at 
        FROM class_schedule 
        WHERE user_id = ? 
        ORDER BY class_date, class_time";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - เลือกเวลาเรียน</title>
<style>
    body {
        margin: 0;
        font-family: "Prompt", sans-serif;
        background: linear-gradient(135deg, #6c5ce7, #2575fc);
        min-height: 100vh;
    }

    /* 🔹 Navbar */
    nav {
        background: #fff;
        padding: 15px 20px;
        display: flex;
        justify-content: center;
        gap: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    nav a {
        text-decoration: none;
        color: #333;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 8px;
        transition: 0.3s;
    }
    nav a:hover {
        background: #2575fc;
        color: white;
    }

    /* 🔹 Dashboard */
    .dashboard-container {
        width: 900px;
        background: #fff;
        padding: 35px;
        border-radius: 20px;
        margin: 30px auto;
        box-shadow: 0px 8px 30px rgba(0,0,0,0.15);
        animation: fadeIn 0.8s ease-in-out;
    }
    @keyframes fadeIn {
        from {opacity: 0; transform: translateY(20px);}
        to {opacity: 1; transform: translateY(0);}
    }

    header {
        text-align: center;
        margin-bottom: 20px;
    }
    header h1 {
        color: #6c5ce7;
        margin-bottom: 5px;
    }

    /* 🔹 Message */
    .message {
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 15px;
    }
    .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

    /* 🔹 Schedule */
    .schedule {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }
    .schedule-item {
        background: #fafafa;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        border: 1px solid #eee;
        transition: 0.3s;
    }
    .schedule-item:hover {
        background: #f0f8ff;
        transform: translateY(-5px);
        border-color: #2575fc;
        box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
    }
    .schedule-item h3 {
        margin: 0;
        color: #2575fc;
    }

    /* 🔹 Form */
    form {
        margin-bottom: 25px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }
    select, input[type="date"], button {
        padding: 12px;
        font-size: 16px;
        border-radius: 10px;
        border: 2px solid #6c5ce7;
        outline: none;
        transition: 0.3s;
    }
    button {
        background: linear-gradient(135deg, #2575fc, #6c5ce7);
        color: #fff;
        font-weight: bold;
        cursor: pointer;
        border: none;
        padding: 12px 25px;
    }
    button:hover {
        background: linear-gradient(135deg, #1a5ed7, #5e3dc6);
        transform: scale(1.05);
        box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
    }

    /* 🔹 Table */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        border-radius: 12px;
        overflow: hidden;
    }
    th, td {
        padding: 14px;
        border-bottom: 1px solid #eee;
        text-align: center;
    }
    th {
        background: #6c5ce7;
        color: white;
    }
    tr:hover td {
        background: #f9f9ff;
    }

    /* 🔹 Action buttons */
    .btn-action {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        transition: 0.3s;
    }
    .btn-edit {
        background: #f0f8ff;
        color: #2575fc;
        border: 1px solid #2575fc;
    }
    .btn-edit:hover {
        background: #2575fc;
        color: #fff;
    }
    .btn-delete {
        background: #fff0f0;
        color: #d63031;
        border: 1px solid #d63031;
    }
    .btn-delete:hover {
        background: #d63031;
        color: #fff;
    }

    footer {
        text-align: center;
        margin-top: 30px;
        padding: 15px;
        color: #fff;
        font-size: 0.9rem;
    }
</style>
</head>
<body>

<!-- 🔹 Navbar -->
<nav>
    <a href="index.html">หน้าหลัก</a>
    <a href="members.php">สมาชิก</a>
    <a href="logout.php">ออกจากระบบ</a>
</nav>

<div class="dashboard-container">
    <header>
        <h1>📚 ระบบการเรียนพิเศษ</h1>
        <p>เลือกเวลาเรียนที่คุณต้องการ</p>
    </header>

    <?php if ($message): ?>
        <div class="message success"><?= $message; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="message error"><?= $error; ?></div>
    <?php endif; ?>

    <!-- ตารางวิชา -->
    <h2>📌 วิชาที่เปิดสอน</h2>
    <div class="schedule">
        <div class="schedule-item"><h3>คณิตศาสตร์</h3><p>ครูผู้สอน: ครูกี ลีลาดี</p></div>
        <div class="schedule-item"><h3>วิทยาศาสตร์</h3><p>ครูผู้สอน: ครูหมี ใหญ่ยาว</p></div>
        <div class="schedule-item"><h3>ภาษาอังกฤษ</h3><p>ครูผู้สอน: ครูมาร์ค สปีคดี</p></div>
        <div class="schedule-item"><h3>ประวัติศาสตร์</h3><p>ครูผู้สอน: ครูโชคชัย ไมค์พิรมพร</p></div>
        <div class="schedule-item"><h3>ภาษาไทย</h3><p>ครูผู้สอน: ครูสมชาย พิรมพร</p></div>
        <div class="schedule-item"><h3>เทคโนโลยีสารสนเทศ</h3><p>ครูผู้สอน: ครูสมหมาย จิตใจ</p></div>
    </div>

    <!-- ฟอร์ม -->
    <h2>📝 เลือกวิชาและเวลาเรียน</h2>
    <form method="POST">
        <select name="subject" required>
            <option value="">-- เลือกวิชา --</option>
            <option value="คณิตศาสตร์">คณิตศาสตร์</option>
            <option value="วิทยาศาสตร์">วิทยาศาสตร์</option>
            <option value="ภาษาอังกฤษ">ภาษาอังกฤษ</option>
            <option value="ประวัติศาสตร์">ประวัติศาสตร์</option>
            <option value="ภาษาไทย">ภาษาไทย</option>
            <option value="เทคโนโลยีสารสนเทศ">เทคโนโลยีสารสนเทศ</option>
        </select>
        <input type="date" name="class_date" required>
        <select name="class_time" required>
            <option value="">-- เลือกเวลาเรียน --</option>
            <option value="09:00 - 10:00">09:00 - 10:00</option>
            <option value="10:00 - 11:00">10:00 - 11:00</option>
            <option value="13:00 - 14:00">13:00 - 14:00</option>
            <option value="14:00 - 15:00">14:00 - 15:00</option>
            <option value="19:00 - 20:00">19:00 - 20:00</option>
        </select>
        <button type="submit">บันทึก</button>
    </form>

    <!-- ตารางเรียน -->
    <h2>📅 ตารางเรียนของคุณ</h2>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>วิชา</th>
                <th>วันที่</th>
                <th>เวลาเรียน</th>
                <th>วันที่บันทึก</th>
                <th>จัดการ</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['subject']); ?></td>
                <td><?= date('d/m/Y', strtotime($row['class_date'])); ?></td>
                <td><?= htmlspecialchars($row['class_time']); ?></td>
                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                <td>
                    <a class="btn-action btn-edit" href="edit_schedule.php?id=<?= $row['id']; ?>">✏️ แก้ไข</a>
                    <a class="btn-action btn-delete" href="delete_schedule.php?id=<?= $row['id']; ?>" onclick="return confirm('คุณแน่ใจว่าต้องการลบวิชานี้?');">🗑️ ลบ</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align:center;">⏳ ยังไม่มีการเลือกวิชาเรียน</p>
    <?php endif; ?>
</div>

<footer>
    © 2025 ระบบการเรียนพิเศษ | All Rights Reserved
</footer>
</body>
</html>
