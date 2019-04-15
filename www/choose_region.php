<?php

require_once '../includs/head.php';

$Location = array();
$qRes = SqlQuery("
	Select R.`id`, R.`name`
	From `CLIENTS` C
		join `CLIENT_RUBRICS` CR on C.`id`=CR.`client`
		join `REGIONS` R on C.`region`=R.`id`
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
$title = 'Регистрация новой компании';

require_once '../includs/choose_region.html';

?>