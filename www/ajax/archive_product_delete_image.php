<?php

require_once "../../includs/head.php";

$error = false;

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){

	if (isset($_POST['id']) && !empty($_POST['id'])){
		$image_id = $_POST['id'];
		// проверка принадлежности данных пользователю
		$qRes = SqlQuery("Select S.`id`, SI.`name`, SI.`ext` From `STR_IMG` SI left join `STR` S on SI.`id_product`=S.`id` Where SI.`id`='".mysql_real_escape_string($image_id)."' and S.`client`='".mysql_real_escape_string($user_client_id)."' and S.`active`='0';");
		if (mysql_num_rows($qRes) === 1)
			$Row = mysql_fetch_row($qRes);
		else
			$error = true;
		@mysql_free_result($qRes);
		// ошибок нет, удаляем картинку
		if (!$error){
			// удаляем фото
			chmod('../i/products', 0777);
			deleteImages('../i/products/', $Row[1], $Row[2]);
			deleteImages('../i/products/', $Row[1], $Row[2], '_medium');
			deleteImages('../i/products/', $Row[1], $Row[2], '_small');
			chmod('../i/products', 0555);
			// удаляем строку из БД
			SqlQuery("Delete From `STR_IMG` Where `id`='".mysql_real_escape_string($image_id)."';");
		}
	}else
		$error = true;

}else
	$error = true;

$json_array = array('error'=>$error);
echo json_encode($json_array);

?>