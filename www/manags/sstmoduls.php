<?php
@require_once "ssbegin.php"; 
@require_once "sscommon.php"; 

die('В разработке');

$totals = 0;
$qRes = mysql_query ('Select COUNT(*) From HIST_TEXTMODULS B Where B.adate between '.$Periods[$perSel]['range'].';');
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals = $Rows[0];
@mysql_free_result ($qRes);

$Mods = array();
$qRes = mysql_query ('Select R.name, K.name, B.head, B.id, B.client, B.rubric from HIST_TEXTMODULS B join CLIENTS K on K.id = B.client join RUBRICS R on R.id = B.rubric Where B.adate between '.$Periods[$perSel]['range'].' Order by K.name;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		$Mods[] = $Rows;
	}
	@mysql_free_result ($qRes);
}
$numRows = count ($Mods);
@require_once "htm/sstmoduls.html"; 
?>