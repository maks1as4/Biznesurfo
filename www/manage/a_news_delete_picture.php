<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['nid']) && !empty($_GET['nid']) && CheckIntegerUnsign($_GET['nid'])){
		
		$news_id = $_GET['nid'];
		
		$qRes = SqlQuery("Select `img`, `ext` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			// удал€ем картинку из базы данных
			SqlQuery("Update `CLIENT_NEWS` Set `img`='', `ext`='' Where `id`='".mysql_real_escape_string($news_id)."';");
			// "физически" удал€ем картинки
			chmod('../i/news', 0777);
			deleteImages('../i/news/', $Row[0], $Row[1]);
			deleteImages('../i/news/', $Row[0], $Row[1], '_small');
			chmod('../i/news', 0555);
			// возвращаемс€ на страницу изменени€ новости
			header('Location: /management/adm/news-edit/'.$news_id);
		}else
			die('Ќеверный идентификатор картинки');
		@mysql_free_result($qRes);
		
	}else
		die('Ќеверный идентификатор картинки');
	
}else
	header('Location: /enter');

?>