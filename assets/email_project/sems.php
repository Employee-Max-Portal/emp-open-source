<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $smtp_host   = $_POST['smtp_host'] ?? '';
    $smtp_auth   = filter_var($_POST['smtp_auth'] ?? false, FILTER_VALIDATE_BOOLEAN);
    $smtp_user   = $_POST['smtp_user'] ?? '';
    $smtp_pass   = $_POST['smtp_pass'] ?? '';
    $smtp_secure = $_POST['smtp_secure'] ?? '';
    $smtp_port   = $_POST['smtp_port'] ?? '';

    $from_email  = $_POST['from_email'] ?? '';
    $from_name   = $_POST['from_name'] ?? '';
    $to_email    = $_POST['to_email'] ?? '';
    $to_name     = $_POST['to_name'] ?? '';
    $subject     = $_POST['subject'] ?? '';
    $body        = $_POST['body'] ?? '';

    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = $smtp_auth;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port       = $smtp_port;

        // Sender and recipient
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to_email, $to_name);

        // Optional: Add CC recipients
        if (!empty($_POST['cc'])) {
            $cc_list = explode(',', $_POST['cc']);
            foreach ($cc_list as $cc_email) {
                $cc_email = trim($cc_email);
                if (filter_var($cc_email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addCC($cc_email);
                }
            }
        }

        // ✅ Optional: Add BCC recipients
        if (!empty($_POST['bcc'])) {
            $bcc_list = explode(',', $_POST['bcc']);
            foreach ($bcc_list as $bcc_email) {
                $bcc_email = trim($bcc_email);
                if (filter_var($bcc_email, FILTER_VALIDATE_EMAIL)) {
                    $mail->addBCC($bcc_email);
                }
            }
        }

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        echo json_encode(["status" => "success", "message" => "Email sent successfully"]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $mail->ErrorInfo]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>