<?php

require_once '../../includs/head.php';

if (isset($_GET['pid']) && !empty($_GET['pid']) && CheckIntegerUnsign($_GET['pid'])){
	
	$image_id = $_GET['pid'];
	
	if (isset($user_id) && CheckIntegerUnsign($user_id)){
		
		$client_info = getClientInfo($user_client_id);
		
		if ($client_info['end']){
			header('Location: /kabinet/end');
			exit;
		}
		
		// проверка принадлежности данных пользователю
		$qRes = SqlQuery("Select S.`id`, SI.`name`, SI.`ext` From `STR_IMG` SI left join `STR` S on SI.`id_product`=S.`id` Where SI.`id`='".mysql_real_escape_string($image_id)."' and S.`client`='".mysql_real_escape_string($user_client_id)."' and S.`active`='1';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$tovar_id = $Row[0];
		}else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		if (isset($_GET['confirm']) && empty($_GET['confirm'])){
			// удаляем фото
			chmod('../i/products', 0777);
			deleteImages('../i/products/', $Row[1], $Row[2]);
			deleteImages('../i/products/', $Row[1], $Row[2], '_medium');
			deleteImages('../i/products/', $Row[1], $Row[2], '_small');
			chmod('../i/products', 0555);
			// удаляем строку из БД
			SqlQuery("Delete From `STR_IMG` Where `id`='".mysql_real_escape_string($image_id)."';");
			// переход на страницу управления товарами
			header('Location: /kabinet/product-edit/'.$tovar_id);
		}
		
	}else
		header('Location: /enter?link=/product-delete-image/'.$image_id);
	
}else
	require_once 'page404.php';

$active_tab = 0;
$title = 'Личный кабинет - удаление изображения товара';

require_once '../../includs/control/product_delete_image.html';

?>