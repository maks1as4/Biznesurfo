<?php

function clearUserData($url = '/'){
	// ���������� ������ � ����, ���� ����, �������������� �� ������
	global $cookieHost;
	if (isset($_SESSION['user'])) unset($_SESSION['user']);
	if (isset($_COOKIE['u_id']))  setcookie('u_id', '', time()-3600, '/', $cookieHost);
	if (isset($_COOKIE['u_key'])) setcookie('u_key', '', time()-3600, '/', $cookieHost);
	header('Location: '.$url);
}

if (isset($_SESSION['user'])){ // ������ ����
	if (is_array($_SESSION['user']) && (count($_SESSION['user']) == 6)){ // ������ - ������ �� 6 ���������
		if (($_SESSION['user']['ip'] == $_SERVER['REMOTE_ADDR']) && ($_SESSION['user']['browser'] == getBrowser())){ // �������� ip � ��������
			// ��� �������� �������������� ����� �������� ����������
			$user_id = $_SESSION['user']['id'];
			$user_client_id = $_SESSION['user']['id_client'];
			$user_email = $_SESSION['user']['email'];
			$user_role = $_SESSION['user']['role'];
		}else{ // ip � ������� �� ������ ��������
			clearUserData();
		}
	}else{ // ���� ������ �� �������� �������� �� 7 ����������
		clearUserData();
	}
}else{ // ������ �� �������
	// ��������� ���� �� ���� � ������������ �� ��� ������
	if (isset($_COOKIE['u_id']) && isset($_COOKIE['u_key']) && $_COOKIE['u_id']!='' && $_COOKIE['u_key']!='' && CheckStr(10, $_COOKIE['u_key'], 32, true) && CheckIntegerUnsign($_COOKIE['u_id'])){
		// ���� ������ � �� �� ������ ����� (id)
		$qRes = SqlQuery("Select `id`, `id_client`, `email`, `role`, Inet_ntoa(`ip`), `hash` From `MEMBERS` Where `id`='".mysql_real_escape_string($_COOKIE['u_id'])."';");
		$userdata = mysql_fetch_row($qRes);
		// ���� ��� ����� ��������� � ����� � �� � ip ���������, �� ������� ������ �����
		if (($userdata[4] == $_SERVER['REMOTE_ADDR']) && ($userdata[4] != '0.0.0.0') && ($userdata[5] == $_COOKIE['u_key'])){
			$_SESSION['user']['id']        = $user_id        = $userdata[0];
			$_SESSION['user']['id_client'] = $user_client_id = $userdata[1];
			$_SESSION['user']['email']     = $user_email     = $userdata[2];
			$_SESSION['user']['role']      = $user_role      = $userdata[3];
			$_SESSION['user']['ip']        = $_SERVER['REMOTE_ADDR'];
			$_SESSION['user']['browser']   = getBrowser();
		}else{ // ��� ��� ip �� �������
			clearUserData();
		}
		@mysql_free_result($qRes);
	}
}

?>