<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$cl_name = '';
$cl_work = 0;
$cl_region = '';
$cl_raion = '';
$cl_index = '';
$cl_city = '';
$cl_street = '';
$cl_house = '';
$cl_office = '';
$cl_aya = '';
$cl_phone = '';
$cl_mail = '';
$cl_site = '';
$cl_fio = '';
$cl_post = '';
$cl_src = '';
$cl_what = true;
$err = '';
if (count($_POST)>0) {
	if (!(isset($_POST['btmes'])))
		die('Вы откуда?');
	if ((isset($_SERVER['HTTP_REFERER']))&&(!CheckRefer($_SERVER['HTTP_REFERER'])))
		die('Откуда?');
	if (!isset($_POST['ucode'])||($_POST['ucode']!=$_SESSION['umm_last_cim_code'])) {
		$err .= 'Не указан или указан неверно код защиты от спама.<br />';
	}
	$cl_what = (isset($_POST['what'])&&($_POST['what']!='jrn'));
	if ((isset($_POST['uname'])&&($_POST['uname']!=''))) {
		$cl_name = $_POST['uname'];
		if (!CheckStr(4, $cl_name, 511, false))
			$err .= 'Слишком длинное название организации (ограничение 220 символов).<br />';
	} else
		$err .= 'Не указано название организации.<br />';
	if ((isset($_POST['uwork'])&&($_POST['uwork']!=''))) {
		if (!CheckInteger($cl_work, 0, 12))
			$cl_work = 13;
		else
			$cl_work = $_POST['uwork'];
	} else
		$err .= 'Не указана сфера деятельности организации.<br />';
	if ((isset($_POST['uregion'])&&($_POST['uregion']!=''))) {
		$cl_region = $_POST['uregion'];
		if (!CheckStr(4, $cl_region, 255, false))
			$err .= 'Поле "Регион" превышает допустимую длину (ограничение 254 символа).<br />';
	} else
		$err .= 'Не заполнено поле "Регион".<br />';
	if ((isset($_POST['uraion'])&&($_POST['uraion']!=''))) {
		$cl_raion = $_POST['uraion'];
		if (!CheckStr(4, $cl_raion, 255, false))
			$err .= 'Поле "Район" превышает допустимую длину (ограничение 254 символа.)<br />';
	} else
		$err .= 'Не заполнено поле "Район".<br />';
	if ((isset($_POST['uindex'])&&($_POST['uindex']!=''))) {
		$cl_index = $_POST['uindex'];
		if (!CheckIntegerUnsign($cl_index))
			$err .= 'В поле "Почтовый индекс" допускается только ввод цифр.<br />';
	} else
		$err .= 'Не заполнено поле "Почтовый индекс".<br />';
	if ((isset($_POST['ucity'])&&($_POST['ucity']!=''))) {
		$cl_city = $_POST['ucity'];
		if (!CheckStr(4, $cl_city, 127, false))
			$err .= 'Поле "Город" превышает допустимую длину (ограничение 126 символов).<br />';
	} else
		$err .= 'Не заполнено поле "Город".<br />';
	if ((isset($_POST['ustreet'])&&($_POST['ustreet']!=''))) {
		$cl_street = $_POST['ustreet'];
		if (!CheckStr(4, $cl_street, 127, false))
			$err .= 'Поле "Улица" превышает допустимую длину (ограничение 126 символов).<br />';
	}
	if ((isset($_POST['uhouse'])&&($_POST['uhouse']!=''))) {
		$cl_house = $_POST['uhouse'];
		if (!CheckStr(4, $cl_house, 15, false))
			$err .= 'Поле "Дом" превышает допустимую длину (ограничение 15 символов).<br />';
	}
	if ((isset($_POST['uoff'])&&($_POST['uoff']!=''))) {
		$cl_office = $_POST['uoff'];
		if (!CheckStr(4, $cl_office, 15, false))
			$err .= 'Поле "Офис" превышает допустимую длину (ограничение 15 символов).<br />';
	}
	if ((isset($_POST['uaya'])&&($_POST['uaya']!=''))) {
		$cl_aya = $_POST['uaya'];
		if (!CheckStr(4, $cl_aya, 15, false))
			$err .= 'Поле "Абонентский ящик" превышает допустимую длину (ограничение 15 символов).<br />';
	}
	if ($cl_street=='') {
		if ($cl_aya=='')
			$err .= 'Если Вы не указали улицу, заполните поле "Абонентский ящик".<br />';
	} else {
		if ($cl_house=='')
			$err .= 'Если Вы указали улицу, заполните поле "Дом".<br />';
	}
	if ((isset($_POST['uphone'])&&($_POST['uphone']!=''))) {
		$cl_phone = $_POST['uphone'];
		if (!CheckStr(4, $cl_phone, 127, false))
			$err .= 'Поле "Телефон/факс" превышает допустимую длину (ограничение 126 символов).<br />';
	} else
		$err .= 'Не заполнено поле "Телефон/факс".<br />';
	if ((isset($_POST['umail'])&&($_POST['umail']!=''))) {
		$cl_mail = $_POST['umail'];
		if (!CheckEmail($cl_mail))
			$err .= 'E-mail имеет недопустимый формат.<br />';
	}
	if ((isset($_POST['usite'])&&($_POST['usite']!=''))) {
		$cl_site = $_POST['usite'];
		if (!CheckStr(4, $cl_site, 63, false))
			$cl_site = substr($cl_site, 0, 63);
	}
	if ((isset($_POST['ufio'])&&($_POST['ufio']!=''))) {
		$cl_fio = $_POST['ufio'];
		if (!CheckStr(4, $cl_fio, 221, false))
			$err .= 'Поле "Получатель" превышает допустимую длину (ограничение 220 символов).<br />';
	} else
		$err .= 'Не заполнено поле "Получатель".<br />';
	if ((isset($_POST['upost'])&&($_POST['upost']!=''))) {
		$cl_post = $_POST['upost'];
		if (!CheckStr(4, $cl_post, 221, false))
			$err .= 'Слишком длинное описание должности (ограничение 220 символов).<br />';
	}
	if ((isset($_POST['usrc'])&&($_POST['usrc']!=''))) {
		$cl_src = $_POST['usrc'];
		if (!CheckStr(4, $cl_src, 512, false))
			$cl_src = substr($cl_src, 0, 512);
	}
	if (get_magic_quotes_gpc()) {
		$cl_name = stripslashes($cl_name);
		$cl_work = stripslashes($cl_work);
		$cl_region = stripslashes($cl_region);
		$cl_raion = stripslashes($cl_raion);
		$cl_index = stripslashes($cl_index);
		$cl_city = stripslashes($cl_city);
		$cl_street = stripslashes($cl_street);
		$cl_house = stripslashes($cl_house);
		$cl_office = stripslashes($cl_office);
		$cl_aya = stripslashes($cl_aya);
		$cl_phone = stripslashes($cl_phone);
		$cl_mail = stripslashes($cl_mail);
		$cl_site = stripslashes($cl_site);
		$cl_fio = stripslashes($cl_fio);
		$cl_post = stripslashes($cl_post);
		$cl_src = stripslashes($cl_src);
	}
	if ($err=='') {
		require_once "../includs/class.mailer.php";
		$mail = new Mailer();
		$mail->From = 'webrobot@biznesurfo.ru';
		$mail->FromName = 'Site BiznesUrfo Robot';
		$mail->Subject = 'Безукладниковой С.М. Заявка на оформление подписки';
		$err = 'С сайта www.biznesurfo.ru подана заявка на подписку. При этом были указаны следующие реквизиты:'." \n\n".'Название организации: '.$cl_name." \n\n".'Сфера деятельности: ';
		switch ($cl_work) {
			case 0 : {
					$err .= 'Металлопрокат, метизы, металлы';
					break;
				}
			case 1 : {
					$err .= 'Общепромышленное оборудование';
					break;
				}
			case 2 : {
					$err .= 'Электрооборудование, промышленная электроника';
					break;
				}
			case 3 : {
					$err .= 'Оборудование специализированных отраслей';
					break;
				}
			case 4 : {
					$err .= 'Промышленные материалы';
					break;
				}
			case 5 : {
					$err .= 'Строительные и отделочные материалы';
					break;
				}
			case 6 : {
					$err .= 'Оборудование для строительства и ЖКХ';
					break;
				}
			case 7 : {
					$err .= 'Инструмент';
					break;
				}
			case 8 : {
					$err .= 'Услуги, сопутствующие материалы';
					break;
				}
			case 9 : {
					$err .= 'Автомобили, транспорт';
					break;
				}
			case 10 : {
					$err .= 'Торговое и складское оборудование';
					break;
				}
			case 11 : {
					$err .= 'Офисное оборудование';
					break;
				}
			case 12 : {
					$err .= 'Рекламное агенство';
					break;
				}
		}
		$err .= " \n\n".'Фактический адрес -'."\n".'Регион (область, республика): '.$cl_region." \n".'Район: '.$cl_raion." \n".'Почтовый индекс: '.$cl_index." \n".'Город (населенный пункт): '.$cl_city." \n".'Улица: ';
		if ($cl_street=='')
			$err .= 'не указана';
		else
			$err .= $cl_street;
		$err .= " \n".'Дом: ';
		if ($cl_house=='')
			$err .= 'не указан';
		else
			$err .= $cl_house;
		$err .= " \n".'Офис: ';
		if ($cl_office=='')
			$err .= 'не указан';
		else
			$err .= $cl_office;
		$err .= " \n".'А/я: ';
		if ($cl_aya=='')
			$err .= 'не указан';
		else
			$err .= $cl_aya;
		$err .= " \n\n".'Телефон: '.$cl_phone." \n".'Электронная почта: ';
		if ($cl_mail=='')
			$err .= 'не указана';
		else
			$err .= $cl_mail;
		$err .= " \n".'Сайт: ';
		if ($cl_site=='')
			$err .= 'не указан';
		else
			$err .= $cl_site;
		$err .= " \n\n".'Получатель : '.$cl_fio." \n".'Должность : ';
		if ($cl_post=='')
			$err .= 'не указана';
		else
			$err .= $cl_post;
		$err .= " \n\n".'Получать : ';
		if ($cl_what==true)
			$err .= 'Диск';
		else
			$err .= 'Журнал';
		$err .= " \n\n".'Ответ на вопрос о справочнике : ';
		if ($cl_src=='')
			$err .= 'не указан';
		else
			$err .= $cl_src;
		$err .= " \n\n ----\n".'Время подачи заявки - '.date("H:i:s d.m.Y");
		$mail->Body = $err;
		$mail->AddAddress('reklama@biznesurfo.ru');
		if ($mail->Send())
			$err = 'Заявка отправлена';
		else
			$err = $mail->ErrorInfo;
	} else
		$err = 'Ошибки: '.$err;
}
if (count($_GET)>0) {
	if ((isset($_GET['rcid']))&&($_GET['rcid']!='')) {
		$flCity = $_GET['rcid'];
		$s = $flCity[0];
		$idReg = substr($flCity, 1);
		if (!((($s=='r')||($s=='c'))&&(CheckIntegerZeroUnsign($idReg)))) {
			$idReg = 0;
			$flCity = '';
		}
		$isReg = ($s=='r');
	}
}

$pagNam = 'subscribe';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$jqAdd = "\n$('#podpiska').jqTransform({imgPath:'js/jqtransform/img/'});\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Оформление подписки на издания';
$crumbsQty = 2;
$title = 'Подписка на справочник Индустрия бизнеса';
$Expos = GetExposBlock();
$expoQty = count($Expos);

require_once '../includs/order_blank.html';

?>