<?php
@require_once "ssbegin.php"; 
@require_once "sscommon.php"; 
$whr = ' Where C.page = 4 and C.rubric > 0 and '.$range;

$totals = 0;
$qRes = mysql_query ('Select SUM(C.visits) from COUNTERS C'.$whr.';');
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals = $Rows[0];
@mysql_free_result ($qRes);

$Prices = array();
$i = 0;
$qRes = mysql_query ('Select K.name, SUM(C.visits), K.id From COUNTERS C join CLIENTS K on K.id = C.rubric'.$whr.' Group by K.name, K.id Order by 2 desc;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		$Prices[$i][0] = $Rows[0];
		$Prices[$i][1] = $Rows[1];
		$Prices[$i][2] = $Rows[2];
		$Prices[$i][3] = 0;
		$qRes2 = mysql_query ('Select SUM(C.visits) from COUNTERS C Where C.page = 4 and C.rubric = '.$Rows[2].' and '.$range);
		if ((@mysql_num_rows ($qRes2) == 1) && ($qRes2) && ($Rows2 = mysql_fetch_row ($qRes2))) {
			$Prices[$i][3] = $Rows2[0];
			@mysql_free_result ($qRes2);
		}
		if ($Prices[$i][3]==0)	$Prices[$i][3] = '-';
		else			$Prices[$i][3] = $Prices[$i][3].' ('.(round(100/$Prices[$i][1]*$Prices[$i][3], 2)).'%)';
		$Prices[$i][4] = (round(100/$totals*$Prices[$i][1], 2)).'%';
		$i++;
	}
	@mysql_free_result ($qRes);
}
$numRows = count($Prices);
@require_once "htm/ssprices.html"; 

?>