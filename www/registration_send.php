<?php

require_once '../includs/head.php';

if (!isset($_SESSION['registration']['email']) || ($_SESSION['registration']['email'] == '')){
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://'.$baseHref.'/add-company');
	exit();
}
if (!isset($_SESSION['registration']['fio']) || ($_SESSION['registration']['fio'] == '')){
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://'.$baseHref.'/add-company');
	exit();
}
if (!isset($_SESSION['registration']['hash']) || ($_SESSION['registration']['hash'] == '')){
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://'.$baseHref.'/add-company');
	exit();
}

$email = $_SESSION['registration']['email'];
$fio   = $_SESSION['registration']['fio'];
$hash  = $_SESSION['registration']['hash'];
unset ($_SESSION['registration']);

$jqAdd = <<<EoL

	$('#timer').show();

	var TimerDiv_obj = $('#timer span');
	var timetogo = 29;
	TimerDiv_obj.text('осталось ' + (timetogo + parseInt(1)) + ' сек.');
	var timer = window.setInterval(function() {
		TimerDiv_obj.text('осталось ' + timetogo + ' сек.');
		if (timetogo <= 0) {
			window.clearInterval(timer);
			window.location = '/';
		}
		timetogo--;
	}, 1000);


EoL;

$title = 'Письмо успешно отправлено';

require_once '../includs/registration_send.html';

/* --- отправка письма --- */

require_once '../extensions/PHPMailer/class.phpmailer.php';
$message = file_get_contents('mail_blank/confirm.html');
$placeholders = array ('/\[=FIO\]/', '/\[=HASH\]/', '/\[=BASE\]/');
$valible = array ($fio, $hash, $baseHref);
$message = preg_replace($placeholders, $valible, $message);

$mail = new PHPMailer();

$mail->CharSet  = 'windows-1251';
$mail->Encoding = 'base64';

$mail->IsSMTP();

$mail->Host       = 'mail.biznesurfo.ru';
$mail->SMTPDebug  = false;
$mail->SMTPAuth   = true;
$mail->Port       = 25;
$mail->Username   = $mailBot;
$mail->Password   = $mailBotPass;

$mail->SetFrom($mailBot, 'Индустрия бизнеса');
if ($sendForUser)
	$mail->AddAddress($email);
else
	$mail->AddAddress('slash@biznesurfo.ru');
$mail->Subject = 'Индустрия бизнеса - подтверждение регистрации на сайте - www.biznesurfo.ru';
//$mail->AddEmbeddedImage($_SERVER["DOCUMENT_ROOT"].'/mail_blank/logo.jpg', 'logo_id', 'logo.jpg', 'base64', 'image/jpeg');
//$mail->AddEmbeddedImage($_SERVER["DOCUMENT_ROOT"].'/mail_blank/blank.gif', 'blank_id', 'blank.gif', 'base64', 'image/gif');
$mail->MsgHTML($message);
$mail->Send();

$mail->ClearAddresses();
$mail->ClearAttachments();

/* --- /отправка письма --- */

?>