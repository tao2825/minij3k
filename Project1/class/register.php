<?php
include 'config.php';
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $age = isset($_POST['age']) ? (int)$_POST['age'] : 0;

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($fullname) || $age <= 0) {
        $error = "⚠️ กรุณากรอกข้อมูลให้ครบทุกช่อง";
    } elseif ($password !== $confirm_password) {
        $error = "❌ รหัสผ่านไม่ตรงกัน";
    } elseif (strlen($password) < 6) {
        $error = "🔑 รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } elseif ($age < 1 || $age > 120) {
        $error = "⚠️ กรุณากรอกอายุให้ถูกต้อง (1-120)";
    } else {
        // Check duplicate
        $check_sql = "SELECT id FROM members WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "🚫 ชื่อผู้ใช้หรืออีเมลนี้มีอยู่แล้ว";
        } else {
            // Upload profile image
            $profile_image = '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024;

                if (in_array($_FILES['profile_image']['type'], $allowed_types) &&
                    $_FILES['profile_image']['size'] <= $max_size) {

                    $upload_dir = 'uploads/';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                    $new_filename = uniqid() . '.' . $file_extension;
                    $upload_path = $upload_dir . $new_filename;

                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                        $profile_image = $upload_path;
                    } else {
                        $error = "⚠️ เกิดข้อผิดพลาดในการอัปโหลดรูปภาพ";
                    }
                } else {
                    $error = "❌ อัปโหลดได้เฉพาะ JPG, PNG, GIF และไม่เกิน 2MB";
                }
            }

            if (empty($error)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO members (username, email, password, fullname, phone, age, profile_image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssis", $username, $email, $hashed_password, $fullname, $phone, $age, $profile_image);

                if ($stmt->execute()) {
                    $message = "✅ สมัครสมาชิกสำเร็จ!";
                    $_POST = array();
                } else {
                    $error = "❌ เกิดข้อผิดพลาด: " . $stmt->error;
                }
                $stmt->close();
            }
        }
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>สมัครสมาชิก</title>
 <style>
    body {
        font-family: 'Prompt', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }
    .container {
        width: 100%;
        max-width: 450px;
        background: #fff;
        padding: 40px 30px;
        border-radius: 20px;
        box-shadow: 0px 10px 25px rgba(0,0,0,0.2);
        animation: fadeIn 1s ease-in-out;
    }
    h2 {
        text-align: center;
        margin-bottom: 25px;
        color: #333;
    }
    .form-group { margin-bottom: 18px; }
    label {
        display: block;
        font-weight: 600;
        margin-bottom: 6px;
        color: #444;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="file"],
    input[type="number"] {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 12px;
        box-sizing: border-box;
        transition: 0.3s;
        font-size: 15px;
    }
    input:focus {
        border-color: #667eea;
        box-shadow: 0 0 8px rgba(102, 126, 234, 0.4);
        outline: none;
    }
    input[type="submit"] {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: #fff;
        padding: 14px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        width: 100%;
        font-size: 16px;
        font-weight: bold;
        transition: 0.3s;
    }
    input[type="submit"]:hover {
        background: linear-gradient(135deg, #5a6de0, #693c9a);
        transform: translateY(-2px);
        box-shadow: 0px 5px 15px rgba(0,0,0,0.2);
    }
    .message {
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 8px;
        text-align: center;
        font-weight: bold;
        font-size: 14px;
    }
    .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .required { color: red; font-weight: normal; }
    p {
        text-align: center;
        font-size: 14px;
        margin-top: 20px;
    }
    p a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    p a:hover { text-decoration: underline; }
    small {
        display: block;
        margin-top: 5px;
        color: #888;
        font-size: 13px;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
 </style>
</head>
<body>
 <div class="container">
 <h2>สมัครสมาชิก</h2>

 <?php if ($message): ?>
 <div class="message success"><?php echo $message; ?></div>
 <?php endif; ?>

 <?php if ($error): ?>
 <div class="message error"><?php echo $error; ?></div>
 <?php endif; ?>

 <form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="username">ชื่อผู้ใช้ <span class="required">*</span></label>
        <input type="text" id="username" name="username"
        value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
    </div>

    <div class="form-group">
        <label for="fullname">ชื่อ-นามสกุล <span class="required">*</span></label>
        <input type="text" id="fullname" name="fullname"
        value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" required>
    </div>

    <div class="form-group">
        <label for="email">อีเมล <span class="required">*</span></label>
        <input type="email" id="email" name="email"
        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
    </div>

    <div class="form-group">
        <label for="password">รหัสผ่าน <span class="required">*</span></label>
        <input type="password" id="password" name="password" required>
        <small>รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</small>
    </div>

    <div class="form-group">
        <label for="confirm_password">ยืนยันรหัสผ่าน <span class="required">*</span></label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>

    <div class="form-group">
        <label for="age">อายุ <span class="required">*</span></label>
        <input type="number" id="age" name="age" min="1" max="120"
        value="<?php echo isset($_POST['age']) ? htmlspecialchars($_POST['age']) : ''; ?>" required>
    </div>

    <div class="form-group">
        <label for="phone">เบอร์โทรศัพท์</label>
        <input type="text" id="phone" name="phone"
        value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
    </div>

    <div class="form-group">
        <label for="profile_image">รูปโปรไฟล์</label>
        <input type="file" id="profile_image" name="profile_image" accept="image/*">
        <small>รองรับ JPG, PNG, GIF ขนาดไม่เกิน 2MB</small>
    </div>

    <input type="submit" value="สมัครสมาชิก">
 </form>

 <p>มีบัญชีแล้ว? <a href="login_members.php">เข้าสู่ระบบ</a></p>
 </div>
</body>
</html>
