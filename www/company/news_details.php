<?php

require_once '../../includs/head.php';

$id_company = 0;
$id_news = 0;

if (count($_GET)>0){
	if ((isset($_GET['sfid'])) && ($_GET['sfid']!='')){
		$getData = CheckIDTranslit($_GET['sfid']);
		$id_company = $getData[0];
		$translit_company = $getData[1];
		$go301 = $getData[2];
	}
	if ((isset($_GET['nid'])) && ($_GET['nid']!='') && (CheckIntegerUnsign($_GET['nid'])))
		$id_news = $_GET['nid'];
	if (isset($_GET['translit']))
		$translit = $_GET['translit'];
}

if (($id_company === 0) || ($id_news === 0))
	require_once '../page404.php';

if ($go301){
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://'.$translit_company.'.'.$url3Href.'/news/'.$translit.'.html');
	exit();
}

$isOwner =	(isset($user_client_id) && CheckIntegerUnsign($user_client_id) && $user_client_id==$id_company) ||
			(isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='adm') ? 
			true : false;

// получаем данные новости
$sql_filter = (!$isOwner) ? " and CN.`visible` = '1' and CN.`status` = '0'" : "";
$News = array();
$qRes = SqlQuery("Select C.`id`, C.`name`, CN.`title`, CN.`text`, CN.`img`, CN.`ext`, CN.`adate`, CN.`status`, CN.`url` From `CLIENT_NEWS` CN join `CLIENTS` C on CN.`id_client`=C.`id` Where CN.`id`='".mysql_real_escape_string($id_news)."' and CN.`id_client` = '".mysql_real_escape_string($id_company)."'".$sql_filter.";");
if (mysql_num_rows($qRes) === 1)
	$News = mysql_fetch_row($qRes);
else{
	if ($translit_company != '')
		require_once 'page404.php';
	else
		require_once '../page404.php';
}
@mysql_free_result($qRes);

if ((isset($translit))&&($translit!='')&&(strcasecmp($News[8],$translit)!=0)){
	$loc = 'Location: '.($translit_company==''?'/company/'.$id_company:'').'/news/'.$News[8].'.html';
	header("HTTP/1.1 301 Moved Permanently");
	header($loc);
	exit;
}

if ($translit_company == ''){
#---- компания или не зарегистрированна или не имеет домен 3го уровня ----#

$forPred = true;

@list ($nm, $opf) = explode(',', $News[1]);
$tagH1 = $opf.' '.$nm;

$cityFormAction = $pagNam;
$Crumbs[0][0] = 'firms';
$Crumbs[0][1] = 'Предприятия';
$Crumbs[1][0] = ($translit_company == '') ? 'company/'.$id_company : 'http://'.$translit_company.'.'.$url3Href;
$Crumbs[1][1] = 'Карточка компании';
$Crumbs[2][0] = ($translit_company == '') ? 'company/'.$id_company.'/news' : 'http://'.$translit_company.'.'.$url3Href.'/news';
$Crumbs[2][1] = 'Новости';
$Crumbs[3][0] = '';
$Crumbs[3][1] = (strlen($News[2])>50) ? substr($News[2], 0, 50).'...' : $News[2];
$crumbsQty = 4;

$keyws = $News[2].', '.$tagH1;
$descr = $News[2].' новость компании - '.$tagH1;
$title = $News[2].' - '.$tagH1;

$template = 'public/news_details.html';

}else{
#--------------------- компания имеет домен 3го уровня ---------------------#

// общие данные о компании
$Client = $Rubrics = array();
$name = $opf = $full_name = '';
$logo_width = $logo_height = $products_qty = 0;

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

if ((integer)(strlen($News[2])) > 65)
	$news_krsh = substr(getDataFromDB($News[2]), 0, 60).'...';
else
	$news_krsh = getDataFromDB($News[2]);

$action = 'news';
$title = getDataFromDB($News[2]).' - '.$name.' - '.$sity_name;
$descr = getDataFromDB($News[2]).' - Новости компании '.$name.' - '.$sity_name;
$keyws = '';

$template = 'default/news_details.html';

}

require_once '../../includs/company/'.$template;

?>