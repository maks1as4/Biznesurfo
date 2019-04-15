<?php

require_once "../../includs/head.php";

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
	
	if (isset($_GET['region']) && !empty($_GET['region'])){
		
		$q = strtolower($_GET['q']);
		$q = urldecode($q);
		$q = mb_strtolower(iconv('UTF-8', 'windows-1251', $q), 'windows-1251');
		if (!$q) return;
		
		$id_region = $_GET['region'];
		if (!CheckIntegerUnsign($id_region)) return;
		
		$Cities = array();
		$qRes = mysql_query("Select `city`, `type`, `name` From `LOCATION` Where `region`='".mysql_real_escape_string($id_region)."' and `name` like '".mysql_real_escape_string($q)."%' and `lvl`='2' limit 10;");
		if (mysql_num_rows($qRes) > 0){
			while ($Rows = mysql_fetch_row($qRes))
				$Cities[] = $Rows;
			@mysql_free_result($qRes);
			foreach ($Cities as $city)
				echo $city[2].'|'.$city[1].'|'.$city[0]."\n";
		}else return;
		
	}else return;
	
}else return;

?>