<?php
@require_once "ssbegin.php"; 
@require_once "sscommon.php"; 

$totals = 0;
$qRes = mysql_query ('Select SUM(C.visits) From COUNTERS_CLIENT C Where C.kind = 0 and '.$range);
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals = $Rows[0];
@mysql_free_result ($qRes);

$Carts = array();
$i = 0;
$qRes = mysql_query ('Select K.name, K.id, SUM(C.visits) From COUNTERS_CLIENT C join CLIENTS K on K.id = C.client Where C.kind = 0 and '.
$range.' Group by K.name, K.id Order by 3 desc;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		$Carts[$i][0] = $Rows[0];
		$Carts[$i][1] = $Rows[1];
		$Carts[$i][2] = $Rows[2];
		$Carts[$i][3] = (round(100/$totals*$Carts[$i][2], 2)).'%';
		$i++;
	}
	@mysql_free_result ($qRes);
}
$numRows = count ($Carts);
@require_once "htm/sscarts.html"; 
?>