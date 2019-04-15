<?php

$Location = array();
$qRes = SqlQuery("
	Select R.`id`, R.`name`
	From `CLIENTS` C
		join `CLIENT_RUBRICS` CR on C.`id`=CR.`client`
		join `REGIONS` R on C.`region`=R.`id`
	Where R.`id` not in (91,84)
	Group by R.`name`
	Order by R.`name`;
");
if (mysql_num_rows($qRes) > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Location[] = $Rows;
}
@mysql_free_result($qRes);

foreach ($Location as $key=>$loc){
	$qRes = SqlQuery("
		Select CI.`id`, CI.`name`
		From `CLIENTS` C
			join `CLIENT_RUBRICS` CR on C.`id`=CR.`client`
			left join `CITIES` CI on C.`city`=CI.`id`
		Where CI.`region`=".mysql_real_escape_string($loc[0])."
		Group by CI.`name`
		Order by CI.`is_capital` desc, CI.`name`;
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Location[$key][2][] = $Rows;
	}
	@mysql_free_result($qRes);
}

$url = 'http://'.$baseHref.'/';
$nameReg = 'Все города';

if (count($_GET) > 0){
	if ((isset($_GET['rcid'])) && ($_GET['rcid']!='')){
		$flCity = $_GET['rcid'];
		$s = $flCity[0];
		$idReg = substr($flCity, 1);
		$isReg = ($s=='r');
		if (!((($s=='r')||($s=='c'))&&(CheckIntegerZeroUnsign($idReg)))){
			$idReg = 0;
			$flCity = '';
		}else{
			$table = ($isReg) ? 'REGIONS' : 'CITIES';
			$qRes = SqlQuery("Select `name` From `".$table."` Where `id`=".mysql_real_escape_string($idReg).";");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$nameReg = $Row[0];
			}else{
				$idReg = 0;
				$flCity = '';
			}
			@mysql_free_result($qRes);
			unset($table);
		}
	}
	if (isset($_GET['fid'])){
		$forPred = true;
		$url .= 'firms';
	}
}

?>