<?php

require_once '../../includs/head.php';

if (isset($_GET['nid']) && !empty($_GET['nid']) && CheckIntegerUnsign($_GET['nid'])){
	
	$news_id = $_GET['nid'];
	
	if (isset($user_id) && CheckIntegerUnsign($user_id)){
		
		$client_info = getClientInfo($user_client_id);
		
		if ($client_info['end']){
			header('Location: /kabinet/end');
			exit;
		}
		
		// проверка принадлежности данных пользователю
		$qRes = SqlQuery("Select `img`, `ext` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
		}else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		if (($Row[0] == '') || ($Row[1] == ''))
			require_once 'page404.php';
		
		if (isset($_GET['confirm']) && empty($_GET['confirm'])){
			// удаляем фото
			chmod('../i/news', 0777);
			deleteImages('../i/news/', $Row[0], $Row[1]);
			deleteImages('../i/news/', $Row[0], $Row[1], '_small');
			chmod('../i/news', 0555);
			// очищаем картинки из БД
			SqlQuery("Update `CLIENT_NEWS` Set `img`='', `ext`='' Where `id`='".mysql_real_escape_string($news_id)."';");
			// переход на страницу управления новостью
			header('Location: /kabinet/edit-news/'.$news_id);
		}
		
	}else
		header('Location: /enter?link=/news-delete-image/'.$news_id);
	
}else
	require_once 'page404.php';

$active_tab = 2;
$title = 'Личный кабинет - удаление изображения новости';

require_once '../../includs/control/news_delete_image.html';

?>