<?php

require_once '../includs/head.php';

if (!isset($_SESSION['change_password']['email']) || ($_SESSION['change_password']['email'] == ''))
	require_once 'page404.php';
if (!isset($_SESSION['change_password']['hash']) || ($_SESSION['change_password']['hash'] == ''))
	require_once 'page404.php';

$email = $_SESSION['change_password']['email'];
$hash  = $_SESSION['change_password']['hash'];
unset ($_SESSION['change_password']);

$jqAdd = <<<EoL

	$('#timer').show();

	var TimerDiv_obj = $('#timer span');
	var timetogo = 29;
	TimerDiv_obj.text('�������� ' + (timetogo + parseInt(1)) + ' ���.');
	var timer = window.setInterval(function() {
		TimerDiv_obj.text('�������� ' + timetogo + ' ���.');
		if (timetogo <= 0) {
			window.clearInterval(timer);
			window.location = '/';
		}
		timetogo--;
	}, 1000);


EoL;

$title = '���������� �� ����� ������ ����������';

require_once '../includs/forgot_password_send.html';

/* --- �������� ������ --- */

require_once '../extensions/PHPMailer/class.phpmailer.php';
$message = file_get_contents('mail_blank/change_password.html');
$placeholders = array ('/\[=HASH\]/', '/\[=BASE\]/');
$valible = array ($hash, $baseHref);
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

$mail->SetFrom($mailBot, '��������� �������');
if ($sendForUser)
	$mail->AddAddress($email);
else
	$mail->AddAddress('slash@biznesurfo.ru');
$mail->Subject = '��������� ������� - ������� ������ - www.biznesurfo.ru';
$mail->MsgHTML($message);
$mail->Send();

$mail->ClearAddresses();
$mail->ClearAttachments();

/* --- /�������� ������ --- */

?>