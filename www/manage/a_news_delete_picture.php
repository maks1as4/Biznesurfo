<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['nid']) && !empty($_GET['nid']) && CheckIntegerUnsign($_GET['nid'])){
		
		$news_id = $_GET['nid'];
		
		$qRes = SqlQuery("Select `img`, `ext` From `CLIENT_NEWS` Where `id`='".mysql_real_escape_string($news_id)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			// ������� �������� �� ���� ������
			SqlQuery("Update `CLIENT_NEWS` Set `img`='', `ext`='' Where `id`='".mysql_real_escape_string($news_id)."';");
			// "���������" ������� ��������
			chmod('../i/news', 0777);
			deleteImages('../i/news/', $Row[0], $Row[1]);
			deleteImages('../i/news/', $Row[0], $Row[1], '_small');
			chmod('../i/news', 0555);
			// ������������ �� �������� ��������� �������
			header('Location: /management/adm/news-edit/'.$news_id);
		}else
			die('�������� ������������� ��������');
		@mysql_free_result($qRes);
		
	}else
		die('�������� ������������� ��������');
	
}else
	header('Location: /enter');

?>