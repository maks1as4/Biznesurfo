<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// загружаем клиентов без координат
	$Uncheck_maps = array();
	$qRes = SqlQuery("
		Select C.`id`, C.`name`, C.`address`, C.`coord`, C.`map_zoom`
		From `CLIENTS` C
		Where (C.`coord`='' or C.`map_zoom`='0') and C.`address`!='';
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Uncheck_maps[] = $Rows;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_maps_check.html';

?>