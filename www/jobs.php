<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$isJobPage = false;

$pagNam = 'jobs';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Вакансии';
$crumbsQty = 2;
$keyws = 'вакансии работа Индустрия Бизнеса';
$descr = 'поиск работы вакансии Индустрия бизнеса';
$title = 'Открытые вакансии Индустрии бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);

require_once '../includs/jobs.html';

?>