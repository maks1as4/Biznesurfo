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
	$s = 'E.id='.$idExpo;
$qRes = SqlQuery('Select E.name, E.anonce, E.txt, E.date1, E.date2 From EXPO E Where '.$s.';');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	if ($Rows = mysql_fetch_row($qRes))
		$Lins = $Rows;
	@mysql_free_result($qRes);
} else
	require_once 'page404.php';

$pagNam = 'expo/'.$idExpo.'.html';
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Товары и услуги';
$Crumbs[1][0] = 'exhibitions';
$Crumbs[1][1] = 'Календарь выставок';
$Crumbs[2][0] = '';
$Crumbs[2][1] = $Lins[0];
$crumbsQty = 3;
// Обработка дат для метатега description
$RusMonths = array ('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
list ($y, $m1, $d1) = explode('-', $Lins[3]);
list ($y, $m2, $d2) = explode('-', $Lins[4]);
if ($m1==$m2)
	$m1 = '';
else
	$m1 = ' '.$RusMonths[$m1-1];
if ($m2==0)
	$m2 = 1; if ($m2>12)
	$m2 = 12;
$m2 = $RusMonths[$m2-1];
$expoName = str_replace('&laquo;', '', $Lins[0]);
$expoName = str_replace('&raquo;', '', $expoName);
$tagH1 = $expoName;
$keyws = $expoName.' разделы выставки, цели мероприятия, организаторы выставки, основные разделы, Индустрия Бизнеса,';
$descr = 'С '.$d1.$m1.' по '.$d2.' '.$m2.' '.$y.' года пройдет выставка '.$expoName.'. Справочник Индустрия бизнеса на выставке расширит сотрудничество с постоянными партнерами и достойно представит своих клиентов';
$title = $expoName.' - Индустрия Бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);

$isDirect = true;

require_once '../includs/expo.html';
?>