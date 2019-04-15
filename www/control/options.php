<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	// загружаем опцию уведомления
	$qRes = SqlQuery("Select `notice` From `MEMBERS` Where `id`='".mysql_real_escape_string($user_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		$notice = $Row[0];
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	// проверяем есть ли домен 3го уровня
	$qRes = SqlQuery("Select `translit` From `CLIENTS` Where `id`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		$url3_exists = ($Row[0] === NULL) ? false : true;
		if ($url3_exists) $url3 = $Row[0];
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	$val_notice = '0';
	$val_url3 = '';
	$err = '';
	
	// показать уведомления
	$show_address_success = $show_notice_succes = false;
	
	if (isset($_SESSION['opt_address']) && ($_SESSION['opt_address'])){
		$show_address_success = true;
		unset ($_SESSION['opt_address']);
	}
	
	if (isset($_SESSION['opt_notice']) && ($_SESSION['opt_notice'])){
		$show_notice_succes = true;
		unset ($_SESSION['opt_notice']);
	}
	
	if (count($_POST)>0){
		if (!isset($_POST['btmes']))
			require_once 'page404.php';
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		if (isset($_POST['action'])){
		
			switch ($_POST['action']){
				case 'address':{
					if (!$url3_exists){
						// домен 3го уровня
						
						$exception_words = array('www', 'mail', 'my-sql', 'mysql', 'mysqli', 'php', 'pdo', 'admin', 'administrator', 'moder', 'moderator', 'root', 'test', 'biznesurfo');
						
						if ((isset($_POST['url3']) && ($_POST['url3']!=''))){
							$val_url3 = $_POST['url3'];
														
							$qRes1 = SqlQuery("Select `id` From `CLIENTS` Where `translit`='".mysql_real_escape_string($val_url3)."' Limit 1;");
							$qRes2 = SqlQuery("Select `id` From `RC_TRANSLIT` Where `translit`='".mysql_real_escape_string($val_url3)."' Limit 1;");
							if ((mysql_num_rows($qRes1)===1) || (mysql_num_rows($qRes2)===1))
								$err .= ' - Такой адрес уже занят, пожалуйста, введите другой.<br />';
							elseif (in_array($val_url3, $exception_words))
								$err .= ' - Такой адрес уже занят, пожалуйста, введите другой.<br />';
							@mysql_free_result($qRes1);
							@mysql_free_result($qRes2);
							
							if ($err == '') {
								if ((strlen($val_url3)<2) || (strlen($val_url3)>20))
									$err .= ' - Адрес должен быть от 2 до 20 символов.<br />';
								if (!preg_match("/^[a-z0-9\-]+$/", $val_url3)){
									$err .= ' - Адрес может состоять только из строчных букв английского алфавита, цифр, знака тире (-).<br />';
								}elseif (!preg_match("/^[a-z0-9][a-z0-9\-]+[a-z0-9]$/", $val_url3)){
									$err .= ' - Адрес не может начинаться или заканчиваться знаком тире (-).<br />';
								}elseif (preg_match("/-{2,}/", $val_url3))
									$err .= ' - Нельзя вводить подряд более 1го символа тире (-).<br />';
							}
						}else
							$err .= ' - Адрес не может быть пустым.<br />';
						
						if ($err == ''){
							// сохраняем домен 3го уровня
							SqlQuery("Update `CLIENTS` Set `translit`='".mysql_real_escape_string($val_url3)."' Where `id`='".mysql_real_escape_string($user_client_id)."';");
							// логируем изменение домена
							SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='6', `action`='0', `old`='-', `new`='".mysql_real_escape_string($val_url3)."', `adate`='".date('Y-m-d H:i:s')."';");
							$_SESSION['opt_address'] = true;
							header('Location: /kabinet/options');
						}
						
					}else
						require_once 'page404.php';
					break;
				}
				case 'notice':{
					if (isset($_POST['unotice']) && $_POST['unotice'] == 'c')
						$val_notice = '1';
					
					if ($notice != $val_notice) $_SESSION['opt_notice'] = true;
					
					// обновляем опцию рассылки
					SqlQuery("Update `MEMBERS` Set `notice`='".mysql_real_escape_string($val_notice)."' Where `id`='".mysql_real_escape_string($user_id)."';");
					header('Location: /kabinet/options');
					break;
				}
				default:
					require_once 'page404.php';
			}
			
		}else
			require_once 'page404.php';
	}

}else
	header('Location: /enter?link=/options');

$jFiles = '<script type="text/javascript" src="/js/validate/jquery.validate.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/additional-methods.min.js"></script>'."\n";
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#address').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				url3: {
					required: true,
					pattern: /^[a-z0-9\-]+$/,
					minlength: 2,
					maxlength: 20,
					remote: {
						url: '/ajax/check_url3.php'
					}
				}
			},
			messages: {
				url3: {
					required: 'адрес не должен быть пустым',
					pattern: 'только строчные буквы английского алфавита, цифры, знак тире (-)',
					minlength: 'не менее 2 символов',
					maxlength: 'не более 20 символов',
					remote: 'такой адрес уже занят, пожалуйста, введите другой'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});
});

EoL;

$active_tab = 3;
$title = 'Личный кабинет - настройки';

require_once '../../includs/control/options.html';

?>