<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// загружаем непроверенные новости
	$Uncheck_news = array();
	$qRes = SqlQuery("
		Select CN.`id`, CN.`title`, CN.`text`, CN.`img`, CN.`ext`, C.`name`
		From `CLIENT_NEWS` CN
			join `CLIENTS` C on CN.`id_client`=C.`id`
		Where CN.`status`!='0' Order by CN.`udate` Limit 10;
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Uncheck_news[] = $Rows;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_news_check.html';

?>