<?php

require_once '../../includs/head.php';

if (isset($_GET['nid']) && !empty($_GET['nid']) && CheckIntegerUnsign($_GET['nid'])){
	
	$news_id = $_GET['nid'];
	
	if (isset($user_id) && CheckIntegerUnsign($user_id)){
		
		$client_info = getClientInfo($user_client_id);
		
		if ($client_info['end'])}
			header('Location: /kabinet/end');
			exit;
		}
		
		// проверка принадлежности данных пользователю
		$qRes = SqlQuery("Select `visible` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			// новое значение видимости
			$new_visible = ($Row[0] == 1) ? 0 : 1;
			SqlQuery("Update `CLIENT_NEWS` Set `visible`='".$new_visible."' Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
			// переход на страницу новостей
			header('Location: /kabinet/news');
		}else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
	}else
		header('Location: /enter?link=/delete-news/'.$news_id);
	
}else
	require_once 'page404.php';

?>