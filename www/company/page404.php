<?php

require_once '../../includs/head.php';

$id_company = 0;

if (count($_GET)>0){
	if ((isset($_GET['sfid'])) && ($_GET['sfid']!='')){
		$getData = CheckIDTranslit($_GET['sfid']);
		$id_company = $getData[0];
		$translit_company = $getData[1];
		$go301 = $getData[2];
	}
}

if ($id_company === 0)
	require_once '../page404.php';

// общие данные о компании
$Client = $Rubrics = $Client_phones = array();
$name = $opf = $full_name = '';
$logo_width = $logo_height = $products_qty = 0;
$isOwner =	(isset($user_client_id) && CheckIntegerUnsign($user_client_id) && $user_client_id==$id_company) ||
			(isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='adm') ? 
			true : false;

$Client_data = getClientContent($id_company, $isOwner);
$Client = $Client_data[0];
if (empty($Client)) require_once '../page404.php';
$Client_phones = $Client_data[7];
$name = getDataFromDB($Client_data[1][0]);
$opf = (isset($Client_data[1][1])) ? getDataFromDB($Client_data[1][1]) : '';
$full_name = ($opf != '') ? $opf.' '.$name : $name;
if ($full_name == '') require_once 'page404.php';
$logo_width = $Client_data[2][0];
$logo_height = $Client_data[2][1];
$Rubrics = $Client_data[3];
if (!empty($Client_data[4])) $products_qty = $Client_data[4];
$sity_name = getDataFromDB($Client_data[5]);

// логируем ошибку
if ($isLog404) {
	SqlQuery('insert into ERROR404 (url,ip,user_agent,referer,is_robot) values ('.
			"'".mysql_real_escape_string(substr(@$_SERVER['SERVER_NAME'].@$_SERVER['REQUEST_URI'], 0, 1000))."',".
			"'".mysql_real_escape_string(substr(@$_SERVER['REMOTE_ADDR'], 0, 18))."',".
			"'".mysql_real_escape_string(substr(@$_SERVER['HTTP_USER_AGENT'], 0, 1000))."',".
			"'".mysql_real_escape_string(substr(@$_SERVER['HTTP_REFERER'], 0, 1000))."',".
			$isRobot.")");
}

$action = 'page404';
$title = 'Страница не найдена - ошибка 404';
$descr = '';
$keyws = '';

header("HTTP/1.1 404 Not Found");

$template = 'default/page404.html';

require_once '../../includs/company/'.$template;

die;

?>