<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$idExpo = 0;
$urlExpo = '';

if (count($_GET)>0) {
	if ((isset($_GET['eid']))&&($_GET['eid']!='')&&(CheckIntegerUnsign($_GET['eid']) ))
		$idExpo = $_GET['eid'];
	if ((isset($_GET['eurl']))&&($_GET['eurl']!='')&&(CheckStr($_GET['eurl'], 6, 0, false) )) {
		$urlExpo = $_GET['eurl'];
		$idExpo = -1;
	}
}
if ($idExpo==0)
	require_once 'page404.php';

$isMainPag = true;
$isExpoPage = true;

$Lins = array ();
if ($idExpo==-1) {
	$s = 'UPPER(E.url)=UPPER('."'$urlExpo')";
	$idExpo = $urlExpo;
} else
	$s = 'E.expo='.$idExpo;
$qRes = SqlQuery('Select E.id, E.short_head, E.txt, E.fullhead, E.anonce, COALESCE(E.descr, "") From EXPO_REPORT E Where '.$s.';');
$numRows = mysql_num_rows($qRes);
if (($numRows==1)&&($qRes)) {
	if ($Rows = mysql_fetch_row($qRes))
		$Lins = $Rows;
	@mysql_free_result($qRes);
} else
	require_once 'page404.php';

$Pics = array ();
$qRes = SqlQuery('Select G.small, G.big, G.alt From EXPO_FOTO G Where G.report='.$Lins[0].' Order by G.sort_order;');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	$i = 0;
	while ($Rows = mysql_fetch_row($qRes)) {
		$Pics[$i][0] = $Rows;
		$imgsize = getimagesize('i/expo/reports/'.$Rows[0]);
		if (count($imgsize)>1)
			$Pics[$i][1] = $imgsize[0];
		else
			$Pics[$i][1] = 200;
		$i++;
	}
	@mysql_free_result($qRes);
}
$picsQty = count($Pics);

$pagNam = 'expo/reports/'.$idExpo.'.html';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$addScripts = '<script type="text/javascript" src="/js/carousel/jquery.jcarousel.min.js"></script>'."\n".
	'<link rel="stylesheet" type="text/css" href="/js/carousel/skin2.css" />'."\n".
	'<script type="text/javascript" src="highslide/highslide-with-gallery.js"></script>'."\n".
	'<script type="text/javascript" src="js/highslide.js"></script>'."\n".
	'<link rel="stylesheet" type="text/css" href="highslide/highslide.css" />';

$jqAdd = "$('#mycarousel').jcarousel();";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = 'expo_reports';
$Crumbs[1][1] = 'Отчеты о выставках';
$Crumbs[2][0] = '';
$Crumbs[2][1] = $Lins[1];
$crumbsQty = 3;
$tagH1 = $Lins[3];
$keyws = '';
$descr = $Lins[5];
$title = $Lins[1].'. Отчет с выставки - Индустрия Бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;

require_once '../includs/exporeport.html';

?>