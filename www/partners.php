<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$isMainPag = true;
$isPartnPage = true;

$pagNam = 'partners';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$Lins = array ();

$qRes = SqlQuery('Select P.name, P.site, COALESCE(P.email, ""), P.address, P.about, P.logo From PARTNERS P Order by P.sort_order;');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	while ($Rows = mysql_fetch_row($qRes))
		$Lins[] = $Rows;
	@mysql_free_result($qRes);
} else
	require_once 'page404.php';

$linQty = count($Lins);
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Партнеры';
$crumbsQty = 2;
$keyws = 'Партнеры';
$descr = 'Подробная информация о выставочных центрах и организациях, являющихся партнерами справочника Индустрия бизнеса';
$title = 'Партнеры, выставочные центры, организации - Индустрия Бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;

require_once '../includs/partners.html';

?>