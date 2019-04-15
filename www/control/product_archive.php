<?php

require_once '../../includs/head.php';

if (isset($_GET['tid']) && !empty($_GET['tid']) && CheckIntegerUnsign($_GET['tid'])){
	
	$tovar_id = $_GET['tid'];
	
	if (isset($user_id) && CheckIntegerUnsign($user_id)){
		
		$client_info = getClientInfo($user_client_id);
		
		if ($client_info['end']){
			header('Location: /kabinet/end');
			exit;
		}
		
		// проверка принадлежности данных пользователю
		$log_old = '';
		$qRes = SqlQuery("Select `id` From `STR` Where `id`='".mysql_real_escape_string($tovar_id)."' and `client`='".mysql_real_escape_string($user_client_id)."' and `active`='1';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$log_old = 'id - '.$Row[0];
		}else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		// отправляем товар в архив
		SqlQuery("Update `STR` Set `active`='0', `udate`='".date('Y-m-d H:i:s')."' Where `id`='".mysql_real_escape_string($tovar_id)."';");
		
		// логируем архивируемый товар
		SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='2', `action`='3', `old`='".mysql_real_escape_string($log_old)."', `new`='-', `adate`='".date('Y-m-d H:i:s')."';");
		
		// переход на страницу управления товарами
		header('Location: /kabinet');
		
	}else
		header('Location: /enter?link=/product-archive/'.$tovar_id);
	
}else
	require_once 'page404.php';

?>