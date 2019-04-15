<?php

require_once "../../includs/head.php";

$error = false;

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){

	// наличие логотипа компании
	$qRes = SqlQuery("Select `logo` From `CLIENTS` Where `id`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
	}else
		$error = true;
	@mysql_free_result($qRes);
	// ошибок нет, удаляем картинку
	if (!$error){
		// удаляем логотип
		$Finfo = pathinfo('../logo/'.$Row[0]);
		chmod('../logo', 0777);
		deleteImages('../logo/', $Finfo['filename'], $Finfo['extension']);
		chmod('../logo', 0555);
		unset($Finfo);
		// удаляем данные из БД
		SqlQuery("Update `CLIENTS` Set `logo`='', `status_logo`='0', `udate`='".date('Y-m-d H:i:s')."' Where `id`='".mysql_real_escape_string($user_client_id)."';");
	}
	
}else
	$error = true;

$json_array = array('error'=>$error);
echo json_encode($json_array);

?>