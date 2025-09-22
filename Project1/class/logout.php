<?php
session_start();
// ทำลาย session ทั้งหมด
session_unset();
session_destroy();
// ลบ session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
// redirect กลับไปหน้า login_members
header("Location: index.html?msg=logged_out");
exit();
