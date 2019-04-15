<?php
@require_once "ssbegin.php"; 
$ModId = 0;
if (count ($_GET) > 0) {
	if ( (isset ($_GET['tid'])) && ($_GET['tid'] != '') && (CheckIntegerUnsign ($_GET['tid']) ) ) {
		$ModId = $_GET['tid'];
		$addParam = '&tid='.$ModId;
	}
}
@require_once "sscommon.php"; 
if ($ModId > 0) {
	$client = '';
	$clId = '';
	$head = '';
	$body = '';
	$link = '';
	$phon = '';
	$qRes = mysql_query ('Select B.head, C.name, R.name, B.rubric, B.txt, B.client, B.link, B.phone, COALESCE(T.id, 0) from HIST_TEXTMODULS B join CLIENTS C on C.id=B.client join RUBRICS R on R.id=B.rubric left join TEXTMODULS T on T.id_module = B.id Where B.id='.$ModId.';');
	if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes))) {
		$head = $Rows[0];
		$client = $Rows[1];
		$rubric = $Rows[2];
		$body = $Rows[4];
		$clId = $Rows[5];
		$link = $Rows[6];
		$phon = $Rows[7];
		$tmId = $Rows[8];
	}
	@mysql_free_result ($qRes);
	$showRubrT = 0;
	$showFindT = 0;
	$qRes = mysql_query ('Select C.is_find, SUM(C.cnt) from COUNTERS_BT C Where C.is_banner=0 and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$showRubrT = $Rows[1];
			else			$showFindT = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
	$showRubr = 0;
	$showFind = 0;
	$qRes = mysql_query ('Select 0, SUM(C.cnt) from COUNTERS_BT C Where C.is_banner=0 and C.module='.$ModId.' and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$showRubr = $Rows[1];
			else			$showFind = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
	$clikRubrT = 0;
	$clikFindT = 0;
	$qRes = mysql_query ('Select 0, SUM(C.cnt) from CLICK_BT C Where C.is_banner=0 and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$clikRubrT = $Rows[1];
			else			$clikFindT = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
	$clikRubr = 0;
	$clikFind = 0;	
	$qRes = mysql_query ('Select 0, SUM(C.cnt) from CLICK_BT C Where C.is_banner=0 and C.module='.$ModId.' and '.$range);
	if ((mysql_num_rows ($qRes) >0) && ($qRes)) {
		while ($Rows = mysql_fetch_row ($qRes)) {
			if ($Rows[0]==0)	$clikRubr = $Rows[1];
			else			$clikFind = $Rows[1];
		}
	}
	@mysql_free_result ($qRes);
@require_once "htm/sstmodule.html"; 
}
?>