<?php
include 'config.php';
$sql = "SELECT id, username, email, fullname, phone, age, profile_image, created_at 
        FROM members ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>รายชื่อสมาชิก</title>
 <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background: linear-gradient(135deg, #74b9ff, #0984e3);
    }
    .container {
        max-width: 1000px;
        margin: 50px auto;
        background: #fff;
        padding: 30px;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        animation: fadeIn 1s ease-in-out;
    }
    h2 {
        text-align: center;
        margin-bottom: 10px;
        color: #2d3436;
    }
    p {
        text-align: center;
        margin-bottom: 20px;
    }
    a {
        display: inline-block;
        background: #0984e3;
        color: #fff;
        padding: 10px 18px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s;
    }
    a:hover {
        background: #74b9ff;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        overflow: hidden;
        border-radius: 10px;
    }
    th, td {
        padding: 14px;
        text-align: left;
    }
    th {
        background: #0984e3;
        color: #fff;
    }
    tr:nth-child(even) {
        background-color: #f8f9fa;
    }
    tr:hover {
        background-color: #dfe6e9;
        transition: 0.3s;
    }
    .profile-image {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #0984e3;
    }
    .no-image {
        width: 55px;
        height: 55px;
        background-color: #b2bec3;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #2d3436;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
 </style>
</head>
<body>
 <div class="container">
    <h2>📋 รายชื่อสมาชิก</h2>
    <p><a href="register.php">➕ สมัครสมาชิกใหม่</a></p>

    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>รูปโปรไฟล์</th>
            <th>ชื่อผู้ใช้</th>
            <th>ชื่อ-นามสกุล</th>
            <th>อายุ</th>
            <th>อีเมล</th>
            <th>เบอร์โทร</th>
            <th>วันที่สมัคร</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                <?php if ($row['profile_image'] && file_exists($row['profile_image'])): ?>
                    <img src="<?php echo htmlspecialchars($row['profile_image']); ?>" 
                         alt="Profile" class="profile-image">
                <?php else: ?>
                    <div class="no-image">ไม่มีรูป</div>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['age'] ?? '-'); ?> ปี</td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['phone'] ?? '-'); ?></td>
            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p style="text-align:center; color:#636e72;">ยังไม่มีสมาชิกในระบบ</p>
    <?php endif; ?>
 </div>
</body>
</html>
<?php
$conn->close();
?>
