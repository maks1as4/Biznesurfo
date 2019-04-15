<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['nid']) && !empty($_GET['nid']) && CheckIntegerUnsign($_GET['nid'])){
		
		$news_id = $_GET['nid'];
		$qRes = SqlQuery("Update `CLIENT_NEWS` Set `status`='0' Where `id`='".mysql_real_escape_string($news_id)."';");
		header('Location: /management/adm/news-check');
		
	}else
		die('Неверный идентификатор товара');
	
}else
	header('Location: /enter');

?>