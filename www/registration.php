<?php

require_once '../includs/head.php';

$val_opf = '';
$val_organisation = '';
$val_code = '';
$val_phone = '';
$val_fio = '';
$val_mail = '';
$val_pass = '';
$err = '';

if (count($_POST)>0){
	if (!isset($_POST['btmes']))
		require_once 'page404.php';
	if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
		require_once 'page404.php';

	// ОПФ
	if ((isset($_POST['uopf']) && ($_POST['uopf']!=''))){
		$val_opf = $_POST['uopf'];
		if (!CheckStr(4, $val_opf, 20, false))
			$err .= ' - Слишком длинное наименование ОПФ (ограничение 20 символов).<br />';
		if (!preg_match("/^[a-zа-яё \-]+$/i", $val_opf))
			$err .= ' - ОПФ может состоять только из букв.<br />';
	}else
		$val_opf = '';

	// Название организации
	if ((isset($_POST['uorganisation']) && ($_POST['uorganisation']!=''))){
		//$val_organisation = $_POST['uorganisation'];
		$val_organisation = stripslashes($_POST['uorganisation']); // -gpc-
		if (!CheckStr(4, $val_organisation, 220, false))
			$err .= ' - Слишком длинное название организации (ограничение 220 символов).<br />';
		if (!preg_match("/^[a-zа-яё0-9 \-()«»\"]+$/i", $val_organisation))
			$err .= ' - Название организации может состоять только из букв, цифр, пробела, тире.<br />';
	}else
		$err .= ' - Не указано "Название организации".<br />';

	// Телефон компании - код
	if (isset($_POST['ucode']) && ($_POST['ucode']!='')){
		$val_code = $_POST['ucode'];
		if (!CheckStr(4, $val_code, 5, false))
			$err .= ' - Поле "Код города" превышает допустимую длину (ограничение 5 символов).<br />';
		if (!CheckIntegerUnsign($val_code))
			$err .= ' - В поле "Код города" допускается только ввод цифр (без разделителей).<br />';
	}else
		$err .= ' - Не заполнено поле "Код города".<br />';

	// Телефон компании - телефон
	if (isset($_POST['uphone']) && ($_POST['uphone']!='')){
		$val_phone = $_POST['uphone'];
		if (!CheckStr(4, $val_phone, 7, false))
			$err .= ' - Поле "Телефон" превышает допустимую длину (ограничение 7 символов).<br />';
		if (!CheckIntegerUnsign($val_phone))
			$err .= ' - В поле "Телефон" допускается только ввод цифр (без разделителей).<br />';
	}else
		$err .= ' - Не заполнено поле "Телефон".<br />';

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

	// Ваше ФИО
	if ((isset($_POST['ufio']) && ($_POST['ufio']!=''))){
		$val_fio = $_POST['ufio'];
		if (!CheckStr(4, $val_fio, 120, false))
			$val_fio = substr($val_fio, 0, 120);
		if (!preg_match("/^[a-zа-яё0-9 ()\-«»]+$/i", $val_fio))
			$err .= ' - ФИО состоять только из букв, цифр, пробела, тире.<br />';
	}else
		$err .= ' - Не указано "Фамилия, Имя, Отчество".<br />';

	// Электронная почта
	if ((isset($_POST['umail']) && ($_POST['umail']!=''))){
		$val_mail = $_POST['umail'];
		if (strlen($val_mail) > 100)
			$err .= ' - Email слишком длинный, ограничение 100 символов.<br />';
		if (!CheckEmail($val_mail))
			$err .= ' - Email имеет недопустимый формат.<br />';
		else{
			$email = mysql_real_escape_string($val_mail);
			$qRes1 = SqlQuery("Select `id` From `REGISTRATION_tmp` Where `email`='".$email."' Limit 1;");
			$qRes2 = SqlQuery("Select `id` From `MEMBERS` Where `email`='".$email."' Limit 1;");
			if ((mysql_num_rows($qRes1)===1) || (mysql_num_rows($qRes2)===1))
				$err .= ' - Такой Email уже занят, пожалуйста, введите другой.<br />';
			@mysql_free_result($qRes1);
			@mysql_free_result($qRes2);
		}
	}else
		$err .= ' - Не указан "Email".<br />';

	// Пароль
	if ((isset($_POST['upass']) && ($_POST['upass']!=''))){
		$val_pass = $_POST['upass'];
		if ((strlen($val_pass)<3) || (strlen($val_pass)>10))
			$err .= ' - Пароль должен быть от 3 до 10 символов.<br />';
		if (!preg_match("/^[a-z0-9_\-]+$/i", $val_pass))
			$err .= ' - Пароль может состоять только из букв английского алфавита, цифр, знака подчеркивания, тире.<br />';
	}else
		$err .= ' - Не указан "Пароль".<br />';

	// Проверка пользовательского соглашения
	if ((!isset($_POST['uagree'])) || ($_POST['uagree'] == ''))
		$err .= ' - Необходимо подтвердить согласие.<br />';

	if ($err == ''){
		$salt = generateCode(3);
		$val_pass = md5(md5($val_pass).$salt);
		$val_hash = md5(microtime().generateCode(3));
		
		$full_name  = ($val_opf != '') ? $val_organisation.','.$val_opf : $val_organisation;
		$full_phone = getPhoneFormat($val_code, $val_phone);
		
		SqlQuery("
			Insert Into `REGISTRATION_tmp` Set
				`opf`='".mysql_real_escape_string(mb_strtoupper($val_opf, 'cp1251'))."',
				`organisation`='".mysql_real_escape_string(ucfirst($val_organisation))."',
				`full_phone`='".mysql_real_escape_string($full_phone)."',
				`code`='".mysql_real_escape_string($val_code)."',
				`phone`='".mysql_real_escape_string($val_phone)."',
				`fio`='".mysql_real_escape_string($val_fio)."',
				`email`='".mysql_real_escape_string($val_mail)."',
				`password`='".$val_pass."',
				`add_solt`='".$salt."',
				`hash`='".$val_hash."',
				`date_add`='".date("Y-m-d H:i:s")."';
		");
		
		// вычисляем последний добавленный id автоинкрементом
		$id_last = lastInsertId();
		
		// проверяем компанию, на присутствие в БД, если да - то предложить 5 вариантов
		$qRes = SqlQuery("
			Select C.`id`, C.`name`, C.`address`, CP.`full_phone` as phone
			From `CLIENTS` C
				Left join `CLIENT_PHONES` CP on C.`id`=CP.`id_client`     
				Left join `MEMBERS` M on C.`id`=M.`id_client`     
			Where (C.`name` = '".mysql_real_escape_string($full_name)."' or CP.`full_phone` = '".mysql_real_escape_string($full_phone)."')
			and isnull(M.`id`)
			Group by C.`id`
			Limit 5;
		");
		
		if (mysql_num_rows($qRes) > 0){ // найдены похожие компании
			$_SESSION['registration']['tmp_id'] = $id_last;
			$path = '/add-exists';
		}else{ // совпадений не найдено
			$_SESSION['registration']['email'] = $val_mail;
			$_SESSION['registration']['fio']   = $val_fio;
			$_SESSION['registration']['hash']  = $val_hash;
			$path = '/add-company/thanks';
		}
		@mysql_free_result($qRes);
		unset ($id_last);
		
		header('Location: '.$path);
	}else{
		$val_opf   = htmlspecialchars($val_opf, ENT_QUOTES, 'cp1251');
		$val_organisation = htmlspecialchars($val_organisation, ENT_QUOTES, 'cp1251');
		$val_code  = htmlspecialchars($val_code, ENT_QUOTES, 'cp1251');
		$val_phone = htmlspecialchars($val_phone, ENT_QUOTES, 'cp1251');
		$val_fio   = htmlspecialchars($val_fio, ENT_QUOTES, 'cp1251');
		$val_mail  = htmlspecialchars($val_mail, ENT_QUOTES, 'cp1251');
		$val_pass  = htmlspecialchars($val_pass, ENT_QUOTES, 'cp1251');
	}
	
}

$jqAdd = <<<EoL

	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				uopf: {
					pattern: /^[a-zа-яё \-]+$/i
				},
				uorganisation: {
					required: true,
					pattern: /^[a-zа-яё0-9 \-()«»\"]+$/i
				},
				ucode: {
					required: true,
					integer: true,
					rangelength: [3, 5]
				},
				uphone: {
					required: true,
					integer: true,
					rangelength: [5, 7]
				},
				ufio: {
					required: true,
					pattern: /^[a-zа-яё0-9 ()\-«»]+$/i
				},
				umail: {
					required: true,
					email2: true,
					remote: {
						url: '/ajax/check_email_reg.php'
					}
				},
				upass: {
					required: true,
					rangelength: [3, 10],
					pattern: /^[a-z0-9_\-]+$/i
				},
				uagree: 'required'
			},
			messages: {
				uopf: {
					pattern: 'только буквы'
				},
				uorganisation: {
					required: 'введите название организации',
					pattern: 'только буквы, цифры, пробел, тире, кавычки'
				},
				ucode: {
					required: 'код города или сотового оператора не должен быть пустым',
					integer: 'код города или сотового оператора может состоять только из цифр',
					rangelength: 'код города или сотового оператора должен быть в диапазоне от 3 до 5 цифр'
				},
				uphone: {
					required: 'номер телефона не должен быть пустым',
					integer: 'номер телефона может состоять только из цифр (без знаков разделения)',
					rangelength: 'номер телефона должен быть в диапазоне от 5 до 7 цифр'
				},
				ufio: {
					required: 'введите Ф.И.О контактного лица',
					pattern: 'только буквы, цифры, пробел, тире'
				},
				umail: {
					required: 'введите email',
					email2: 'email имеет некорректный формат',
					remote: 'такой email уже занят, пожалуйста, введите другой'
				},
				upass: {
					required: 'введите пароль',
					rangelength: 'пароль должен быть от 3 до 10 символов',
					pattern: 'только буквы английского алфавита, цифры, знак подчеркивания, тире'
				},
				uagree: 'необходимо подтвердить согласие'
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			},
			submitHandler: function() {
				$('#submit').attr('disabled', 'disabled').removeClass('orange').addClass('disable');
				$('#loading').show();
				form.submit();
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

$title = 'Добавить компанию, организацию';

require_once '../includs/registration.html';

?>