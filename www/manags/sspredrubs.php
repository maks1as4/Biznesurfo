<?php
@require_once "ssbegin.php"; 
@require_once "sscommon.php"; 
$whr = ' Where C.page = 5 and C.rubric > 0 and '.$range;
	
$totals = 0;
$qRes = mysql_query ('Select SUM(C.visits) from COUNTERS C'.$whr.';');
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals = $Rows[0];
@mysql_free_result ($qRes);

$Rubs = array();
$i = 0;
$qRes = mysql_query ('Select R.name, SUM(C.visits), R.id From COUNTERS C join RUBRICS R on R.id = C.rubric'.$whr.' Group by R.name, R.id Order by 2 desc;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		$Rubs[$i][0] = $Rows[0];
		$Rubs[$i][1] = $Rows[1];
		$Rubs[$i][2] = $Rows[2];
		$Rubs[$i][3] = 0;
		$qRes2 = mysql_query ('Select SUM(C.visits) from COUNTERS C Where C.page = 5 and C.rubric = '.$Rows[2].' and '.$range);
		if ((@mysql_num_rows ($qRes2) == 1) && ($qRes2) && ($Rows2 = mysql_fetch_row ($qRes2))) {
			$Rubs[$i][3] = $Rows2[0];
			@mysql_free_result ($qRes2);
		}
		if ($Rubs[$i][3]==0)	$Rubs[$i][3] = '-';
		else			$Rubs[$i][3] = $Rubs[$i][3].' ('.(round(100/$Rubs[$i][1]*$Rubs[$i][3], 2)).'%)';
		$Rubs[$i][4] = (round(100/$totals*$Rubs[$i][1], 2)).'%';
		$i++;
	}
	@mysql_free_result ($qRes);
}
$qRes = mysql_query ('Select R.name, R.id From RUBRICS R Where exists (Select * From STR S where S.rubric=R.id) and not exists (Select * From COUNTERS C where C.page = 5 and C.rubric = R.id and '.$range.') Order by R.name;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		$Rubs[$i][0] = $Rows[0];
		$Rubs[$i][1] = 0;
		$Rubs[$i][2] = $Rows[1];
		$Rubs[$i][3] = '-';
		$Rubs[$i][4] = '-';
		$i++;
	}
	@mysql_free_result ($qRes);
}
$numRows = count($Rubs);

@require_once "htm/sspredrubs.html"; 
?>