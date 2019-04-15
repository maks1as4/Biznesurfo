<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$idFirm = 0;
$cl_mail = '';
$cl_text = '';
$do_email = false;
$nameFirm = '';
$mailFirm = '';
$err = '';

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
	if ((isset($_POST['umail'])&&($_POST['umail']!=''))) {
		$cl_mail = $_POST['umail'];
		if (!CheckEmail($cl_mail))
			$err .= 'E-mail имеет недопустимый формат.<br />';
	} else
		$err .= 'Не указан e-mail.<br />';
	if ((isset($_POST['utext'])&&($_POST['utext']!=''))) {
		$cl_text = $_POST['utext'];
		if (!CheckStr(4, $cl_text, 8192, false))
			$err .= 'Слишком большое сообщение (ограничение 8200 символов).<br />';
	} else
		$err .= 'Не указан текст сообщения.<br />';
	if ($err!='')
		$err = 'Ошибки: '.$err;
	$do_email = true;
	if (get_magic_quotes_gpc()) {
		$cl_mail = stripslashes($cl_mail);
		$cl_text = stripslashes($cl_text);
	}
}

$qRes = SqlQuery("Select C.name, (Select email From CLIENT_EMAILS Where id_client = C.id Order by sort_order Limit 1) From CLIENTS C Where C.id='".mysql_real_escape_string($idFirm)."';");
if ((mysql_num_rows($qRes)===1)&&($qRes)) {
	if ($Rows = mysql_fetch_row($qRes)) {
		$nameFirm = '&laquo;'.$Rows[0].'&raquo;';
		$mailFirm = ($Rows[1] != null) ? $Rows[1] : '';
	}
	@mysql_free_result($qRes);
}
if ($nameFirm=='')
	die('Такой фирмы не зарегистрировано. Возможно, Вы перешли по устаревшей ссылке');
if ($mailFirm=='')
	die('У фирмы '.$nameFirm.' электронный почтовый ящик отсутствует');
if ($do_email&&($err=='')) {
	sendEmail($err);
	$err = 'Письмо успешно отправлено';
	$_POST['umail'] = '';
	$_POST['utext'] = '';
}

$forPred = true;
$exampl = GetExample();

$pagNam = 'mailto/'.$idFirm;
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$jqAdd = "\n$('#podpiska').jqTransform({imgPath:'js/jqtransform/img/'});\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = 'firms';
$Crumbs[0][1] = 'Предприятия';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Отправка письма';
$crumbsQty = 2;
$tagH1 = 'Письмо в компанию '.$nameFirm;
$title = $tagH1.' - Индустрия Бизнеса';
$Expos = GetExposBlock();
$expoQty = count($Expos);

require_once '../includs/firmmail.html';

function sendEmail(&$err) {
	global $cl_mail, $cl_text, $nameFirm, $mailFirm;
	$cl_text = nl2br($cl_text);
	$s = <<<EofMess
<html lang="ru">
<head>
<title>Письмо с сайта Biznesurfo.ru</title>
</head>
<style type="text/css">
h1 {
 color: #1E42A6;
 font: bold 16px Arial, sans-serif;
}
p {
 color: #1E42A6;
 text-align: left;
 font-weight: bold;
}
.mess {
 color: #000000;
 font: 12px Arial, sans-serif;
 text-align: justify;
 text-indent: 1.5em;
}
span {
 color: #1E42A6;
 font: italic 14px Arial, sans-serif;
}
.mmt td {
 border-top-width: 0;
 border-right-width: 0;
 border-bottom-width: 0;
 border-left-width: 0;
 border-color: #000000;
 border-style: solid;
 margin: 0;
 padding: 10px 10px 10px 10px;
}
</style>
<body bgcolor="#FFFFFF">
<table class="mmt" cellspacing="0" border="0" width="600">
<tr><td><a href="http://www.biznesurfo.ru/" target="_blank"><img src="cid:ib_logo.jpg" style="border: 0px; display: block; margin: 0px 10px 0px 0px;" /></a>
</td></tr>
<tr><td style="background-color: #EEEEEE;">
	<h1>Уважаемый, $nameFirm</h1>
	<p>С нашего сайта в адрес Вашей компании было отправлено письмо следующего содержания:</p>
	<table cellspacing="0" border="0" style="background-color: #FFFFFF; margin-left: 25px; padding: 10px 20px 10px 20px; width: 90%;"><tr><td>
		<p class="mess">$cl_text</p>
	</td></tr></table>
	<p>E-mail отправителя: $cl_mail</p>
</td></tr>
<tr><td><span>С уважением, редакция Справочника &laquo;Индустрия Бизнеса&raquo;<br>620075, г. Екатеринбург ул. Бажова, 51 оф. 49<br>Тел/факс: +7(343) 27-00-127<br>e-mail: <a href="mailto:info@biznesurfo.ru">info@biznesurfo.ru</a></span>
</td></tr>
</table>
</body>
</html>
EofMess;

	require_once "../includs/class.mailer.php";
	$mail = new Mailer();
	$mail->IsHTML(true);
	$mail->From = 'webrobot@biznesurfo.ru';
	$mail->FromName = 'Справочник "Индустрия Бизнеса"';
	$mail->Subject = 'Письмо с сайта "Индустрия Бизнеса"';
	$mail->AddEmbeddedImage('i/ib_color.jpg', 'ib_logo.jpg', 'logo IB', 'base64', 'image/jpeg');
	$mail->Body = $s;
	$mail->AddAddress($mailFirm);
	if ($mail->Send())
		$err = 'Письмо успешно отправлено';
	else
		$err = $mail->ErrorInfo;

	$mail2 = new Mailer();
	$mail2->IsHTML(true);
	$mail2->From = 'webrobot@biznesurfo.ru';
	$mail2->FromName = 'Site BiznesUrfo Robot';
	$mail2->Subject = 'Письмо на адрес '.$mailFirm;
	$mail2->AddEmbeddedImage('i/ib_color.jpg', 'ib_logo.jpg', 'logo IB', 'base64', 'image/jpeg');
	$mail2->Body = $s;
	$mail2->AddAddress('webadmin@biznesurfo.ru');
	$mail2->Send();
}

?>