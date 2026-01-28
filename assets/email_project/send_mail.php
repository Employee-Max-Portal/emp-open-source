<?php
/* $recipient = 'sakibshahriar02@gmail.com';
$key = 'dsadi1';
$name = 'Tanvir'; */
require_once "config.php";

$recipient = $argv[1];
$name = $argv[2];
$type = $argv[3];


if ($type=='reset_password'){
	$key = $argv[4];
	$reset_pass_url = "https://voice.tolpar.com.bd/web_apps/sohub_connect/authentication/pwreset?key=$key";
	$mail_subject = 'Reset your user password of SOHUB Connect International';
	$mail_body = "
			<!DOCTYPE html>
			<html>
			<head>
			</head>
			<body>
			<p>Dear $name,</p>
			<p>Please reset your password from the below URL:</p>
			<p><a href='$reset_pass_url'>$reset_pass_url</a></p>
			<p>Thank You,<br>SOHUB</p>
			</body>
			</html>
			";
	
}

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = "$smtp_host";
    $mail->SMTPAuth = true;
    $mail->Username = "$smtp_user";
    $mail->Password = "$smtp_pass";
    $mail->SMTPSecure = "$smtp_encryption";
    $mail->Port = $smtp_port;
    $mail->setFrom("$email");
    $mail->addAddress("$recipient");
    $mail->isHTML(true);
    $mail->Subject = $mail_subject;
    $mail->Body = "hello";

    $mail->send();
    echo 'Email sent successfully';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
