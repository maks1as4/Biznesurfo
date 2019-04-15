<?php
@require_once "ssbegin.php"; 
$banId = 0;
if (count ($_GET) > 0) {
	if ( (isset ($_GET['bid'])) && ($_GET['bid'] != '') && (CheckIntegerUnsign ($_GET['bid']) ) ) {
		$banId = $_GET['bid'];
		$addParam = '&bid='.$banId;
	}
}


@require_once "sscommon.php"; 
if ($banId > 0) {
	$kind = '';
	$client = '';
	$name = '';
	$clId = '';
	$qRes = mysql_query ('Select B.kind, C.name, COALESCE(R.name, "-"), B.rubric, B.name, B.client from BANNERS B join CLIENTS C on C.id=B.client left join RUBRICS R on R.id=B.rubric Where B.id='.$banId.';');
	if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes))) {
		switch ($Rows[0]) {
			case 0:	$kind='Транспарант'; break;
			case 1:	$kind='Внизу главной'; break;
			case 2: { if ($Rows[3]==0)	$kind='Справа на главной';
				else			$kind='Справа в рубрике';
				break; }
			case 3: { if ($Rows[3]==0)	$kind='Наш справа во всех рубриках';
				else			$kind='Наш справа в рубрике';
				break; }
		}
		$client = $Rows[1];
		$rubric = $Rows[2];
		if ($Rows[3]>0)		$_SESSION ['ccrubr'] = $Rows[3];
		$name = $Rows[4];
		$clId = $Rows[5];
	}
	@mysql_free_result ($qRes);
	
	
	
	$showRubrT = 0;
	$showFindT = 0;
	$qRes = mysql_query ('Select 0, SUM(C.cnt) from COUNTERS_BT C Where C.is_banner=1 and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$showRubrT = $Rows[1];
			else			$showFindT = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
	$showRubr = 0;
	$showFind = 0;
	$qRes = mysql_query ('Select 0, SUM(C.cnt) from COUNTERS_BT C Where C.is_banner=1 and C.module='.$banId.' and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$showRubr = $Rows[1];
			else			$showFind = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
	$clikRubrT = 0;
	$clikFindT = 0;
	$qRes = mysql_query ('Select 0, SUM(C.cnt) from CLICK_BT C Where C.is_banner=1 and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$clikRubrT = $Rows[1];
			else			$clikFindT = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
	$clikRubr = 0;
	$clikFind = 0;	
	$qRes = mysql_query ('Select 0, SUM(C.cnt) from CLICK_BT C Where C.is_banner=1 and C.module='.$banId.' and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$clikRubr = $Rows[1];
			else			$clikFind = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
@require_once "htm/ssbanner.html"; 
}
?>