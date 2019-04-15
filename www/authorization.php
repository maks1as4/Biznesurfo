<?php

require_once '../includs/head.php';

if (isset($_GET['logout']) && empty($_GET['logout'])){

	if (isset($_SESSION['user'])){
	
		SqlQuery("Update `MEMBERS` Set `ip`='0', `hash`='' Where `id`='".mysql_real_escape_string($user_id)."';");
		
		if (isset($_SESSION['user'])) unset($_SESSION['user']);
		if (isset($_COOKIE['u_id']))  setcookie('u_id', '', time()-3600, '/', $cookieHost);
		if (isset($_COOKIE['u_key'])) setcookie('u_key', '', time()-3600, '/', $cookieHost);
		
		if (isset($_GET['gohome']) && empty($_GET['gohome'])) $url = '/';
		else $url = ($_SERVER['HTTP_REFERER'] != '') ? $_SERVER['HTTP_REFERER'] : '/';
		
		header('Location: '.$url);
		exit;
	
	}else
		header('Location: /');

}

$val_mail = '';
$val_pass = '';
$err = '';
$chek_password = false;

$goto = (isset($_GET['link']) && !empty($_GET['link'])) ? $_GET['link'] : '';

if (count($_POST)>0){
	if (!isset($_POST['btmes']))
		require_once 'page404.php';
	if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
		require_once 'page404.php';
	
	// Электронная почта
	if ((isset($_POST['umail']) && ($_POST['umail']!=''))){
		$val_mail = $_POST['umail'];
		if (strlen($val_mail) > 100)
			$err .= ' - Email слишком длинный, ограничение 100 символов.<br />';
		if (!CheckEmail($val_mail))
			$err .= ' - Email имеет недопустимый формат.<br />';
		else{
			$email = mysql_real_escape_string($val_mail);
			$qRes = SqlQuery("Select `add_solt` From `MEMBERS` Where `email`='".$email."' Limit 1");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$salt = $Row[0];
				$chek_password = true;
			}else
				$err .= ' - Пользователя с таким почтовым адресом не существует.<br />';
			@mysql_free_result($qRes);
		}
	}else
		$err .= ' - Не указан "Email".<br />';
	
	// Пароль
	if ((isset($_POST['upass']) && ($_POST['upass']!=''))){
		$val_pass = $_POST['upass'];
		if ((strlen($val_pass)<3) || (strlen($val_pass)>10))
			$err .= ' - Пароль должен быть от 3 до 10 символов.<br />';
		if (!preg_match("/^[a-zA-Z0-9_-]+$/", $val_pass))
			$err .= ' - Пароль может состоять только из букв английского алфавита, цифр, знака подчеркивания, тире.<br />';
		else{
			if ($chek_password){ // если email найден, то проверяем пароль
				$password = md5(md5($val_pass).$salt);
				$qRes = SqlQuery("Select `id`, `id_client`, `email`, `role` From `MEMBERS` Where `email`='".$email."' and `password`='".$password."' Limit 1;");
				if (mysql_num_rows($qRes) === 1){
					$Row = mysql_fetch_row($qRes);
					$temp_id        = $Row[0];
					$temp_id_client = $Row[1];
					$temp_email     = $Row[2];
					$temp_role      = $Row[3];
				}else
					$err .= ' - Неверный пароль.<br />';
				@mysql_free_result($qRes);
			}
		}
	}else
		$err .= ' - Не указан "Пароль".<br />';
	
	if ($err == ''){		
		// прошли проверку-вносим данные в сессию
		$_SESSION['user']['id']    = $temp_id;
		$_SESSION['user']['id_client'] = $temp_id_client;
		$_SESSION['user']['email'] = $temp_email;
		$_SESSION['user']['role']  = $temp_role;
		$_SESSION['user']['ip']    = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user']['browser'] = getBrowser();
		
		if ((isset($_POST['uremembeme'])) && ($_POST['uremembeme'] == 'yes')){
			$hash = md5(generateCode(10));
			SqlQuery("Update `MEMBERS` Set `ip`=Inet_aton('".$_SERVER['REMOTE_ADDR']."'), `hash`='".$hash."' Where `id`='".mysql_real_escape_string($temp_id)."';");
			setcookie('u_id', $temp_id, time()+3600*24*$cookie_days, '/', $cookieHost);
			setcookie('u_key', $hash, time()+3600*24*$cookie_days, '/', $cookieHost);
		}
		
		unset($temp_id);
		unset($temp_id_client);
		unset($temp_email);
		
		// переход в зависимости от роли пользователя
		/*switch ($temp_role){
			case 'adm':{
				header('Location: /management/adm');
				break;
			}
			default:
				header('Location: /kabinet'.$goto);
		}*/
		
		unset($temp_role);
		
		if ($goto == '')
			header('Location: /');
		else
			header('Location: /kabinet'.$goto);
	}else{
		$val_mail = htmlspecialchars($val_mail , ENT_QUOTES, 'cp1251');
	}
}

$jqAdd = <<<EoL

	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				umail: {
					required: true,
					email2: true
				},
				upass: {
					required: true,
					rangelength: [3, 10],
					pattern: /^[\-a-zA-Z0-9_]+$/i
				}
			},
			messages: {
				umail: {
					required: 'введите email',
					email2: 'email имеет некорректный формат'
				},
				upass: {
					required: 'введите пароль',
					rangelength: 'пароль должен быть от 3 до 10 символов',
					pattern: 'только буквы английского алфавита, цифры, знак подчеркивания, тире'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});


EoL;

$title = 'Вход в кабинет';

require_once '../includs/authorization.html';

?>