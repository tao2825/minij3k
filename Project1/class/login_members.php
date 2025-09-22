<?php
session_start();
require_once 'config.php'; // เชื่อมต่อ DB
$error = '';

// ตรวจสอบว่าล็อกอินอยู่แล้วหรือไม่
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        // ตรวจสอบว่าตาราง members มีอยู่จริงหรือไม่
        $checkTable = $conn->query("SHOW TABLES LIKE 'members'");
        if ($checkTable->num_rows == 0) {
            $error = "❌ ยังไม่มีตาราง members ในฐานข้อมูล กรุณาสร้างตารางก่อน";
        } else {
            $stmt = $conn->prepare("SELECT id, username, email, password, fullname
                                     FROM members WHERE username = ? OR email = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $username, $username);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        // เก็บข้อมูลใน session
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['full_name'] = $user['fullname'];
                        //$_SESSION['login_time'] = time();

                        header("Location: index.html");
                        exit();
                    } else {
                        $error = "❌ รหัสผ่านไม่ถูกต้อง";
                    }
                } else {
                    $error = "❌ ไม่พบผู้ใช้นี้ในระบบ";
                }
                $stmt->close();
            } else {
                $error = "⚠️ เกิดข้อผิดพลาดในการ query: " . $conn->error;
            }
        }
    } else {
        $error = "⚠️ กรุณากรอกชื่อผู้ใช้และรหัสผ่าน";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>เข้าสู่ระบบ</title>
<style>
    body {
        margin: 0;
        font-family: 'Prompt', sans-serif;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-container {
        width: 400px;
        background: #fff;
        padding: 35px;
        border-radius: 20px;
        box-shadow: 0 8px 30px rgba(0,0,0,0.2);
        animation: fadeIn 1s ease-in-out;
        text-align: center;
    }
    .login-header h1 {
        margin-bottom: 10px;
        font-size: 26px;
        color: #2575fc;
    }
    .login-header p {
        color: #666;
        margin-bottom: 20px;
        font-size: 14px;
    }
    .form-group { margin-bottom: 18px; text-align: left; }
    .form-label { display: block; margin-bottom: 6px; font-weight: 600; color: #2d3436; }
    .form-input {
        width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 12px;
        font-size: 16px; outline: none; transition: 0.3s;
    }
    .form-input:focus { border-color: #2575fc; box-shadow: 0 0 6px rgba(37,117,252,0.3); }
    .login-button {
        background: linear-gradient(135deg, #2575fc, #6a11cb);
        color: white; padding: 14px; width: 100%; border: none; border-radius: 12px;
        font-size: 18px; font-weight: 600; cursor: pointer; transition: 0.3s;
    }
    .login-button:hover {
        background: linear-gradient(135deg, #1a5ed7, #5411cb);
        transform: translateY(-2px);
        box-shadow: 0px 4px 12px rgba(0,0,0,0.2);
    }
    .error-message {
        background: #ffe3e3; color: #d63031; padding: 12px; border-radius: 10px;
        margin-bottom: 15px; font-size: 14px; text-align: center; font-weight: bold;
    }
    .links { margin-top: 18px; font-size: 14px; }
    .links a { color: #2575fc; font-weight: 600; text-decoration: none; margin: 0 5px; transition: 0.3s; }
    .links a:hover { text-decoration: underline; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(20px);} to { opacity: 1; transform: translateY(0);} }
</style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <h1>เข้าสู่ระบบ</h1>
        <p>กรอกข้อมูลเพื่อเข้าสู่ระบบการเรียนพิเศษ</p>
    </div>
    <?php if (!empty($error)): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($error); ?>
    </div>
    <?php endif; ?>
    <form method="POST">
        <div class="form-group">
            <label for="username" class="form-label">ชื่อผู้ใช้หรืออีเมล</label>
            <input type="text" id="username" name="username" class="form-input" placeholder="กรอกชื่อผู้ใช้หรืออีเมล" required>
        </div>
        <div class="form-group">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" id="password" name="password" class="form-input" placeholder="กรอกรหัสผ่าน" required>
        </div>
        <button type="submit" class="login-button">เข้าสู่ระบบ</button>
    </form>
    <div class="links">
        <p>ยังไม่มีบัญชี? <a href="register.php">สมัครสมาชิก</a></p>
        <p><a href="index.html">⬅ กลับไปหน้าหลัก</a></p>
    </div>
</div>
</body>
</html>
