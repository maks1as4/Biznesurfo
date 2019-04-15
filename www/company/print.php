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
	header('Location: http://'.$translit_company.'.'.$url3Href.'/print');
	exit();
}

// Запрос информации клиента
$Client = $Phones = $Emails = $Sites = array();
$qRes = SqlQuery("
	Select C.`name`, C.`address`, C.`logo`, C.`coord`, C.`map_zoom`, CA.`about`
	From `CLIENTS` C
		left join `CLIENTS_ABOUT` CA on C.`id`=CA.`id_client`
	Where C.`id`='".$id_company."';
");
if (mysql_num_rows($qRes) === 1){
	$Client = mysql_fetch_row($qRes);
}else
	require_once '../page404.php';
@mysql_free_result($qRes);

// получаем все телефоны компании (минимум 1)
$qRes = SqlQuery("Select `full_phone` From `CLIENT_PHONES` Where `id_client`='".$id_company."' Order by `sort_order`;");
$count_phones = mysql_num_rows($qRes);
if ($count_phones > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Phones[] = $Rows;
}
@mysql_free_result($qRes);

// получаем все емейлы компании
$qRes = SqlQuery("Select `email` From `CLIENT_EMAILS` Where `id_client`='".$id_company."' Order by `sort_order`;");
$count_emails = mysql_num_rows($qRes);
if ($count_emails > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Emails[] = $Rows;
}
@mysql_free_result($qRes);

// получаем все сайты компании
$qRes = SqlQuery("Select `site` From `CLIENT_SITES` Where `id_client`='".$id_company."' Order by `sort_order`;");
$count_sites = mysql_num_rows($qRes);
if ($count_sites > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Sites[] = $Rows;
}
@mysql_free_result($qRes);

// Обработка логотипа
if ($Client[2] != ''){
	$imgsize = getimagesize('../logo/'.$Client[2]);
	$Client[2] = 'alt="'.$Client[0].'" src="logo/'.$Client[2].'"';
	if ((count($imgsize)>1)&&($imgsize[0]>360))
		$Client[2] .= ' width="360"';
}

// YandexMap - static
if (($Client[3]!='') && ($Client[4]!='')){
	$Client[4] += 1;
	$YandexStatImg = '<img xmlns="http://www.w3.org/1999/xhtml" xmlns:lego="https://lego.yandex-team.ru" xmlns:dev="http://dev.yandex.ru/xmlns" alt="" src="http://static-maps.yandex.ru/1.x/?l=map&ll='.$Client[3].'&pt='.$Client[3].',pmors&z='.$Client[4].'&size=646,396&key='.$yandex_key.'" />';
}

// Обработка телефонов
$all_phones = '';
if ($count_phones > 1){
	for ($i=1; $i<$count_phones; $i++){
		$all_phones .= $Phones[$i][0].',<br />';
	}
	$all_phones = substr($all_phones, 0, -7);
}

// Обработка сайтов
$all_sites = '';
if ($count_sites > 0){
	$first_site = $Sites[0][0];
	foreach ($Sites as $site){
		$all_sites .= $site[0].' , ';
	}
	$all_sites = substr($all_sites, 0, -3);
}

// Обработка ящиков
$all_emails = '';
if ($count_emails > 0){
	foreach ($Emails as $email){
		$all_emails .= $email[0].' , ';
	}
	$all_emails = substr($all_emails, 0, -3);
}

// Список рубрик в которых размещается компания
$Rubs = array ();
$qRes = SqlQuery('Select R.new_url, R.name, R.id_parent, R.id, C.cnt_str From CLIENT_RUBRICS C join RUBRICS R on R.id=C.rubric Where C.client='.$id_company.';');
if ((mysql_num_rows($qRes)>0)&&($qRes)){
	while ($Rows = mysql_fetch_row($qRes))
		$Rubs[] = $Rows;
	@mysql_free_result($qRes);
}
$rubsQty = count($Rubs);
$rubrics = $rubsQty>0;
/*if (!$rubrics){
	$qRes = SqlQuery('Select COALESCE(C.url, "q"), C.name, 0, 0 From CLIENT_ACTIVITIES C Where C.client='.$id_company.';');
	if ((mysql_num_rows($qRes)>0)&&($qRes)){
		while ($Rows = mysql_fetch_row($qRes))
			$Rubs[] = $Rows;
		@mysql_free_result($qRes);
	}
	$rubsQty = count($Rubs);
}*/
for ($i = 0; $i<$rubsQty; $i++)
	if (($Rubs[$i][2]==62)&&($Rubs[$i][3]!=149)&&($Rubs[$i][3]!=150)&&($Rubs[$i][3]!=156))
		$Rubs[$i][1] .= ' инструмент';

$time = DataParser(date('Y-m-d H:i:s'));

$pagNam = 'company_print/'.$id_company.'.html';

$cityFormAction = $pagNam;
@list ($nm, $opf) = explode(',', $Client[0]);
$tagH1 = $opf.' '.$nm;

$keyws = '';
$descr = '';
$title = $nm.' - карточка компании - печать';

require_once '../../includs/company/public/print.html';

?>