<?php
@require_once "ssbegin.php"; 
@require_once "sscommon.php"; 

$whr = ' Where C.page IN(2, 4, 5) and C.rubric > 0 and '.$range;
$totals = 0;
$qRes = mysql_query ('Select SUM(C.visits) From COUNTERS_CLIENT C Where '.$range);
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals = $Rows[0];
@mysql_free_result ($qRes);
$qRes = mysql_query ('Select SUM(C.visits) from COUNTERS C'.$whr.';');
if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)))
	$totals += $Rows[0];
@mysql_free_result ($qRes);

$Lines = array();
$i = 0;
$qRes = mysql_query ('Select C.page, SUM(C.visits) From COUNTERS C'.$whr.' Group by C.page Order by C.page;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		switch ($Rows[0]) {
			case 2 :{	$Lines[$i][1] = 'Статистика посещений рубрик справочника';
					$Lines[$i][0] = 'ssrubs.php'; break;}
			case 4 :{	$Lines[$i][1] = 'Статистика посещений прайс-листов компаний';
					$Lines[$i][0] = 'ssprices.php'; break;}
			case 5 :{	$Lines[$i][1] = 'Статистика посещений рубрик справочника в режиме "Предприятия"';
					$Lines[$i][0] = 'sspredrubs.php'; break;}
			default:{	$Lines[$i][1] = 'Неизвестная статистика';
					$Lines[$i][0] = 'menu.php'; break;}
		}
		$Lines[$i][2] = $Rows[1];		
		$Lines[$i][3] = (round(100/$totals*$Lines[$i][2], 2)).'%';
		$i++;
	}
	@mysql_free_result ($qRes);
}
$qRes = mysql_query ('Select C.kind, SUM(C.visits) From COUNTERS_CLIENT C Where '.$range.' Group by C.kind Order by C.kind;');
if ((mysql_num_rows ($qRes) > 0) && ($qRes)) {
	while ($Rows = mysql_fetch_row ($qRes)) {
		switch ($Rows[0]) {
			case 0 :{	$Lines[$i][1] = 'Статистика посещений карточек предприятий';
					$Lines[$i][0] = 'sscarts.php'; break;}
			case 1 :{	$Lines[$i][1] = 'Статистика переходов на сайты компаний';
					$Lines[$i][0] = 'sslink.php'; break;}
			case 2 :{	$Lines[$i][1] = 'Статистика скачивания прайс-листов компаний';
					$Lines[$i][0] = 'ssdowns.php'; break;}
			case 3 :{	$Lines[$i][1] = 'Статистика печати карточек компаний';
					$Lines[$i][0] = 'ssprintc.php'; break;}
			default:{	$Lines[$i][1] = 'Неизвестная статистика - часть вторая';
					$Lines[$i][0] = 'menu.php'; break;}
		}
		$Lines[$i][2] = $Rows[1];		
		$Lines[$i][3] = (round(100/$totals*$Lines[$i][2], 2)).'%';
		$i++;
	}
	@mysql_free_result ($qRes);
}
$numRows = count ($Lines);
@require_once "htm/ssgeneral.html"; 
?>