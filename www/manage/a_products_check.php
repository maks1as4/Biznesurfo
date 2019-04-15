<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// загружаем непроверенные товары
	$Uncheck_products = array();
	$qRes = SqlQuery("
		Select S.`id`, S.`name`, S.`dop`, S.`price`, S.`text`, C.`name`, SI.`name`, SI.`ext`, C.`translit`, S.`translit` 
		From `STR` S
			join `CLIENTS` C on S.`client`=C.`id`
			left join `STR_IMG` SI on S.`id`=SI.`id_product`
		Where S.`status`!='0' Order by S.`udate` Limit 10;
	");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Uncheck_products[] = $Rows;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_products_check.html';

?>