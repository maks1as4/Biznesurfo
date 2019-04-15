<?php

require_once "../../includs/head.php";

$error = false;
$need_hide = false;

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){

	if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['action']) && !empty($_POST['action'])){
		$id_row = $_POST['id'];
		$action = $_POST['action'];
		switch ($action){
			case 'p':{
				// если телефон последний, то его удалять нельзя
				$qRes = SqlQuery("Select * From `CLIENT_PHONES` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
				if (mysql_num_rows($qRes) <= 1)
					$error = true;
				@mysql_free_result($qRes);
				$table_name = 'CLIENT_PHONES';
				break;
			}
			case 'e':{
				$table_name = 'CLIENT_EMAILS';
				break;
			}
			case 's':{
				$table_name = 'CLIENT_SITES';
				break;
			}
			default:{
				$error = true;
			}
		}
		// проверка принадлежности данных пользователю
		$qRes = SqlQuery("Select * From `".mysql_real_escape_string($table_name)."` Where `id`='".mysql_real_escape_string($id_row)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) === 0)
			$error = true;
		@mysql_free_result($qRes);
		// ошибок нет, удаляем данные
		if (!$error){
			SqlQuery("Delete From `".mysql_real_escape_string($table_name)."` Where `id`='".mysql_real_escape_string($id_row)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
			if ($action == 'p'){
				$qRes = SqlQuery("Select * From `CLIENT_PHONES` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
				if (mysql_num_rows($qRes) === 1)
					$need_hide = true;
				@mysql_free_result($qRes);
			}
		}
	}else
		$error = true;

}else
	$error = true;

$json_array = array('error'=>$error, 'hide'=>$need_hide);
echo json_encode($json_array);

?>