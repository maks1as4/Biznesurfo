<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	if (count($_POST)>0){
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		if (isset($_POST['multi_action']) && !empty($_POST['multi_action'])){
			switch ($_POST['multi_action']){
				case 'showAll':{
					foreach ($_POST['multi_items'] as $news_id)
						SqlQuery("Update `CLIENT_NEWS` Set `visible`='1' Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
					break;
				}
				case 'hideAll':{
					foreach ($_POST['multi_items'] as $news_id)
						SqlQuery("Update `CLIENT_NEWS` Set `visible`='0' Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
					break;
				}
				case 'deleteAll':{
					foreach ($_POST['multi_items'] as $news_id){
						$log_old = '';
						$qRes = SqlQuery("Select `title`, `text`, `img`, `ext` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
						if (mysql_num_rows($qRes) === 1){
							$Row = mysql_fetch_row($qRes);
							$log_old = 'title - '.$Row[0].'; text - '.$Row[1].';';
							// если есть картинка дл€ новости, то удал€ем
							if ($Row[2]!='' && $Row[3]!=''){
								chmod('../i/news', 0777);
								deleteImages('../i/news/', $Row[2], $Row[3]);
								deleteImages('../i/news/', $Row[2], $Row[3], '_small');
								chmod('../i/news', 0555);
							}
						}
						@mysql_free_result($qRes);
						// удал€ем данные из Ѕƒ
						SqlQuery("Delete From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
						// логируем удал€емую новость
						if ($log_old != '')
							SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='3', `action`='2', `old`='".mysql_real_escape_string($log_old)."', `new`='-', `adate`='".date('Y-m-d H:i:s')."';");
					}
					break;
				}
				default:{
					require_once 'page404.php';
				}
			}
			header('Location: /kabinet/news');
		}else
			require_once 'page404.php';
	}else
		require_once 'page404.php';
}else
	header('Location: /enter?link=/news');

?>