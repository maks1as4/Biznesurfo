<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$pagNam = 'locat';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$addScripts = '<script type="text/javascript" src="/js/curvycorners.js" ></script>'."\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = '√лавна€';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '–азмещение рекламы';
$crumbsQty = 2;

$keyws = 'размещение рекламы справочник скидки, модульна€ строчна€ реклама, расценки';
$descr = '÷ены на размещение прайс-листа и баннерной рекламы в справочнике »ндустри€ бизнеса';
$title = 'ѕрайс-лист на услуги и размещение рекламы в »ндустрии бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;

require_once '../includs/locat.html';

?>