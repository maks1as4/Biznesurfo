<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// загружаем непроверенные данные компании
	$Uncheck_clients = array();
	$qRes = SqlQuery("
		Select C.`id`, C.`name`, C.`region`, C.`city`, C.`city_name`, C.`address`, CA.`about`, C.`translit` 
		From `CLIENTS` C
			left join `CLIENTS_ABOUT` CA on C.`id`=CA.`id_client`
		Where (C.`status`='1' or CA.`status`='1') and C.`address`!='' Order by C.`id` Limit 30;
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Uncheck_clients[] = $Rows;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_clients_check.html';

?>