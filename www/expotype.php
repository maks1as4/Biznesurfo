<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$calMonths = array (
	1=>array ('Январь', '01'),
	2=>array ('Февраль', '02'),
	3=>array ('Март', '03'),
	4=>array ('Апрель', '04'),
	5=>array ('Май', '05'),
	6=>array ('Июнь', '06'),
	7=>array ('Июль', '07'),
	8=>array ('Август', '08'),
	9=>array ('Сентябрь', '09'),
	10=>array ('Октябрь', '10'),
	11=>array ('Ноябрь', '11'),
	12=>array ('Декабрь', '12')
);

$year = $curYear = date('Y');
$month = $curMon = date('n');
// Поскольку в Январе выставок нет, сделаем перенаправление на Февраль
if ($curMon==1) {
	$month = $curMon = 2;
}

if (count($_GET)>0) {
	if ((isset($_GET['hsq']))&&($_GET['hsq']!='')&&(CheckIntegerUnsign($_GET['hsq']) ))
		$curPageNo = $_GET['hsq'];
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckIntegerUnsign($_GET['pno']) ))
		$curPageNo = $_GET['pno'];
	if ((isset($_GET['mon']))&&($_GET['mon']!='')&&(CheckInteger($_GET['mon'], 1, 12) ))
		$month = intval($_GET['mon']);
	if ((isset($_GET['year']))&&($_GET['year']!='')&&(CheckInteger($_GET['year'], 2012, 2014) ))
		$year = intval($_GET['year']);
	if ((isset($_GET['col']))&&($_GET['col']!='')&&(CheckInteger($_GET['col'], 1, 4) ))
		$sortCol = $_GET['col'];
	$sortDesc = isset($_GET['desc']);
}
$lastPeriod = (($year<$curYear)||(($year==$curYear)&&($month<$curMon)));
$fstMonth = 1; // здесь 1 - порядковый календарный номер первого месяца
if ($month<$fstMonth)
	$actMonth = $month+12-$fstMonth+1;
else
	$actMonth = $month-$fstMonth+1;
//if ($month<10)	$month='0'.$month;

$isMainPag = true;
$isExpoPage = true;

$pagNam = 'exhibitions';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$Lins = array ();
$Spaces = array ();

if ($sortDesc) {
	if ($sortCol==2)
		$qTxt = 'E.city DESC, E.date1, E.date2, E.id';
	else
		$qTxt = 'E.date1 DESC, E.date2, E.id DESC';
} else {
	if ($sortCol==2)
		$qTxt = 'E.city, E.date1, E.date2, E.id';
	else
		$qTxt = 'E.date1, E.date2, E.id';
}
$ml = ($month<10)?'0'.$month:$month;
if ($ml==12)
	$mr = 1;
else
	$mr = $month+1;
$yearr = $year;
if ($mr<10) {
	if ($mr==1)
		$yearr = $year+1;
	$mr = '0'.$mr;
}
$qRes = SqlQuery("Select COALESCE(E.url,E.id), E.name, E.date1, E.date2, E.city, E.space From EXPO E Where year(E.date1)='".$year."' and month(E.date1)='".$ml."' Order by E.date1, E.date2, E.id;");
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	while ($Rows = mysql_fetch_row($qRes)) {
		$Lins[] = $Rows;
		if (isset($Rows[5]))
			$Spaces[] = $Rows[5];
	}
	@mysql_free_result($qRes);
}
$spQty = count($Spaces);
$Pics = array ();
$qRes = SqlQuery('Select G.filename, G.alt, G.url From EXPO_GALL G Order by G.sort_order;');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	$i = 0;
	while ($Rows = mysql_fetch_row($qRes)) {
		$Pics[$i][0] = $Rows;
		$imgsize = getimagesize('i/expo/common/'.$Rows[0]);
		if (count($imgsize)>1)
			$Pics[$i][1] = $imgsize[0];
		else
			$Pics[$i][1] = 200;
		$i++;
	}
	@mysql_free_result($qRes);
}

$linsQty = count($Lins);
$picsQty = count($Pics);
$parStr = AddParStr($parStr, 'year='.$year);
$parStr = AddParStr($parStr, 'mon='.$ml);
$tabHeaders = array ('Дата', 'Город');
$Headers = array ();
BuildHeaders($pagNam.AddParStr($parStr, 'col='));
$Headers[] = '<td>Название выставки</td>';
$HeadersQty = 3;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$addScripts = '<script type="text/javascript" src="/js/carousel/jquery.jcarousel.min.js"></script>'."\n".
	'<link rel="stylesheet" type="text/css" href="/js/carousel/skin.css" />'."\n";

// $jsFuncs = "jQuery(document).ready(function() { jQuery('#mycarousel').jcarousel(); });";
$jqAdd = "$('#mycarousel').jcarousel();";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Календарь выставок';
$crumbsQty = 2;
$keyws = 'Выставки. Календарь выставок. Индустрия Бизнеса. Новости. Размещение рекламы';
$descr = 'Вся информация по выставочным мероприятиям: дата и место проведения, список участников, организаторов, тематические разделы';
$title = 'Календарь выставок и форумов на '.$calMonths[$actMonth][0].' '.$year.' год. Крупные отраслевые выставки';
$tagH1 = 'Календарь выставок на '.$calMonths[$actMonth][0];
$News = GetNewsBlock();
$newsQty = count($News);
$ExpoReps = GetExpoReportsBlock();
$expoReportQty = count($ExpoReps);

$isDirect = true;


$RusMonths = array ('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

function PrintRusDate($dt) {
	global $RusMonths;
	list ($y, $m, $d) = explode('-', $dt);
	return $d.'&nbsp;'.$RusMonths[$m-1];
}

require_once '../includs/expotype.html';

?>