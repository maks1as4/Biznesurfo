<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$idNew = 0;

if (count($_GET)>0) {
	if ((isset($_GET['nid']))&&($_GET['nid']!='')&&(CheckIntegerUnsign($_GET['nid']) ))
		$idNew = $_GET['nid'];
}

if ($idNew==0)
	require_once 'page404.php';

$isMainPag = true;
$isNewsPage = true;

$Lins = array ();
$qRes = SqlQuery('Select N.head, N.full_head, DATE(N.ns_date), N.body, N.kind, N.anonce, N.visits From NEWS N Where N.id='.$idNew.';');
if (($qRes)&&($Rows = mysql_fetch_row($qRes))) {
	$Lins = $Rows;
	if ($isLogStat)
		SqlQuery('update NEWS set visits=visits+1 where id='.$idNew);	// Роботов тоже считаем ;)
} else
	require_once 'page404.php';
@mysql_free_result($qRes);

$pagNam = 'news/'.$idNew.'.html';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Товары и услуги';
$Crumbs[1][0] = 'newstype';
$Crumbs[1][1] = 'Новости';
$Crumbs[2][0] = '';
$Crumbs[2][1] = $Lins[0];
$crumbsQty = 3;
$tagH1 = $Lins[1];
$keyws = 'Новости компаний регионов уральского товары услуги предприятия. Индустрия Бизнеса. Выставки. Размещение рекламы';
$descr = $Lins[1].' в Справочнике Индустрия Бизнеса';
$title = 'Новости: '.$Lins[1].' - Индустрия Бизнеса';
$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
$isDirect = true;

require_once '../includs/news.html';

?>