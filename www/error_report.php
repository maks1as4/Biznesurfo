<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$idFirm = 0;
$cl_mail = '';
$cl_text = '';
$cl_phone = '';
$nameFirm = '';
$err = '';

if ((isset($_SESSION ['ernf']))&&($_SESSION ['ernf']!='')) {
	$nameFirm = $_SESSION['ernf'];
}
if (count($_GET)>0) {
	if ((isset($_GET['fid']))&&($_GET['fid']!='')&&(CheckIntegerZeroUnsign($_GET['fid'])))
		$idFirm = $_GET['fid'];
	else
		require_once 'page404.php';
}
if (count($_POST)>0) {
	if (!(isset($_POST['btmes'])))
		die('Вы откуда?');
	if ((isset($_SERVER['HTTP_REFERER']))&&(!CheckRefer($_SERVER['HTTP_REFERER'])))
		die('Откуда?');
	if (!isset($_POST['ucode'])||($_POST['ucode']!=$_SESSION['umm_last_cim_code'])) {
		$err .= 'Не указан или указан неверно код защиты от спама.<br />';
	}
	if ((isset($_POST['uphone'])&&($_POST['uphone']!=''))) {
		$cl_phone = $_POST['uphone'];
		if (!CheckStr(4, $cl_phone, 26, false))
			$err .= 'Поле "Телефон" превышает допустимую длину (ограничение 25 символов).<br />';
	}
	if ((isset($_POST['umail'])&&($_POST['umail']!=''))) {
		$cl_mail = $_POST['umail'];
		if (!CheckEmail($cl_mail))
			$err .= 'E-mail имеет недопустимый формат.<br />';
	}
	if (($cl_mail=='')&&($cl_phone==''))
		$err .= 'Укажите хотя бы один из способов связаться с Вами: Телефон или e-mail. Иначе Ваше сообщение будет проигнорировано!<br />';
	if ((isset($_POST['utext'])&&($_POST['utext']!=''))) {
		$cl_text = $_POST['utext'];
		if (!CheckStr(4, $cl_text, 8192, false))
			$err .= 'Слишком большое сообщение (ограничение 8 кб).<br />';
	} else
		$err .= 'Не указан текст сообщения.<br />';
	if (get_magic_quotes_gpc()) {
		$cl_mail = stripslashes($cl_mail);
		$cl_phone = stripslashes($cl_phone);
		$cl_text = stripslashes($cl_text);
	}
	if ($err=='') {
		require_once "../includs/class.mailer.php";
		$mail = new Mailer();
		$mail->From = 'webrobot@biznesurfo.ru';
		$mail->FromName = 'Site BiznesUrfo Robot';
		$mail->Subject = 'Безукладниковой С.М. С карточки предприятия.';
		$err = 'С карточки предприятия '.$nameFirm.' (id='.$idFirm.') сайта www.biznesurfo.ru отправлено письмо, описывающее предполагаемые ошибки в реквизитах компании или в прайс-листе компании.'." \n\n".'Контактные данные отправителя:'." \n".'Электронная почта - ';
		if ($cl_mail=='')
			$err .= 'не указана';
		else
			$err .= $cl_mail;
		$err .= " \n".'Телефон - ';
		if ($cl_phone=='')
			$err .= 'не указан';
		else
			$err .= $cl_phone;
		$err .= " \n\n".'Текс сообщения: '."\n\n".$cl_text."\n\n ----\n".'Письмо было отправлено в '.date("H:i:s d.m.Y");
		$mail->Body = $err;
		$mail->AddAddress('reklama@biznesurfo.ru');
		if ($mail->Send())
			$err = 'Письмо успешно отправлено';
		else
			$err = $mail->ErrorInfo;
	} else
		$err = 'Ошибки: '.$err;
}
$forPred = true;
$exampl = GetExample();
$nameFirm = '';
$qRes = SqlQuery('Select C.name From CLIENTS C Where C.id='.$idFirm.';');
if ((mysql_num_rows($qRes)==1)&&($qRes)) {
	if ($Rows = mysql_fetch_row($qRes)) {
		$nameFirm = '&laquo;'.$Rows[0].'&raquo;';
	}
	@mysql_free_result($qRes);
}
if ($nameFirm=='')
	die('Такой фирмы не зарегистрировано. Возможно, Вы перешли по устаревшей ссылке');
$_SESSION ['ernf'] = $nameFirm;

$pagNam = 'error_report/'.$idFirm;
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
$tagH1 = 'Сообщить об ошибке в информации о компании '.$nameFirm;
$title = $tagH1;
$Expos = GetExposBlock();
$expoQty = count($Expos);

require_once '../includs/error_report.html';

?>