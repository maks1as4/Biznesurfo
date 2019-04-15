<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$st = gettime();
$fText = '';
$forMeta = '';
$tagH1 = '';
$forPred = true;
$cnt_wrds = 0;
$Lins = array ();
$linQty = 0;
$wrds = array ();
$pagNam = 'find_firms';

if (count($_GET)>0) {
	if (isset($_GET['fnd'])) {
		$fText = $_GET['fnd'];
		$fText = urldecode($fText);
		// Если передан UTF8, переводим его в cp1251
		if (preg_match('//u', $fText))
			$fText = ICONV('UTF-8','Windows-1251',$fText);
		if (get_magic_quotes_gpc())
			$fText = stripslashes($fText);
		$fText = preg_replace('/\s+/',' ',$fText);
		$fText = trim($fText);
		if (strlen($fText)>127)
			$fText = substr($fText, 0, 127);
		$forMeta = htmlspecialchars($fText);
		$findText = $fText;		
		$parStr = AddParStr($parStr,'fnd='.urlencode($fText));
	}
	if ($flCity!='')
		$parStr = AddParStr($parStr, 'rcid='.$flCity);
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckInteger($_GET['pno'],1,10000) ))
		$curPageNo = $_GET['pno'];
	if ((isset($_GET['col']))&&($_GET['col']!='')&&(CheckInteger($_GET['col'],1,2) ))
		$sortCol = $_GET['col'];
}

$wrds = GetWords($fText);
$cnt_wrds = count($wrds);
if ($cnt_wrds>0) {
	$sql =	'Select sql_calc_found_rows distinct '.
			'C.`id`,'.
			'C.`name`,'.
			'C.`address`,'.
			'('.
			'	Select `full_phone` '.
			'	From `CLIENT_PHONES` '.
			'	Where `id_client` = C.`id` '.
			'	Order by `sort_order` '.
			'	Limit 1'.
			'),'.
			'C.`goods_qty`,'.
			'(Select `site` From `CLIENT_SITES` Where `id_client` = C.`id` Order by `sort_order` Limit 1),'.
			'(Select `email` From `CLIENT_EMAILS` Where `id_client` = C.`id` Order by `sort_order` Limit 1),'.
			'C.`translit` '.
			'From `CLIENTS` C '.
			'Where ';
			for ($i = 0; $i<$cnt_wrds; $i++) {
				$sql .= 'C.`name` LIKE "%'.$wrds[$i].'%" ';
				if ($i<$cnt_wrds-1)
					$sql .= ' and ';
			}
			$sql .= 'and C.`address`!=""';
			if ($idReg>0) {
				if ($isReg)
					$sql .= ' and C.`region`='.$idReg;
				else
					$sql .= ' and C.`city`='.$idReg;
			}
			$sql .= ' Order by ';
			if ($sortCol==1)
				$sql .= 'C.`name`';
			else
				$sql .= 'C.`goods_qty` desc, C.`name`';
			$sql .= ' Limit '.(($curPageNo-1)*30).',30;';

	$st_sql = gettime();
	$qRes = SqlQuery($sql);
	$qRes2 = SqlQuery('select found_rows();');
	$RowsQty = mysql_result($qRes2, 0);
	$dt_sql = bcsub(gettime(), $st_sql, 6);

	if ($qRes) {
		while ($Rows = mysql_fetch_row($qRes))
			$Lins[] = $Rows;
	}
	$linQty = count($Lins);

	@mysql_free_result($qRes);
	@mysql_free_result($qRes2);

	if ($sortCol==1) {
		$sortByName = true;
		$sortLink = $pagNam.AddParStr($parStr, 'col=2');
	} else {
		$sortByName = false;
		$sortLink = $pagNam.$parStr;
		$parStr = AddParStr($parStr, 'col=2');
	}
	$PageNav = CreatePageNavFind($RowsQty, 10, $pagNam, $parStr);
	$pageNavQty = count($PageNav);
	$qtyRow = $RowsQty.' предприяти'.PrintFinLetter($RowsQty, false);
}

$cityFormAction = $pagNam.$parStr;
$toFirmSwitch = $pagNam.$parStr;
$toMatSwitch = 'find'.$parStr;

$Crumbs[0][0] = 'firms';
$Crumbs[0][1] = 'Предприятия';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Результаты поиска по запросу &laquo;'.$forMeta.'&raquo;';
$crumbsQty = 2;
$tagH1 = 'Результаты поиска по запросу &laquo;'.$forMeta.'&raquo;';

if ($forMeta=='')
	$title = 'Поиск предприятий - Индустрия Бизнеса';
else {
	$title = $forMeta.' - Индустрия Бизнеса';
	$keyws = $forMeta;
	$descr = $forMeta;
}

$ft = gettime();
$dt = bcsub((bcsub($ft, $st, 6)), $dt_sql, 6);
$execTime = "время выполнения PHP: $dt; SQL: $dt_sql";
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";

// Запишем в базу статистику по запросу
if ($cnt_wrds>0) {
	$s1 = addslashes($fText);
	$s2 = addslashes($_SERVER['REMOTE_ADDR']);
	$s3 = substr(addslashes($_SERVER['HTTP_USER_AGENT']),0,150);
	if ($isLogStat)
		SqlQuery('insert into FINDS (atime, val, cnt, userIP, userAgent, what) values '."('$dt_sql','$s1','$RowsQty','$s2','$s3','1');");
}

$RightTextMods = array ();
$RightBanners = array ();
GetRightBans();
$rbansQty = count($RightBanners);
$rTblocksQty = count($RightTextMods);
$i = $linQty-$rTblocksQty-$rbansQty*2;
$vertDirectQty = ($i<8)?$i:7;
if ($vertDirectQty<0)
	$vertDirectQty = 0;
$isDirect = $vertDirectQty>0;
if ($i-$vertDirectQty>4) {
	$Expos = GetExposBlock();
	$expoQty = count($Expos);
}

require_once '../includs/find_firms.html';

?>