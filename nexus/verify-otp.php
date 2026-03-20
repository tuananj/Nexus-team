<?php
session_start();
if (!isset($_SESSION['temp_user'])) {
    header("Location: index.php");
    exit();
}
echo "Chào " . $_SESSION['temp_user']['username'] . ", hãy nhập mã 6 số từ điện thoại!";
?>
<form action="check-otp.php" method="POST">
    <input type="text" name="otp" placeholder="Nhập 6 số OTP" required>
    <button type="submit">Xác nhận</button>
</form>