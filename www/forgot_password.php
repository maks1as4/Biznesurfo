<?php

require_once '../includs/head.php';

$val_mail = '';
$err = '';

if (count($_POST)>0){
	if (!isset($_POST['btmes']))
		require_once 'page404.php';
	if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
		require_once 'page404.php';
	
	// Ёлектронна€ почта
	if ((isset($_POST['umail']) && ($_POST['umail']!=''))){
		$val_mail = $_POST['umail'];
		if (strlen($val_mail) > 100)
			$err .= ' - Email слишком длинный, ограничение 100 символов.<br />';
		if (!CheckEmail($val_mail))
			$err .= ' - Email имеет недопустимый формат.<br />';
		else{
			$email = mysql_real_escape_string($val_mail);
			$qRes = SqlQuery("Select `id` From `MEMBERS` Where `email`='".$email."' Limit 1");
			if (mysql_num_rows($qRes) !== 1)
				$err .= ' - ѕользовател€ с таким почтовым адресом не существует.<br />';
			@mysql_free_result($qRes);
		}
	}else
		$err .= ' - Ќе указан "Email".<br />';
	
	if ($err == ''){
		$val_hash = md5(microtime().generateCode(3));
		
		$query = "Insert Into `CHANGE_PASSWORD_tmp` Set `email`='".mysql_real_escape_string($val_mail)."', `hash`='".$val_hash."', `date_add`='".date("Y-m-d H:i:s")."';";
		SqlQuery($query);
		
		$_SESSION['change_password']['email'] = $val_mail;
		$_SESSION['change_password']['hash']  = $val_hash;
		
		header('Location: /forgot-password/send');
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
					email2: true,
					remote: {
						url: '/ajax/check_email_forgot.php'
					}
				}
			},
			messages: {
				umail: {
					required: 'введите email',
					email2: 'email имеет некорректный формат',
					remote: 'пользовател€ с таким почтовым адресом не существует'
				}
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


EoL;

$title = '«апрос смены парол€';

require_once '../includs/forgot_password.html';

?>