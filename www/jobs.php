<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$isJobPage = false;

$pagNam = 'jobs';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = '�������';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '��������';
$crumbsQty = 2;
$keyws = '�������� ������ ��������� �������';
$descr = '����� ������ �������� ��������� �������';
$title = '�������� �������� ��������� �������';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);

require_once '../includs/jobs.html';

?>