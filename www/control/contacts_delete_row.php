<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	if (isset($_GET['type']) && !empty($_GET['type'])){
		
		if (isset($_GET['idrow']) && !empty($_GET['idrow']) && CheckIntegerUnsign($_GET['idrow']))
			$id_row = $_GET['idrow'];
		else
			require_once 'page404.php';
		
		$type = $_GET['type'];
		switch ($type){
			case 'phone':{
				// если телефон последний, то его удалять нельзя
				$qRes = SqlQuery("Select * From `CLIENT_PHONES` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
				if (mysql_num_rows($qRes) <= 1)
					require_once 'page404.php';
				@mysql_free_result($qRes);
				$table_name = 'CLIENT_PHONES';
				$title_suffix = 'телефон';
				break;
			}
			case 'email':{
				$table_name = 'CLIENT_EMAILS';
				$title_suffix = 'электронную почту';
				break;
			}
			case 'site':{
				$table_name = 'CLIENT_SITES';
				$title_suffix = 'сайт';
				break;
			}
			default:{
				require_once 'page404.php';
			}
		}
		
		// проверка соответствия данных с пользователем
		$qRes = SqlQuery("Select * From `".mysql_real_escape_string($table_name)."` Where `id`='".mysql_real_escape_string($id_row)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) !== 1)
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		if (isset($_GET['confirm']) && empty($_GET['confirm'])){
			// удаляем запись из БД
			SqlQuery("Delete From `".mysql_real_escape_string($table_name)."` Where `id`='".mysql_real_escape_string($id_row)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
			// переход на страницу управления данными пользователя
			header('Location: /kabinet/contacts-change');
		}
		
	}else
		require_once 'page404.php';

}else
	header('Location: /enter?link=/contacts-change');

$active_tab = 1;
$title = 'Личный кабинет - удалить '.$title_suffix;

require_once '../../includs/control/contacts_delete_row.html';

?>