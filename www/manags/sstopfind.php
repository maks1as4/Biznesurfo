<?php
@require_once "ssbegin.php"; 
$inPred = 0;
if (count ($_GET) > 0) {
	if ( (isset ($_GET['prd'])) && ($_GET['prd'] == 1)) {
		$inPred = 1;
		$addParam = '&prd=1';
	}
}
@require_once "sscommon.php"; 


$totals = 0;
$qRes = mysql_query ('Select COUNT(*) From FINDS F where F.ftime between '.$Periods[$perSel]['range'].' and F.what='.$inPred.';');
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals = $Rows[0];
@mysql_free_result ($qRes);

$Req = array();
$i = 0;
$qRes = mysql_query ('Select F.val, COUNT(*) From FINDS F where F.ftime between '.$Periods[$perSel]['range'].' and F.what='.$inPred.' Group by F.val Order by 2 desc LIMIT 0, 100;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		$Req[$i][0] = htmlspecialchars($Rows[0]);
		$Req[$i][1] = $Rows[1];
		$Req[$i][2] = (round(100/$totals*$Req[$i][1], 2)).'%';
		$i++;
	}
	@mysql_free_result ($qRes);
}
$numRows = count ($Req);
@require_once "htm/sstopfind.html"; 
?>