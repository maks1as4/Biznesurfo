<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['cid']) && !empty($_GET['cid']) && CheckIntegerUnsign($_GET['cid'])){
		
		$client_id = $_GET['cid'];
		
		$qRes = SqlQuery("Select `logo` From `CLIENTS` Where `id`='".mysql_real_escape_string($client_id)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$client_logo = $Row[0];
		}else
			die('�������� ������������� �������');
		@mysql_free_result($qRes);
		
		if ($client_logo != ''){
			// ������� ������� �������� �� ���� ������
			SqlQuery("Update `CLIENTS` Set `logo`='', `status_logo`='0' Where `id`='".mysql_real_escape_string($client_id)."';");
			// "���������" ������� ��������
			$Finfo = pathinfo('../logo/'.$client_logo);
			chmod('../logo', 0777);
			deleteImages('../logo/', $Finfo['filename'], $Finfo['extension']);
			chmod('../logo', 0555);
			// ������������ �� �������� ��������� �������
			header('Location: /management/adm/logos-check');
		}else
			die('������� �����������');
		
	}else
		die('�������� ������������� �������');
	
}else
	header('Location: /enter');

?>