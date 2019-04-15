<?php

require_once '../includs/head.php';

if (isset($_GET['hash']) && !empty($_GET['hash'])){
	$hash = $_GET['hash'];
	if (preg_match('/^[a-z0-9]{32}$/', $hash)){
		// получаем запись временной регистрации по уникальному хешу
		$qRes = SqlQuery("Select `email` From `CHANGE_PASSWORD_tmp` Where `hash` = '".mysql_real_escape_string($hash)."' Limit 1;");
		if (mysql_num_rows($qRes) == 1){
			
			$Row = mysql_fetch_row($qRes);
			$email = mysql_real_escape_string($Row[0]);
			@mysql_free_result($qRes);
			
			$val_pass = '';
			$err = '';
			
			if (count($_POST)>0){
				if (!isset($_POST['btmes']))
					require_once 'page404.php';
				if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
					require_once 'page404.php';
				
				// Пароль
				if ((isset($_POST['upass']) && ($_POST['upass']!=''))){
					$val_pass = $_POST['upass'];
					if ((strlen($val_pass)<3) || (strlen($val_pass)>10))
						$err .= ' - Пароль должен быть от 3 до 10 символов.<br />';
					if (!preg_match("/^[a-zA-Z0-9_-]+$/", $val_pass))
						$err .= ' - Пароль может состоять только из букв английского алфавита, цифр, знака подчеркивания, тире.<br />';
				}else
					$err .= ' - Не указан "Пароль".<br />';
				
				if ($err == ''){
					// меняем пароль пользователю
					$salt = generateCode(3);
					$val_pass = md5(md5($val_pass).$salt);
					SqlQuery("Update `MEMBERS` Set `password`='".$val_pass."', `add_solt`='".$salt."' Where `email`='".$email."' Limit 1;");
					
					// удаляем запись из таблицы CHANGE_PASSWORD_tmp
					SqlQuery("Delete From `CHANGE_PASSWORD_tmp` Where `hash`='".mysql_real_escape_string($hash)."' or `email`='".$email."';");
					
					// вход пользователя в кабинет после смены пароля
					$qRes = SqlQuery("Select `id`, `id_client`, `email`, `role` From `MEMBERS` Where `email`='".$email."' and `password`='".$val_pass."' Limit 1;");
					
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
					
					// логируем смену пароля
					SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($Row[0])."', `type`='5', `action`='1', `old`='-', `new`='-', `adate`='".date('Y-m-d H:i:s')."';");
					
					//header('Location: /');
					header('Location: /kabinet');
				}
			}
			
		}else
			require_once 'page404.php';
	}else
		require_once 'page404.php';
}else
	require_once 'page404.php';

$jqAdd = <<<EoL

	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				upass: {
					required: true,
					rangelength: [3, 10],
					pattern: /^[\-a-zA-Z0-9_]+$/i
				}
			},
			messages: {
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

	$('div.hint').bt({
		width: '250px',
		trigger: 'click',
		positions: 'right',
		padding: '15px',
		clickAnywhereToClose: true,
		closeWhenOthersOpen: true,
		fill: '#fdf5e6',
		cornerRadius: 3,
		strokeStyle: '#aeaeae',
		cssStyles: {fontFamily: 'Arial, Helvetica, sans-serif', fontSize: '13px', lineHeight: '17px'},
		showTip: function(box) {
			$(box).fadeIn(150);
		},
		hideTip: function(box, callback) {
			$(box).animate({opacity: 0}, 150, callback);
		},
		hoverIntentOpts: {
			interval: 0,
			timeout: 0
		}
	});


EoL;

$action = $_SERVER['REQUEST_URI'];
$title = 'Сменить пароль';

require_once '../includs/change_password.html';

?>