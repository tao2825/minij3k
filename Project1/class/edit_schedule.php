<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login_members.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// ดึงข้อมูลเดิมมาแสดงในฟอร์ม
$stmt = $conn->prepare("SELECT subject, class_date, class_time FROM class_schedule WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();
$stmt->close();

// ถ้าไม่มีข้อมูล ให้ส่งกลับไปหน้า dashboard
if (!$data) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $class_date = $_POST['class_date'];
    $class_time = $_POST['class_time'];

    $stmt = $conn->prepare("UPDATE class_schedule SET subject=?, class_date=?, class_time=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sssii", $subject, $class_date, $class_time, $id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>แก้ไขตารางเรียน</title>
<style>
    body {
        font-family: 'Prompt', sans-serif;
        background: linear-gradient(135deg, #a29bfe, #6c5ce7);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 0;
        color: #333;
    }
    .edit-container {
        width: 100%;
        max-width: 500px;
        background: #fff;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        text-align: center;
        animation: fadeIn 0.8s ease-in-out;
    }
    .edit-container h2 {
        color: #6c5ce7;
        margin-bottom: 25px;
        font-size: 28px;
    }
    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }
    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #4a4a4a;
    }
    .form-input {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 2px solid #ddd;
        border-radius: 12px;
        box-sizing: border-box;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    .form-input:focus, select:focus {
        border-color: #6c5ce7;
        box-shadow: 0 0 8px rgba(108, 92, 231, 0.3);
        outline: none;
    }
    .form-select {
        width: 100%;
        padding: 12px;
        font-size: 16px;
        border: 2px solid #ddd;
        border-radius: 12px;
        box-sizing: border-box;
        transition: border-color 0.3s, box-shadow 0.3s;
    }
    .submit-button {
        width: 100%;
        padding: 15px;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        color: #fff;
        font-size: 18px;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .submit-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(108, 92, 231, 0.3);
    }
    .back-link {
        display: inline-block;
        margin-top: 20px;
        color: #6c5ce7;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.3s;
    }
    .back-link:hover {
        color: #3f36a4;
        text-decoration: underline;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>
<div class="edit-container">
    <h2>📝 เลือกวิชาและเวลาเรียน</h2>
    <form method="POST">
        <div class="form-group">
            <label class="form-label" for="subject">วิชา</label>
            <select class="form-select" id="subject" name="subject" required>
                <option value="">-- เลือกวิชา --</option>
                <option value="คณิตศาสตร์" <?= ($data['subject'] == 'คณิตศาสตร์') ? 'selected' : ''; ?>>คณิตศาสตร์</option>
                <option value="วิทยาศาสตร์" <?= ($data['subject'] == 'วิทยาศาสตร์') ? 'selected' : ''; ?>>วิทยาศาสตร์</option>
                <option value="ภาษาอังกฤษ" <?= ($data['subject'] == 'ภาษาอังกฤษ') ? 'selected' : ''; ?>>ภาษาอังกฤษ</option>
                <option value="ประวัติศาสตร์" <?= ($data['subject'] == 'ประวัติศาสตร์') ? 'selected' : ''; ?>>ประวัติศาสตร์</option>
                <option value="ภาษาไทย" <?= ($data['subject'] == 'ภาษาไทย') ? 'selected' : ''; ?>>ภาษาไทย</option>
                <option value="เทคโนโลยีสารสนเทศ" <?= ($data['subject'] == 'เทคโนโลยีสารสนเทศ') ? 'selected' : ''; ?>>เทคโนโลยีสารสนเทศ</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="class_date">วันที่</label>
            <input class="form-input" type="date" id="class_date" name="class_date" value="<?= $data['class_date'] ?? ''; ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label" for="class_time">เวลาเรียน</label>
            <select class="form-select" id="class_time" name="class_time" required>
                <option value="">-- เลือกเวลาเรียน --</option>
                <option value="09:00 - 10:00" <?= ($data['class_time'] == '09:00 - 10:00') ? 'selected' : ''; ?>>09:00 - 10:00</option>
                <option value="10:00 - 11:00" <?= ($data['class_time'] == '10:00 - 11:00') ? 'selected' : ''; ?>>10:00 - 11:00</option>
                <option value="13:00 - 14:00" <?= ($data['class_time'] == '13:00 - 14:00') ? 'selected' : ''; ?>>13:00 - 14:00</option>
                <option value="14:00 - 15:00" <?= ($data['class_time'] == '14:00 - 15:00') ? 'selected' : ''; ?>>14:00 - 15:00</option>
                <option value="19:00 - 20:00" <?= ($data['class_time'] == '19:00 - 20:00') ? 'selected' : ''; ?>>19:00 - 20:00</option>
            </select>
        </div>
        <button class="submit-button" type="submit">บันทึกการแก้ไข</button>
    </form>
    <a href="dashboard.php" class="back-link">ย้อนกลับไปหน้าเลือกเวลาเรียน</a>
</div>
</body>
</html>
