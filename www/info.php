<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$isInfoPage = true;

$pagNam = 'info';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$addScripts = '<script type="text/javascript" src="js/curvycorners.js" ></script>'."\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = '�������';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '� �������';
$crumbsQty = 2;

$keyws = '���� ��������� �������� ���������� ������� ����������� �����';
$descr = '��������� �������: ������� �������, ��� � ��������, ������������������ �� ��������������, ����������, �������������, ����������� � ��������������';
$title = '��������� �������: ���� �����������, �������������, �����, ������� ���������������';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;

require_once '../includs/info.html';

?>