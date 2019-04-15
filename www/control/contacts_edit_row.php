<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	if (isset($_GET['type']) && !empty($_GET['type'])){
		
		if (isset($_GET['idrow']) && !empty($_GET['idrow']) && CheckIntegerUnsign($_GET['idrow']))
			$id_row = $_GET['idrow'];
		else
			require_once 'page404.php';
		
		$type = $_GET['type'];
		switch ($type){
			case 'phone':{
				$qRes = SqlQuery("Select `code`, `phone` From `CLIENT_PHONES` Where `id`='".mysql_real_escape_string($id_row)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
				if (mysql_num_rows($qRes) === 1){
					$Row = mysql_fetch_row($qRes);
					$val_code = $Row[0];
					$val_phone = $Row[1];
				}else
					require_once 'page404.php';
				@mysql_free_result($qRes);
				$title_suffix = 'телефон';
				break;
			}
			case 'email':{
				$qRes = SqlQuery("Select `email` From `CLIENT_EMAILS` Where `id`='".mysql_real_escape_string($id_row)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
				if (mysql_num_rows($qRes) === 1){
					$Row = mysql_fetch_row($qRes);
					$val_mail = $Row[0];
				}else
					require_once 'page404.php';
				@mysql_free_result($qRes);
				$title_suffix = 'электронную почту';
				break;
			}
			case 'site':{
				$qRes = SqlQuery("Select `site` From `CLIENT_SITES` Where `id`='".mysql_real_escape_string($id_row)."' and `id_client`='".mysql_real_escape_string($user_client_id)."';");
				if (mysql_num_rows($qRes) === 1){
					$Row = mysql_fetch_row($qRes);
					$val_site = $Row[0];
				}else
					require_once 'page404.php';
				@mysql_free_result($qRes);
				$title_suffix = 'сайт';
				break;
			}
			default:{
				require_once 'page404.php';
			}
		}
		
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			switch ($type){
				case 'phone':{
#---------------- Телефон -------------------

					// Код
					if (isset($_POST['u1code']) && ($_POST['u1code']!='')){
						$val_code = $_POST['u1code'];
						if (!CheckStr(4, $val_code, 5, false))
							$err .= ' - Поле "Код города" превышает допустимую длину (ограничение 5 символов).<br />';
						if (!CheckIntegerUnsign($val_code))
							$err .= ' - В поле "Код города" допускается только ввод цифр (без разделителей).<br />';
					}else
						$err .= ' - Не заполнено поле "Код города".<br />';
					
					// Номер
					if (isset($_POST['u1phone']) && ($_POST['u1phone']!='')){
						$val_phone = $_POST['u1phone'];
						if (!CheckStr(4, $val_phone, 7, false))
							$err .= ' - Поле "Телефон" превышает допустимую длину (ограничение 7 символов).<br />';
						if (!CheckIntegerUnsign($val_phone))
							$err .= ' - В поле "Телефон" допускается только ввод цифр (без разделителей).<br />';
					}else
						$err .= ' - Не заполнено поле "Телефон".<br />';
					
					// Код + номер не введены
					if ((isset($_POST['u1code']) && ($_POST['u1code']=='')) && (isset($_POST['u1phone'])&&($_POST['u1phone']=='')))
						$err = ' - Введите телефон.<br />';
					
					// Проверка колличества цифр в телефоне (код + номер), 10 для обычного и сотового, 11 для бесплатного 8-800
					if ($err == ''){
						$cp_length = strlen($val_code.$val_phone);
						if ($val_code == '8800'){
							if ($cp_length != 11)
								$err .= ' - Неправильное количество цифр в телефоне, для бесплатного номера 8-800... необходимо 11 цифр (код + номер).<br />';
						}else{
							if ($cp_length != 10)
								$err .= ' - Неправильное количество цифр в телефоне, необходимо 10 цифр (код + номер).<br />';
						}
					}
					
					if ($err == ''){
						// обновляем данные в БД
						SqlQuery("Update `CLIENT_PHONES` Set `full_phone`='".mysql_real_escape_string(getPhoneFormat($val_code, $val_phone))."', `code`='".mysql_real_escape_string($val_code)."', `phone`='".mysql_real_escape_string($val_phone)."' Where `id`='".mysql_real_escape_string($id_row)."';");
						// переход на страницу управления данными пользователя
						header('Location: /kabinet/contacts-change');
					}else{
						$val_code  = htmlspecialchars($val_code, ENT_QUOTES, 'cp1251');
						$val_phone = htmlspecialchars($val_phone, ENT_QUOTES, 'cp1251');
					}

#--------------------------------------------
					break;
				}
				case 'email':{
#----------------- Email --------------------

					// Электронная почта
					if (isset($_POST['umail']) && ($_POST['umail']!='')){
						$val_mail = $_POST['umail'];
						if (strlen($val_mail) > 100)
							$err .= ' - Email слишком длинный, ограничение 100 символов.<br />';
						if (!CheckEmail($val_mail))
							$err .= ' - E-mail имеет недопустимый формат.<br />';
					}else
						$err .= ' - Введите адрес электронной почты.<br />';
					
					if ($err == ''){
						// обновляем данные в БД
						SqlQuery("Update `CLIENT_EMAILS` Set `email`='".mysql_real_escape_string($val_mail)."' Where `id`='".mysql_real_escape_string($id_row)."';");
						// переход на страницу управления данными пользователя
						header('Location: /kabinet/contacts-change');
					}else{
						$val_mail = htmlspecialchars($val_mail, ENT_QUOTES, 'cp1251');
					}

#--------------------------------------------
					break;
				}
				case 'site':{
#------------------ Сайт --------------------

					// Сайт
					if (isset($_POST['usite']) && ($_POST['usite']!='')){
						$val_site = $_POST['usite'];
						if (strlen($val_site) > 100)
							$err .= ' - Сайт слишком длинный, ограничение 100 символов.<br />';
						if (!CheckSite($val_site))
							$err .= ' - Сайт имеет недопустимый формат.<br />';
					}else
						$err .= ' - Введите адрес сайта.<br />';
					
					if ($err == ''){
						// убираем http(s)
						$val_site = preg_replace("#^https?://#i", '', ltrim($val_site));
						// обновляем данные в БД
						SqlQuery("Update `CLIENT_SITES` Set `site`='".mysql_real_escape_string($val_site)."' Where `id`='".mysql_real_escape_string($id_row)."';");
						// переход на страницу управления данными пользователя
						header('Location: /kabinet/contacts-change');
					}else{
						$val_site = htmlspecialchars($val_site, ENT_QUOTES, 'cp1251');
					}

#--------------------------------------------
					break;
				}
			}
		}
		
	}else
		require_once 'page404.php';

}else
	header('Location: /enter?link=/contacts-change');

$jFiles = '<script type="text/javascript" src="/js/validate/jquery.validate.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/additional-methods.min.js"></script>'."\n";
switch ($type){
	case 'phone':{
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				u1code: {
					required: true,
					integer: true,
					rangelength: [3, 5]
				},
				u1phone: {
					required: true,
					integer: true,
					rangelength: [5, 7]
				}
			},
			messages: {
				u1code: {
					required: 'код города или сотового оператора не должен быть пустым',
					integer: 'код города или сотового оператора может состоять только из цифр',
					rangelength: 'код города или сотового оператора должен быть в диапазоне от 3 до 5 цифр'
				},
				u1phone: {
					required: 'номер телефона не должен быть пустым',
					integer: 'номер телефона может состоять только из цифр (без знаков разделения)',
					rangelength: 'номер телефона должен быть в диапазоне от 5 до 7 цифр'
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
		break;
	}
	case 'email':{
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				umail: {
					required: true,
					email2: true
				}
			},
			messages: {
				umail: {
					required: 'введите email',
					email2: 'email имеет некорректный формат'
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
		break;
	}
	case 'site':{
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				usite: {
					required: true,
					url2: true
				}
			},
			messages: {
				usite: {
					required: 'введите сайт',
					url2: 'название сайта имеет некорректный формат'
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
		break;
	}
}

$active_tab = 1;
$title = 'Личный кабинет - изменить '.$title_suffix;

require_once '../../includs/control/contacts_edit_'.$type.'.html';

?>