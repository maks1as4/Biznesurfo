<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['cid']) && !empty($_GET['cid']) && CheckIntegerUnsign($_GET['cid'])){
		
		$client_id = $_GET['cid'];
		$qRes = SqlQuery("Update `CLIENTS` Set `status`='0' Where `id`='".mysql_real_escape_string($client_id)."';");
		$qRes = SqlQuery("Update `CLIENTS_ABOUT` Set `status`='0' Where `id_client`='".mysql_real_escape_string($client_id)."';");
		header('Location: /management/adm/clients-check');
		
	}else
		die('Неверный идентификатор клиента');
	
}else
	header('Location: /enter');

?>