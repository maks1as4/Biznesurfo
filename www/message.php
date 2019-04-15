<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$cl_mail = '';
$cl_text = '';
$err = '';
if (count($_POST)>0) {
	if (!(isset($_POST['btmes'])))
		die('Вы откуда?');
	if ((isset($_SERVER['HTTP_REFERER']))&&(!CheckRefer($_SERVER['HTTP_REFERER'])))
		die('Откуда?');
	if (!isset($_POST['ucode'])||($_POST['ucode']!=$_SESSION['umm_last_cim_code'])) {
		$err .= 'Не указан или указан неверно код защиты от спама.<br />';
	}
	if ((isset($_POST['umail'])&&($_POST['umail']!=''))) {
		$cl_mail = $_POST['umail'];
		if (!CheckEmail($cl_mail))
			$err .= 'E-mail имеет недопустимый формат.<br />';
	} else
		$err .= 'Не указан e-mail.<br />';
	if ((isset($_POST['utext'])&&($_POST['utext']!=''))) {
		$cl_text = $_POST['utext'];
		if (!CheckStr(4, $cl_text, 8192, false))
			$err .= 'Слишком большое сообщение (ограничение 8 кб).<br />';
	} else
		$err .= 'Не указан текст сообщения.<br />';
	if (get_magic_quotes_gpc()) {
		$cl_mail = stripslashes($cl_mail);
		$cl_text = stripslashes($cl_text);
	}
	if ($err=='') {
		require_once '../includs/class.mailer.php';
		$mail = new Mailer();
		$mail->From = 'webrobot@biznesurfo.ru';
		$mail->FromName = 'Site BiznesUrfo Robot';
		$mail->Subject = 'Предложения по Сайту';
		$mail->Body = 'Со страницы "Сообщить об ошибке" сайта www.biznesurfo.ru отправлено письмо. При этом был указан обратный адрес: '.$cl_mail." \n\n".'Текс сообщения: '."\n\n".$cl_text."\n\n ----\n".'Письмо было отправлено в '.date("H:i:s d.m.Y");
		$mail->AddAddress('webadmin@biznesurfo.ru');
		if ($mail->Send())
			$err = 'Письмо успешно отправлено';
		else
			$err = $mail->ErrorInfo;
	} else
		$err = 'Ошибки: '.$err;
}

$pagNam = 'message';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$jqAdd = "\n$('#podpiska').jqTransform({imgPath:'js/jqtransform/img/'});\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Письмо в редакцию';
$crumbsQty = 2;
$title = 'Оставить замечания и предложения. Сообщить об ошибке на сайте Индустрия бизнеса';
$Expos = GetExposBlock();
$expoQty = count($Expos);

require_once '../includs/message.html';

?>