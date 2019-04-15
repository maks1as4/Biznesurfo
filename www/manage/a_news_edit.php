<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['nid']) && !empty($_GET['nid']) && CheckIntegerUnsign($_GET['nid'])){
		
		$news_id = $_GET['nid'];
		
		$News = array();
		$qRes = SqlQuery("
			Select C.`name`, CN.`title`, CN.`text`, CN.`img`, CN.`ext`, CN.`url`
			From `CLIENT_NEWS` CN
				join `CLIENTS` C on CN.`id_client`=C.`id`
			Where CN.`id`='".mysql_real_escape_string($news_id)."';
		");
		if (mysql_num_rows($qRes) === 1)
			$News = mysql_fetch_row($qRes);
		else
			die('Неверный идентификатор новости');
		@mysql_free_result($qRes);
		
		$var_title = $News[1];
		$var_url = $News[5];
		$var_text = $News[2];
		$var_img_name = ($News[3] != null) ? $News[3] : '';
		$var_ing_ext = ($News[4] != null) ? $News[4] : '';
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			if (isset($_POST['utitle']) && ($_POST['utitle']!='')){
				//$var_title = $_POST['utitle'];
				$var_title = stripslashes($_POST['utitle']); // -gpc-
			}else
				$err .= ' - Не заполнено поле "наименование новости".<br />';
			
			if (isset($_POST['uurl']) && ($_POST['uurl']!='')){
				$var_url = $_POST['uurl'];
			}else
				$err .= ' - Не заполнено поле "url новости".<br />';
			
			if (isset($_POST['utext']) && ($_POST['utext']!='')){
				//$var_text = $_POST['utext'];
				$var_text = stripslashes($_POST['utext']); // -gpc-
			}else
				$err .= ' - Не заполнено поле "текст новости".<br />';
			
			if ($err == ''){
				// изменяем данные таблицы новостей
				SqlQuery("Update `CLIENT_NEWS` Set `title`='".mysql_real_escape_string($var_title)."', `url`='".mysql_real_escape_string($var_url)."', `text`='".mysql_real_escape_string($var_text)."' Where `id`='".mysql_real_escape_string($news_id)."';");
				// переходим на страницу проверки новостей
				header('Location: /management/adm/news-check');
			}else{
				$var_title = htmlspecialchars($var_title, ENT_QUOTES, 'cp1251');
				$var_url = htmlspecialchars($var_url, ENT_QUOTES, 'cp1251');
				$var_text = htmlspecialchars($var_text, ENT_QUOTES, 'cp1251');
			}
		}
		
	}else
		die('Неверный идентификатор новости');
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_news_edit.html';

?>