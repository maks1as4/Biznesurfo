<?php

require_once "../../includs/head.php";

$error = false;

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
	
	if (isset($_POST['id']) && !empty($_POST['id'])){
		$news_id = $_POST['id'];
		// проверка принадлежности данных пользователю
		$qRes = SqlQuery("Select `img`, `ext` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) === 1)
			$Row = mysql_fetch_row($qRes);
		else
			$error = true;
		@mysql_free_result($qRes);
		// ошибок нет, удаляем картинку
		if (!$error){
			// удаляем фото
			chmod('../i/news', 0777);
			deleteImages('../i/news/', $Row[0], $Row[1]);
			deleteImages('../i/news/', $Row[0], $Row[1], '_small');
			chmod('../i/news', 0555);
			// очищаем картинки из БД
			SqlQuery("Update `CLIENT_NEWS` Set `img`='', `ext`='' Where `id`='".mysql_real_escape_string($news_id)."';");
		}
	}else
		$error = true;
	
}else
	$error = true;

$json_array = array('error'=>$error);
echo json_encode($json_array);

?>