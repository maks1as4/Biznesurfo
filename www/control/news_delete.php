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
		
		// �������� �������������� ������ ������������
		$qRes = SqlQuery("Select `title`, `text` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$log_old = 'title - '.$Row[0].'; text - '.$Row[1].';';
		}else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		if (isset($_GET['confirm']) && empty($_GET['confirm'])){
			// ���� ���� �������� ��� �������, �� �������
			$qRes = SqlQuery("Select `img`, `ext` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				if ($Row[0]!='' && $Row[1]!=''){
					// ������� ����
					chmod('../i/news', 0777);
					deleteImages('../i/news/', $Row[0], $Row[1]);
					deleteImages('../i/news/', $Row[0], $Row[1], '_small');
					chmod('../i/news', 0555);
				}
			}
			@mysql_free_result($qRes);
			// ������� ������ �� ��
			SqlQuery("Delete From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
			// �������� ��������� �������
			SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='3', `action`='2', `old`='".mysql_real_escape_string($log_old)."', `new`='-', `adate`='".date('Y-m-d H:i:s')."';");
			// ������� �� �������� ��������
			header('Location: /kabinet/news');
		}
		
	}else
		header('Location: /enter?link=/delete-news/'.$news_id);
	
}else
	require_once 'page404.php';

$active_tab = 2;
$title = '������ ������� - �������� �������';

require_once '../../includs/control/news_delete.html';

?>