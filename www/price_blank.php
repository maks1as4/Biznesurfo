<?php
@require_once '../includs/head.php';

$cl_opf = '';
$cl_fio = '';
$cl_con_phone = '';
for ($i=1;$i<6;$i++) {
	$cl_pos{$i} = '';
	$cl_cost{$i} = '';
}
$rows_exists = false;
$file_exists = false;
$fl_name = '';
$err 	 = '';
if (count ($_POST) > 0) {
	if (!(isset ($_POST['btmes']))) die ('Вы откуда?');
	if ((isset ($_SERVER['HTTP_REFERER'])) && (!CheckRefer ($_SERVER['HTTP_REFERER']))) die ('Откуда?');
	if(!isset($_POST['ucode'])||($_POST['ucode']!=$_SESSION['umm_last_cim_code'])){
		$err .= 'Не указан или указан неверно код защиты от спама.<br />';
	}	
	if (count($_FILES)==1) {
		$valid_types =  array('doc','xls','txt','docx', 'xlsx');
		$tarDir = $_SERVER['DOCUMENT_ROOT'].'price/';
//		$tarDir = $_SERVER['DOCUMENT_ROOT'].'/price/';
		foreach ($_FILES as $fl) {
			if ($fl['error']<4) {
				$file_exists = true;
				if (($fl['error']==0) && (is_uploaded_file($fl['tmp_name'])) ) { // файл загружен
					$tfn = $fl['tmp_name'];
					$ext = substr ($fl['name'], 1 + strrpos ($fl['name'], '.') );
					if (!in_array($ext, $valid_types)) 
						$err .= 'Недопустимый формат файла '.$fl['name'].'<br />';
					elseif ($fl['size']<512001) {
							$fl_name = $tarDir.basename($tfn).'.'.$ext;
							@chmod ($tarDir, 0755);
							if (!(@move_uploaded_file($tfn, $fl_name)))
							  $err .= 'Ошибка при копировании файла '.$fl['name'].'<br />';
						} else {
							$err .= 'Превышен размер файла '.$fl['name'].'<br />';
						}
				} else	$err .= 'Ошибка при загрузке файла '.$fl['name'].' Код: '.$fl['error'].'<br />';
			}
		}
	}
	$Enum = array('первой', 'второй', 'третьей', 'четвертой', 'пятой');
	for ($i=1;$i<6;$i++) {
		if ((isset ($_POST["uname$i"]) && ($_POST["uname$i"] != ''))) {
			$cl_pos{$i} = $_POST["uname$i"];
			if (!CheckStr (4, $cl_pos{$i}, 512, false) )
				$cl_pos{$i} = substr($cl_pos{$i}, 0, 512);
			if ((isset ($_POST["ucost$i"]) && ($_POST["ucost$i"] != ''))) {
				$cl_cost{$i} = $_POST["ucost$i"];
				if (CheckStr (4, $cl_cost{$i}, 48, false) ) 
					$cl_cost{$i} = substr($cl_cost{$i}, 0, 48);
				$rows_exists = true;
			} else	$err .= 'Не указана "Цена" '.$Enum[$i-1].' позиции.<br />';
		}
	}
	if ((isset ($_POST['ufullname']) && ($_POST['ufullname'] != ''))) {
		$cl_opf = $_POST['ufullname'];
		if (!CheckStr (4, $cl_opf, 220, false) )
			$err .= 'Слишком длинное название организации (ограничение 220 символов).<br />';
	} else	$err .= 'Не указано "ОПФ, Полное название организации".<br />';
	if ((isset ($_POST['ufio']) && ($_POST['ufio'] != ''))) {
		$cl_fio = $_POST['ufio'];
		if (!CheckStr (4, $cl_fio, 127, false) )
			$cl_fio = substr($cl_fio, 0, 127);
	} else	$err .= 'Не заполнено поле "Фамилия, Имя, Отчество".<br />';
	if ((isset ($_POST['uconphone']) && ($_POST['uconphone'] != ''))) {
		$cl_con_phone= $_POST['uconphone'];
		if (!CheckStr (4, $cl_con_phone, 20, false) )
			$err .= 'Поле "Контактный телефон" превышает допустимую длину (ограничение 20 символов).<br />';
	} else	$err .= 'Не заполнено поле "Контактный телефон".<br />';
	if (get_magic_quotes_gpc()) {
		$cl_opf = stripslashes($cl_opf);
		$cl_fio = stripslashes($cl_fio);
		$cl_con_phone = stripslashes($cl_con_phone);
		for ($i=1;$i<6;$i++) {
			$cl_pos{$i} = stripslashes($cl_pos{$i});
			$cl_cost{$i} = stripslashes($cl_cost{$i});
		}
	}
	if (!($file_exists || $rows_exists))
		$err = 'Вы не прикрепили прайс-лист и не указали ни одной позиции.<br>'.$err;
	if ($err=='') {
		@require_once "../includs/class.mailer.php";
		$mail = new Mailer();
		$mail->From     = 'webrobot@biznesurfo.ru';
		$mail->FromName = 'Site BiznesUrfo Robot';
		$mail->Subject  = 'Безукладниковой С.М. Заявка на добавление прайс-листа';
		$err = 'С сайта www.biznesurfo.ru подана заявка на добавление прайс-листа в наш каталог для публикации на страницах наших изданий. При этом было указано:'." \n\n".'ОПФ, Полное название организации: '.$cl_opf." \n\n".'ФИО контактного лица: '.$cl_fio." \n".'Контактный телефон лица: '.$cl_con_phone." \n\n";
		if ($file_exists) {
			$err .= 'Сам прайс-лист смотрите во вложении к письму.'."\n\n";
			$mail->AddAttachment ($fl_name , 'Price.'.$ext);
		}
		if ($rows_exists) {
			$err .= 'Позиции для размещения:'."\n";
			for ($i=1;$i<6;$i++) {
				$err .= $i.'. Наименование:"'.$cl_pos{$i}.'"; Цена:"'.$cl_cost{$i}.'"'."\n";
			}
		}
		$err .= " \n\n ----\n".'Время подачи заявки - '.date("H:i:s d.m.Y");
		$mail->Body     = $err;
		$mail->AddAddress('reklama@biznesurfo.ru');
		if ($mail->Send()) 
			$err = 'Заявка отправлена';
		else	$err = $mail->ErrorInfo;		
		if ($file_exists) {
			unlink ($fl_name);
			@chmod ($tarDir, 0555);
		}
	} else	$err = 'Ошибки: '.$err;
}
if (count ($_GET) > 0) {
	if ( (isset ($_GET['rcid'])) && ($_GET['rcid'] != '')) {
		$flCity = $_GET['rcid'];
		$s = $flCity[0];
		$idReg = substr($flCity, 1);
		if (!((($s=='r') || ($s=='c')) && (CheckIntegerZeroUnsign($idReg)))) {
			$idReg = 0;
			$flCity = '';
		}
		$isReg = ($s=='r');
	}
}

$pagNam = 'blank/price';
if ($flCity!='')	$findParStr = '?rcid='.$flCity;
$Cities = GetCities();
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$jqAdd = "\n$('#podpiska').jqTransform({imgPath:'js/jqtransform/img/'});\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Оформление заявки на добавление прайс-листа';
$crumbsQty = 2;
$title = 'Добавить прайс-лист компании: перечень предоставляемых товаров и услуг';
$Expos = GetExposBlock();
$expoQty = count($Expos);

@require_once '../includs/price_blank.html'; 
?>