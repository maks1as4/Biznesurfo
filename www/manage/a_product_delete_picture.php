<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['picid']) && !empty($_GET['picid']) && CheckIntegerUnsign($_GET['picid'])){
		
		$picture_id = $_GET['picid'];
		
		$qRes = SqlQuery("Select `name`, `ext`, `id_product` From `STR_IMG` Where `id`='".mysql_real_escape_string($picture_id)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			// удал€ем картинку из базы данных
			SqlQuery("Delete From `STR_IMG` Where `id`='".mysql_real_escape_string($picture_id)."';");
			// "физически" удал€ем картинки
			chmod('../i/products', 0777);
			deleteImages('../i/products/', $Row[0], $Row[1]);
			deleteImages('../i/products/', $Row[0], $Row[1], '_medium');
			deleteImages('../i/products/', $Row[0], $Row[1], '_small');
			chmod('../i/products', 0555);
			// возвращаемс€ на страницу изменение клиента
			header('Location: /management/adm/product-edit/'.$Row[2]);
		}else
			die('Ќеверный идентификатор картинки');
		@mysql_free_result($qRes);
		
	}else
		die('Ќеверный идентификатор картинки');
	
}else
	header('Location: /enter');

?>