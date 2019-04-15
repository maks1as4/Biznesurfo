<?php
@require_once "ssbegin.php"; 
@require_once "sscommon.php"; 

$totals = 0;
$qRes = mysql_query ('Select COUNT(*) From HIST_BANNERS B Where B.adate between '.$Periods[$perSel]['range'].';');
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals = $Rows[0];
@mysql_free_result ($qRes);

$Bans = array();
$qRes = mysql_query ('Select B.kind, COALESCE(R.name, "-"), K.name, B.name, B.id, B.client, B.rubric from HIST_BANNERS B join CLIENTS K on K.id = B.client left join RUBRICS R on R.id = B.rubric Where B.adate between '.$Periods[$perSel]['range'].' Order by B.kind, B.rubric;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		$Bans[] = $Rows;
	}
	@mysql_free_result ($qRes);
}
$numRows = count ($Bans);
@require_once "htm/ssbanners.html"; 
?>