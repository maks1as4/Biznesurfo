<?php
require_once "../funcs/config.php";
require_once "../funcs/check_funcs.php";
require_once "../funcs/biz_funcs.php";
require_once "../funcs/dbconnect.php";
if (!(ConnectDB() ))
	Show_Critical_Error('Необходима авторизация');
$target = '';
if (count ($_GET) == 2) {
	if (isset($_GET['url']))
		$target = $_GET['url'];
	if ((isset($_GET['id']))&&($_GET['id']!='')&&(CheckIntegerZeroUnsign($_GET['id']))) {
		$userId = 0;
		if ((isset($_COOKIE['usernumber']))&&($_COOKIE['usernumber']!='')&&(CheckIntegerUnsign($_COOKIE['usernumber']) ))
			$userId = $_COOKIE['usernumber'];
		PutClientStat($_GET['id'], 1);
	}
}
elseif (count ($_GET) == 1) {
	if (isset($_GET['url']))
		$target = $_GET['url'];
}else
	die('Недопустимая операция');
if ($target != '') {
	$p = strpos($target, 'biznesurfo');
	if ($p===false){
		$url = parse_url('http://'.$target);
		if ($url){
			$host = iconv("cp1251", "utf-8", $url['host']);
			require_once '../extensions/punycode_convert/idna_convert.class.php';
			$idn = new idna_convert(array('idn_version'=>2008));
			$host = $idn->encode($host);
			$target = $host.$url['path'];
		}
		header("Location: http://$target");
	}else
		die('Некорректный адрес');
}
?>