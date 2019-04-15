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

if ($go301){
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://'.$translit_company.'.'.$url3Href.'/news');
	exit();
}

// проверка, если компания имеет доступ к кабинету и при этом End=0,
// то смотрим является ли пользователь владельцем страницы
// если нет, то 404
/*$end = -1;
$qRes = SqlQuery("Select (`rubrics` and `address` and `contacts`) From `END` Where `id_client`='".mysql_real_escape_string($id_company)."';");
if (mysql_num_rows($qRes) === 1){
	$Row = mysql_fetch_row($qRes);
	$end = $Row[0];
}
@mysql_free_result($qRes);
if ($end == 0){
	if (!isset($user_client_id))
		require_once '../page404.php';
	elseif ($user_client_id != $id_company)
		require_once '../page404.php';
}*/

// общие данные о компании
$Client = $Rubrics = array();
$name = $opf = $full_name = '';
$logo_width = $logo_height = $products_qty = 0;
$isOwner =	(isset($user_client_id) && CheckIntegerUnsign($user_client_id) && $user_client_id==$id_company) ||
			(isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='adm') ? 
			true : false;

$Client_data = getClientContent($id_company, $isOwner);
$Client = $Client_data[0];
if (empty($Client)) require_once 'page404.php';
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

$Phones = $Emails = $Sites = array();

// контактное лицо
$qRes = SqlQuery("Select `fio` From `MEMBERS` Where `id_client` = '".mysql_real_escape_string($id_company)."';");
if (mysql_num_rows($qRes) === 1){
	$Row = mysql_fetch_row($qRes);
	$fio = getDataFromDB($Row[0]);
}else
	$fio = '';
@mysql_free_result($qRes);

// все ящики
$qRes = SqlQuery("Select `email` From `CLIENT_EMAILS` Where `id_client` = '".mysql_real_escape_string($id_company)."';");
$count_emails = mysql_num_rows($qRes);
if ($count_emails > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Emails[] = $Rows;
}
@mysql_free_result($qRes);

// все сайты
$qRes = SqlQuery("Select `site` From `CLIENT_SITES` Where `id_client` = '".mysql_real_escape_string($id_company)."';");
$count_sites = mysql_num_rows($qRes);
if ($count_sites > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Sites[] = $Rows;
}
@mysql_free_result($qRes);

if ($Client[3]!='' && $Client[4]!='0'){
// filter
$coord = getDataFromDB($Client[3]);
$zoom  = getDataFromDB($Client[4]);
$addr  = getDataFromDB($Client[1]);
// java load
$jLoad = '<script src="http://api-maps.yandex.ru/2.0-stable/?load=package.standard&coordorder=longlat&lang=ru-RU" type="text/javascript"></script>'."\n";
// java
$java = <<<EoL
ymaps.ready(init);
var map, 
	myPlacemark;

function init(){
	map = new ymaps.Map ("YmapBig", {
		center: [$coord],
		zoom: $zoom
	});
	map.controls.add("mapTools").add("zoomControl").add("typeSelector");
	myPlacemark = new ymaps.Placemark([$coord], {
		iconContent: '$full_name',
		balloonContentHeader: '<strong>$full_name</strong>',
		balloonContentBody: '$addr'
	}, {
		preset: 'twirl#blueStretchyIcon'
	});
	map.geoObjects.add(myPlacemark);
}

EoL;
}

$action = 'contacts';
$title = 'Контактная информация компании '.$name.' - '.$sity_name.', адрес, телефон, схема проезда';
$descr = 'Контактная информация компании '.$name.' - '.$sity_name.', адрес, телефон, схема проезда';
$keyws = $name.', контакты, адрес, телефон, схема проезда';

$template = 'default/contacts.html';

require_once '../../includs/company/'.$template;

?>