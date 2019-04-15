<?php

session_start()||die('Без cookeis дальнейшая работа не возможна.');
require_once "../funcs/config.php";
require_once "../funcs/check_funcs.php";
require_once "../funcs/dbconnect.php";
if (!(ConnectDB() ))
	Show_Critical_Error('Необходима авторизация');
$target = '';

if (count($_GET)==3) {
	if (isset($_GET['url']))
		$target = $_GET['url'];
	$isBanner = 0;
	if (isset($_GET['obj']))
		if ($_GET['obj']=='banners')
			$isBanner = 1;
		elseif ($_GET['obj']=='tmodule')
			$isBanner = 2;
	if ((isset($_SESSION ['ccfind']))&&(CheckInteger($_SESSION['ccfind'], 1, 1))) {
		$atFind = 1;
		unset($_SESSION ['ccfind']);
	} else
		$atFind = 0;
	switch ($isBanner) {
		case 1 : {
				if ((isset($_GET['myccid']))&&($_GET['myccid']!='')) {
					$curObj = preg_replace('/\/$/', '', $_GET['myccid']);
					if (CheckStr(6, $curObj, 0, false)) {
						if ($atFind==0) {
							if ((isset($_SESSION ['ccrubr']))&&(CheckIntegerUnsign($_SESSION['ccrubr']))) {
								$idRubric = $_SESSION ['ccrubr'];
								unset($_SESSION ['ccrubr']);
							} else
								$idRubric = 0;
							$s = 'B.rubric = '.$idRubric.' and ';
						} else
							$s = '';
						if (preg_match("/^[0-9]+$/", $curObj)) {
							$where_val = 'B.id';
							$ext_val = '';
						}else{
							$where_val = 'B.name';
							$ext_val = '.swf';
						}
						if ($isLogStat) {
							$sql =	'Insert into CLICK_BT '.
									'Select B.id, 1, curdate(), 1 '.
									'From BANNERS B '.
									'Where '.$where_val.' = "'.$curObj.$ext_val.'" '.
									'limit 1 '.
									'on duplicate key update cnt=cnt+1';
							SqlQuery($sql);
						}
					} else
						die('not checked');
				}
				break;
			}
		case 2 : {
				if ((isset($_GET['myccid']))&&($_GET['myccid']!='')) {
					$s = preg_replace('/\/$/', '', $_GET['myccid']);
					if (CheckIntegerUnsign($s)) {
						if ($isLogStat)
							SqlQuery('Insert into CLICK_BT values ('.$s.',0,curdate(),1) on duplicate key update cnt=cnt+1');
					}
				}
				break;
			}
	}
	if ($target!='') {
		if (($target == 'firm') || ($target == 'price')){
			if (!preg_match("/^[0-9]+$/", $curObj)) $curObj .= '.swf';
			$qRes = SqlQuery("
				Select B.`client`
				From `BANNERS` B
				Where B.`id` = '".mysql_real_escape_string($curObj)."' or B.`name` = '".mysql_real_escape_string($curObj)."' Limit 1;
			");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$id_client = $Row[0];
			}else
				die('Сообщите об ошибке!');
			@mysql_free_result($qRes);

			if ($target == 'firm')
				header('Location: '.'http://'.$baseHref.'/company/'.$id_client);
			else
				header('Location: '.'http://'.$baseHref.'/price_'.$id_client);

		}else{
			$p = strpos($target, 'http://');
			if ($p!==false&&$p===0)
				header("Location: $target");
			else
				header("Location: http://$target");
		}
	} else
		die('Сообщите об ошибке!');
} else
	die('Спасибо что потестировали!');
?>