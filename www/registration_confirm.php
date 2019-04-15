<?php

require_once '../includs/head.php';

if (isset($_GET['hash']) && !empty($_GET['hash'])){
	$hash = $_GET['hash'];
	if (preg_match('/^[a-z0-9]{32}$/', $hash)){
		// получаем запись временной регистрации по уникальному хешу
		$qRes = SqlQuery("Select `id`, `opf`, `organisation`, `fio`, `email`, `password`, `add_solt`, `full_phone`, `code`, `phone` From `REGISTRATION_tmp` Where `hash` = '".mysql_real_escape_string($hash)."' Limit 1;");
		if (mysql_num_rows($qRes) == 1){
			
			$Clients = mysql_fetch_row($qRes);
			$id_tmp = $Clients[0];
			$fullname = ($Clients[1] != '') ? $Clients[2].','.$Clients[1] : $Clients[2];
			$fio = $Clients[3];
			$email = $Clients[4];
			$password = $Clients[5];
			$solt = $Clients[6];
			$full_phone = $Clients[7];
			$code = $Clients[8];
			$phone = $Clients[9];
			@mysql_free_result($qRes);
			
			$email = mysql_real_escape_string($email);
			$password = mysql_real_escape_string($password);
			$now = date("Y-m-d H:i:s");
			
			// добавляем запись в таблицу клиентс
			SqlQuery("Insert Into `CLIENTS` Set `id_client`=NULL, `region`='0', `city`='0', `city_name`='', `name`='".mysql_real_escape_string($fullname)."', `translit`=NULL, `index`='', `street`='', `house`='', `office`='', `aya`='', `address`='', `logo`='', `bold_rows`='0', `positions`='10', `goods_qty`='0', `coord`='', `map_zoom`='0', `status`='0', `status_logo`='0', `journal`='0', `adate`='".$now."', `udate`='".$now."';");
			
			// вычисляем последний добавленный id CLIENTS автоинкрементом
			$id_last = lastInsertId();
			
			// добавляем запись в таблицу абаут
			SqlQuery("Insert Into `CLIENTS_ABOUT` Set `id_client`='".$id_last."', `about`='', `status`='0';");
			
			// добавляем запись в таблицу телефонов
			SqlQuery("Insert Into `CLIENT_PHONES` Set `id_client`='".$id_last."', `full_phone`='".mysql_real_escape_string($full_phone)."', `code`='".mysql_real_escape_string($code)."', `phone`='".mysql_real_escape_string($phone)."', `city_code`='', `sort_order`='1'");
			
			// добавляем запись в таблицу мемберс
			SqlQuery("Insert Into `MEMBERS` Set `id_client`='".$id_last."', `email`='".$email."', `password`='".$password."', `add_solt`='".mysql_real_escape_string($solt)."', `fio`='".mysql_real_escape_string($fio)."', `ip`='0', `hash`='', `role`='com', `notice`='0', `date_add`='".$now."';");
			
			// вычисляем последний добавленный id MEMBERS автоинкрементом
			$id_member_last = lastInsertId();
			
			// логируем добавляющуюся компанию
			$log_new = 'id - '.$id_last.'; name - '.$fullname;
			SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($id_member_last)."', `type`='4', `action`='0', `old`='-', `new`='".mysql_real_escape_string($log_new)."', `adate`='".$now."';");
			
			// удаляем запись временной регистрации
			SqlQuery("Delete From `REGISTRATION_tmp` Where `id`='".mysql_real_escape_string($id_tmp)."' Limit 1;");
			
			// чистим сессию и куки, если пользователь был авторизирован в момент активации другой регистрации
			if (isset($user_id)){
				SqlQuery("Update `MEMBERS` Set `ip`='0', `hash`='' Where `id`='".mysql_real_escape_string($user_id)."';");
				if (isset($_SESSION['user'])) unset($_SESSION['user']);
				if (isset($_COOKIE['u_id']))  setcookie('u_id', '', time()-3600, '/', $cookieHost);
				if (isset($_COOKIE['u_key'])) setcookie('u_key', '', time()-3600, '/', $cookieHost);
			}
			
			// вход пользователя в кабинет после подтверждения регистрации
			$qRes = SqlQuery("Select `id`, `id_client`, `email`, `role` From `MEMBERS` Where `email`='".$email."' and `password`='".$password."' Limit 1;");
			
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$_SESSION['user']['id']        = $Row[0];
				$_SESSION['user']['id_client'] = $Row[1];
				$_SESSION['user']['email']     = $Row[2];
				$_SESSION['user']['role']      = $Row[3];
				$_SESSION['user']['ip']      = $_SERVER['REMOTE_ADDR'];
				$_SESSION['user']['browser'] = getBrowser();
			}
			
			@mysql_free_result($qRes);
			
			//header('Location: /');
			header('Location: /kabinet');
			
		}else
			require_once 'page404.php';
	}else
		require_once 'page404.php';
}else
	require_once 'page404.php';

?>