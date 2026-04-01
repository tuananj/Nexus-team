<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'vendor/autoload.php';
require_once 'config.php';

function sendLoginMail($toEmail, $ip, $device, $time, $status) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $mail->Username = MAIL_USERNAME;
        $mail->Password = MAIL_PASSWORD;

        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(MAIL_USERNAME, 'Nexus Security');
        $mail->addAddress($toEmail);

        if ($status === 'Thiết bị lạ') {
            $mail->Subject = 'Cảnh báo đăng nhập từ thiết bị lạ';
            $messageStatus = "<p style='color:red;'>Phát hiện đăng nhập từ thiết bị lạ.</p>";
        } elseif ($status === 'IP mới') {
            $mail->Subject = 'Đăng nhập từ IP mới';
            $messageStatus = "<p style='color:orange;'>Thiết bị quen nhưng IP mới.</p>";
        } else {
            $mail->Subject = 'Đăng nhập thành công';
            $messageStatus = "<p style='color:green;'>Đăng nhập bình thường.</p>";
        }

        $mail->isHTML(true);
        $mail->Body = "
            <h3>Thông tin đăng nhập</h3>
            $messageStatus
            <p><b>Email:</b> " . htmlspecialchars($toEmail) . "</p>
            <p><b>IP:</b> " . htmlspecialchars($ip) . "</p>
            <p><b>Thiết bị:</b> " . htmlspecialchars($device) . "</p>
            <p><b>Thời gian:</b> " . htmlspecialchars($time) . "</p>
            <p><b>Trạng thái:</b> " . htmlspecialchars($status) . "</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        return false;
    }
}
?>