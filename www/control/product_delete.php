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
		$qRes = SqlQuery("Select `rubric`, `name`, `price`, `text` From `STR` Where `id`='".mysql_real_escape_string($tovar_id)."' and `client`='".mysql_real_escape_string($user_client_id)."' and `active`='1';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$log_old = 'rubric - '.$Row[0].'; name - '.$Row[1].'; price - '.$Row[2].'; text - '.$Row[3];
		}else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		if (isset($_GET['confirm']) && empty($_GET['confirm'])){
			// удал€ем данные из Ѕƒ
			SqlQuery("Delete From `STR` Where `id`='".mysql_real_escape_string($tovar_id)."' and `client`='".mysql_real_escape_string($user_client_id)."' and `active`='1';");
			// если есть картинка дл€ товара, то удал€ем
			$qRes = SqlQuery("Select `name`, `ext` From `STR_IMG` Where `id_product`='".mysql_real_escape_string($tovar_id)."';");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				// удал€ем фото
				chmod('../i/products', 0777);
				deleteImages('../i/products/', $Row[0], $Row[1]);
				deleteImages('../i/products/', $Row[0], $Row[1], '_medium');
				deleteImages('../i/products/', $Row[0], $Row[1], '_small');
				chmod('../i/products', 0555);
				// удал€ем строку из Ѕƒ
				SqlQuery("Delete From `STR_IMG` Where `id_product`='".mysql_real_escape_string($tovar_id)."';");
			}
			@mysql_free_result($qRes);
			// логируем удал€емый товар
			SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='2', `action`='2', `old`='".mysql_real_escape_string($log_old)."', `new`='-', `adate`='".date('Y-m-d H:i:s')."';");
			// переход на прайс-лист
			header('Location: /kabinet');
		}
		
	}else
		header('Location: /enter?link=/product-delete/'.$tovar_id);
	
}else
	require_once 'page404.php';

$active_tab = 0;
$title = 'Ћичный кабинет - удаление товара';

require_once '../../includs/control/product_delete.html';

?>