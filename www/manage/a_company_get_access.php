<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// загружаем 5 компаний подавшие заявление на получение доступа
	$Uncheck_get_access = array();
	$qRes = SqlQuery("
		Select
			RR.`id_client`, RR.`name`, RR.`phone`, RR.`fio`, RR.`email`,
			(Select `full_phone` From `CLIENT_PHONES` Where `id_client` = RR.`id_client` Order by `sort_order` Limit 1) as `phone`,
			(Select `rubric` From `CLIENT_ACTIVITIES` Where `client` = RR.`id_client` Order by `rubric` Limit 1) as `active`,
			(Select `about` From `CLIENTS_ABOUT` Where `id_client` = RR.`id_client`) as `about`,
			(Select `rubric` From `STR` Where `client` = RR.`id_client` Limit 1) as `product`,
			C.`positions`, C.`goods_qty`
		From `REGISTRATION_request` RR join `CLIENTS` C on RR.`id_client` = C.`id`
		Where `checked`='0' Limit 5;
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Uncheck_get_access[] = $Rows;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_company_get_access.html';

?>