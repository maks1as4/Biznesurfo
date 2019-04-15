<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['tid']) && !empty($_GET['tid']) && CheckIntegerUnsign($_GET['tid'])){
		
		$tovar_id = $_GET['tid'];
		$qRes = SqlQuery("Update `STR` Set `status`='0' Where `id`='".mysql_real_escape_string($tovar_id)."';");
		header('Location: /management/adm/products-check');
		
	}else
		die('Неверный идентификатор товара');
	
}else
	header('Location: /enter');

?>