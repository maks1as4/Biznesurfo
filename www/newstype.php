<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

if (count($_GET)>0) {
	if ((isset($_GET['hsq']))&&($_GET['hsq']!='')&&(CheckIntegerUnsign($_GET['hsq']) ))
		$curPageNo = $_GET['hsq'];
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckIntegerUnsign($_GET['pno']) ))
		$curPageNo = $_GET['pno'];
}

$isMainPag = true;
$isNewsPage = true;

$pagNam = 'newstype';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$Lins = array ();

$qRes = SqlQuery('select count(*) from NEWS;');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)&&($Rows = mysql_fetch_row($qRes))) {
	$RowsQty = $Rows[0];
	@mysql_free_result($qRes);
} else
	$RowsQty = 0;
if ($RowsQty==0)
	require_once 'page404.php';
else {
	$qtyPerPage = $itemLimits['news'];
	$qRes = SqlQuery('Select N.id, N.kind, N.head, DATE(N.ns_date), N.anonce, COALESCE(N.imgs, ""), N.visits From NEWS N Order by N.ns_date DESC, N.id DESC Limit '.(($curPageNo-1)*$qtyPerPage).','.$qtyPerPage.';');
	$numRows = mysql_num_rows($qRes);
	if (($numRows!=0)&&($qRes)) {
		while ($Rows = mysql_fetch_row($qRes))
			$Lins[] = $Rows;
		@mysql_free_result($qRes);
	} else
		require_once 'page404.php';
	$PageNav = CreatePageNav($RowsQty, 10, $pagNam, $parStr);
	$pageNavQty = count($PageNav);
} // if $RowsQty >0

$linsQty = count($Lins);
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Новости';
$crumbsQty = 2;
$keyws = 'Новости компаний регионов уральского товары услуги предприятия. Индустрия Бизнеса. Выставки. Размещение рекламы';
$descr = 'Анонсы мероприятий, проводимых Губернатором Свердловской области, руководителями регионов федерального округа и Министерством промышленности и науки области';
$title = 'Новости Уральского региона - Индустрия Бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;

require_once '../includs/newstype.html';

?>