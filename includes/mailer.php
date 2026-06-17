<?php
// mailer.php — cấu hình PHPMailer + hàm gửi OTP

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// chỉnh lại đường dẫn nếu thư mục PHPMailer ở chỗ khác
require __DIR__ . '/../PHPMailer/src/Exception.php';
require __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer/src/SMTP.php';

function sendOtpMail(string $toEmail, string $toName, string $otp): bool
{
    $mail = new PHPMailer(true);
    $mail->CharSet  = 'UTF-8';
    $mail->Encoding = 'base64';

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; //Server SMTP của Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'afoodahp@gmail.com';  // API Key
        $mail->Password   = 'tloj vbgr rihk scjh';  // Secret Key
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('afoodahp@gmail.com', 'Ăn húp hội');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = 'Mã xác thực OTP - Ăn Húp Hội';
        $mail->Body    = "
            <h3>Xin chào {$toName},</h3>
            <p>Mã OTP của bạn là: <strong>{$otp}</strong></p>
            <p>Mã này có hiệu lực trong 10 phút, đừng chia sẻ cho ai nhé.</p>
        ";
        $mail->AltBody = "Ma OTP cua ban la: {$otp} (co hieu luc trong 10 phut).";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // ghi log nếu muốn
        // error_log('Mail error: ' . $mail->ErrorInfo);
        return false;
    }
}
