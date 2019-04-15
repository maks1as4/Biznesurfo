<?php
@require_once "ssbegin.php"; 
$clId = 0;
if (count ($_GET) > 0) {
	if ( (isset ($_GET['id'])) && ($_GET['id'] != '') && (CheckIntegerUnsign ($_GET['id']) ) ) {
		$clId = $_GET['id'];
		$addParam = '&id='.$clId;
	}
}
@require_once "sscommon.php"; 
if ($clId == 0) {
	$Clients = array();
	$i = 0;
	$sql =	'Select distinct '.
			'K.name,'.
			'K.id '.
			'From COUNTERS_CLIENT C '.
			'join CLIENTS K on K.id = C.client '.
			'Where '.$range.' '.
			'union '.
			'Select distinct '.
			'K.name, '.
			'K.id '.
			'From COUNTERS C '.
			'join CLIENTS K on K.id = C.rubric '.
			'Where C.page = 4 '.
			'and '.$range.' '.
			'Order by 1 asc;';	
	$qRes = mysql_query($sql);
	if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			$Clients[$i][0] = $Rows[0];
			$Clients[$i][1] = $Rows[1];
			$i++;
		}
		@mysql_free_result ($qRes);
	}
	$numRows = count($Clients);
} else {
	/* Получим информацию о клиенте */
	/*$name = '';
	$site = '';
	$qRes = mysql_query ('Select C.name, C.www from CLIENTS C Where C.id='.$clId.';');
	if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes))) {
		$name = $Rows[0];
		if ($Rows[1]!='') {
			$wws = explode (',', $Rows[1]);
			$site = trim($wws[0]);
		}
	}
	@mysql_free_result ($qRes);*/
	/* Карточка  клиента */
	GetClientStat (0, $cart, $cartT);
	/* Прайс-лист  клиента */
	$price = 0;
	$priceT = 0;
	$qRes = mysql_query ('Select SUM(C.visits) From COUNTERS C Where C.page = 4 and C.rubric='.$clId.' and '.$range.
		' union Select SUM(C.visits) From COUNTERS C Where C.page = 4 and C.rubric>0 and '.$range);
	$first = true;
	if ((mysql_num_rows ($qRes) == 2) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($first) {
				$price = $Rows[0];
				$first = false;
			} else	$priceT = $Rows[0];
		}
	}
	@mysql_free_result ($qRes);
	/* Скачивание Прайс-листа  клиента */
	GetClientStat (2, $down, $downT);
	/* Печать карточки  клиента */
	GetClientStat (3, $prnt, $prntT);
	/* Кликов по ссылке на сайт клиента */
	GetClientStat (1, $link, $linkT);
	/* Баннеры клиента */
	/*$Bans = array();
	$i = 0;
	$qRes = mysql_query ('Select B.kind, B.name, B.id, B.rubric from HIST_BANNERS B Where B.client = '.$clId.' and B.adate between '.$Periods[$perSel]['range'].' Order by B.kind, B.name;');
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			switch ($Rows[0]) {
				case 0:	$Bans[$i][0]='Транспарант'; break;
				case 1:	$Bans[$i][0]='Внизу главной'; break;
				case 2: if ($Rows[3]==0)	$Bans[$i][0]='Справа на главной';
					else			$Bans[$i][0]='Справа в рубрике';
			}
			$Bans[$i][1] = $Rows[1];
			$Bans[$i][2] = $Rows[2];
			$i++;
		}
	}
	@mysql_free_result ($qRes);
	$banQty = count($Bans);*/
	$banQty = 0;
	/* Текстовые модули клиента */
	/*$Mods = array();
	$qRes = mysql_query ('Select CONCAT(R.name, " / ", B.head), B.id from HIST_TEXTMODULS B join RUBRICS R on R.id=B.rubric Where B.client = '.$clId.' and B.adate between '.$Periods[$perSel]['range'].' Order by R.name;');
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			$Mods[] = $Rows;
		}
	}
	@mysql_free_result ($qRes);
	$modQty = count($Mods);*/
	$modQty = 0;
}

function GetClientStat ($kind, &$var, &$varT) {
	global $clId, $range;
	$var = 0;
	$varT = 0;
	$qRes = mysql_query ('Select SUM(C.visits) From COUNTERS_CLIENT C Where C.kind = '.$kind.' and C.client='.$clId.' and '.$range.
		' union Select SUM(C.visits) From COUNTERS_CLIENT C Where C.kind = '.$kind.' and '.$range);
	$first = true;
	if ((mysql_num_rows ($qRes) == 2) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($first) {
				$var = $Rows[0];
				$first = false;
			} else	$varT = $Rows[0];
		}
	}
	@mysql_free_result ($qRes);
}

if ($clId == 0) 
	@require_once "htm/ssclients.html"; 
else	@require_once "htm/ssclient.html"; 

?>