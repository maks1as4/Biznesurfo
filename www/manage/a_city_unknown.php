<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// загружаем неопределенные города
	$Unknown_city = array();
	$qRes = SqlQuery("Select `id`, `region`, `city`, `city_name`, `name` From `CLIENTS` Where `city_name`!='' Limit 30;");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Unknown_city[] = $Rows;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_city_unknown.html';

?>