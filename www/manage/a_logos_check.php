<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// загружаем непроверенные логотипы
	$Uncheck_logos = array();
	$qRes = SqlQuery("
		Select C.`id`, C.`name`, C.`logo`
		From `CLIENTS` C
		Where C.`status_logo`='1' and C.`logo`!='' and C.`address`!='';
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Uncheck_logos[] = $Rows;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_logos_check.html';

?>