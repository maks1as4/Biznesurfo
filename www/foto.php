<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$predGroup = ''; // ��� ���������� ������

$isMainPag = true;
$isPartnPage = true;

$pagNam = 'foto';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$Lins = array ();

$qRes = SqlQuery('Select F.img, F.title, F.annot From FOTO F Order by F.sort_order;');
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
$Crumbs[0][1] = '�������';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '����������';
$crumbsQty = 2;
$keyws = '����������';
$descr = '����������';
$title = '���������� - ��������� �������';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;

require_once '../includs/foto.html';

?>