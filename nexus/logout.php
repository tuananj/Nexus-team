<?php
session_start();

// 1. Xóa toàn bộ biến session
$_SESSION = array();

// 2. Nếu sử dụng cookie để lưu session, hãy xóa nó đi
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

// 3. Hủy bỏ phiên làm việc (session)
session_destroy();

// 4. Chuyển hướng về trang đăng nhập chính với giao diện đẹp
header("Location: login.php?message=logged_out");
exit();
?>s