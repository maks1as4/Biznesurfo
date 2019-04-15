<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

if (count($_GET)>0) {
	if ((isset($_GET['hsq']))&&($_GET['hsq']!='')&&(CheckIntegerUnsign($_GET['hsq']) ))
		$curPageNo = $_GET['hsq'];
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckIntegerUnsign($_GET['pno']) ))
		$curPageNo = $_GET['pno'];
}

$pagNam = 'expo_reports';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$Lins = array ();

$qRes = SqlQuery('Select Count(*) from EXPO_REPORT;');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)&&($Rows = mysql_fetch_row($qRes))) {
	$RowsQty = $Rows[0];
	@mysql_free_result($qRes);
} else
	$RowsQty = 0;
if ($RowsQty==0)
	require_once 'page404.php';
else {
	$qtyPerPage = $itemLimits['expo_reports'];
	$qRes = SqlQuery('Select COALESCE(E.url, E.expo), X.name, X.space, X.city, X.date1, X.date2, COALESCE(E.img, ""), E.short_head, E.middle_head From EXPO_REPORT E join EXPO X on X.id=E.expo Order by E.id DESC Limit '.(($curPageNo-1)*$qtyPerPage).','.$qtyPerPage.';');
	$numRows = mysql_num_rows($qRes);
	if (($numRows!=0)&&($qRes)) {
		while ($Rows = mysql_fetch_row($qRes))
			$Lins[] = $Rows;
		@mysql_free_result($qRes);
	} else
		require_once 'page404.php';
	$PageNav = CreatePageNav($RowsQty, 10, $pagNam, $parStr);
	$pageNavQty = count($PageNav);
} // if $RowsQty >0

$linsQty = count($Lins);
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = '√лавна€';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'ќтчеты с выставок';
$crumbsQty = 2;
$keyws = 'выставки, отчеты, прошедша€ выставка';
$descr = '»тоги международных и региональных тематических выставок, активным участником которых стал справочник »ндустри€ бизнеса';
$title = 'ќтчеты о выставках. Ёффективность участи€ справочника »ндустрии бизнеса в выставках';

$News = GetNewsBlock();
$newsQty = count($News);
$Expos = GetExposBlock();
$expoQty = count($Expos);

$isDirect = true;
$RusMonths = array ('€нвар€', 'феврал€', 'марта', 'апрел€', 'ма€', 'июн€', 'июл€', 'августа', 'сент€бр€', 'окт€бр€', 'но€бр€', 'декабр€');

function PrintRusDate($dt) {
	global $RusMonths;
	list ($y, $m, $d) = explode('-', $dt);
	return $d.'&nbsp;'.$RusMonths[$m-1].'&nbsp;'.$y;
}

require_once '../includs/expo_reports_type.html';

?>