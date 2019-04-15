<?php

function clearUserData($url = '/'){
	// сбразываем сессию и куки, если есть, перенаправл€ем на индекс
	global $cookieHost;
	if (isset($_SESSION['user'])) unset($_SESSION['user']);
	if (isset($_COOKIE['u_id']))  setcookie('u_id', '', time()-3600, '/', $cookieHost);
	if (isset($_COOKIE['u_key'])) setcookie('u_key', '', time()-3600, '/', $cookieHost);
	header('Location: '.$url);
}

if (isset($_SESSION['user'])){ // сесси€ есть
	if (is_array($_SESSION['user']) && (count($_SESSION['user']) == 6)){ // сесси€ - массив из 6 элементов
		if (($_SESSION['user']['ip'] == $_SERVER['REMOTE_ADDR']) && ($_SESSION['user']['browser'] == getBrowser())){ // проверка ip и браузера
			// дл€ удобства инициализируем более пон€тные переменные
			$user_id = $_SESSION['user']['id'];
			$user_client_id = $_SESSION['user']['id_client'];
			$user_email = $_SESSION['user']['email'];
			$user_role = $_SESSION['user']['role'];
		}else{ // ip и браузер не прошли проверку
			clearUserData();
		}
	}else{ // если сесси€ не €вл€етс€ массивом из 7 переменных
		clearUserData();
	}
}else{ // сесси€ не активна
	// провер€ем есть ли куки и соотвествуют ли они данным
	if (isset($_COOKIE['u_id']) && isset($_COOKIE['u_key']) && $_COOKIE['u_id']!='' && $_COOKIE['u_key']!='' && CheckStr(10, $_COOKIE['u_key'], 32, true) && CheckIntegerUnsign($_COOKIE['u_id'])){
		// ищем запись в Ѕƒ по данным куков (id)
		$qRes = SqlQuery("Select `id`, `id_client`, `email`, `role`, Inet_ntoa(`ip`), `hash` From `MEMBERS` Where `id`='".mysql_real_escape_string($_COOKIE['u_id'])."';");
		$userdata = mysql_fetch_row($qRes);
		// если хеш куков совпадает с хешем в Ѕƒ и ip совпадает, то создаем сессию юзера
		if (($userdata[4] == $_SERVER['REMOTE_ADDR']) && ($userdata[4] != '0.0.0.0') && ($userdata[5] == $_COOKIE['u_key'])){
			$_SESSION['user']['id']        = $user_id        = $userdata[0];
			$_SESSION['user']['id_client'] = $user_client_id = $userdata[1];
			$_SESSION['user']['email']     = $user_email     = $userdata[2];
			$_SESSION['user']['role']      = $user_role      = $userdata[3];
			$_SESSION['user']['ip']        = $_SERVER['REMOTE_ADDR'];
			$_SESSION['user']['browser']   = getBrowser();
		}else{ // хеш или ip не совпали
			clearUserData();
		}
		@mysql_free_result($qRes);
	}
}

?>