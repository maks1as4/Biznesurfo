<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$isMainPag = true;

$pagNam = 'diploms';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$Lins = array ();

$qRes = SqlQuery('Select D.img_small, D.img, D.txt From DIPLOMS D Order by D.id DESC;');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	while ($Rows = mysql_fetch_row($qRes))
		$Lins[] = $Rows;
	@mysql_free_result($qRes);
} else
	require_once 'page404.php';

$linQty = count($Lins);
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$addScripts = '<script type="text/javascript" src="highslide/highslide-with-gallery.js"></script>'."\n".
	'<script type="text/javascript" src="js/highslide.js"></script>'."\n".
	'<link rel="stylesheet" type="text/css" href="highslide/highslide.css" />';
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Дипломы, сертификаты';
$crumbsQty = 2;
$keyws = 'Дипломы, сертификаты';
$descr = 'Дипломы, сертификаты и благодарственные письма Индустрии бизнеса за активную работу на рынке b2b';
$title = 'Дипломы, сертификаты - Индустрия Бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;

require_once '../includs/diploms.html';

?>