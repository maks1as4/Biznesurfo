<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	// наличие логотипа компании
	$qRes = SqlQuery("Select `logo` From `CLIENTS` Where `id`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	if (isset($_GET['confirm']) && empty($_GET['confirm'])){
		// если есть логотип, то удаляем
		if ($Row[0] != ''){
			// удаляем логотип
			$Finfo = pathinfo('../logo/'.$Row[0]);
			chmod('../logo', 0777);
			deleteImages('../logo/', $Finfo['filename'], $Finfo['extension']);
			chmod('../logo', 0555);
			unset($Finfo);
			// удаляем данные из БД
			SqlQuery("Update `CLIENTS` Set `logo`='', `status_logo`='0', `udate`='".date('Y-m-d H:i:s')."' Where `id`='".mysql_real_escape_string($user_client_id)."';");
			// переход на страницу управления данными пользователя
			header('Location: /kabinet/about');
		}else
			require_once 'page404.php';
	}

}else
	header('Location: /enter?link=/logo-delete');

$active_tab = 1;
$title = 'Личный кабинет - удалить логотип компании';

require_once '../../includs/control/logo_delete.html';

?>