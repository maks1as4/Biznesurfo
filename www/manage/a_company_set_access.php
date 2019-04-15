<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['cid']) && !empty($_GET['cid']) && CheckIntegerUnsign($_GET['cid'])){
		
		$company_id = $_GET['cid'];
		
		// получаем данные компании
		$qRes = SqlQuery("
			Select
				RR.`id_client`, RR.`name`, RR.`phone`, RR.`fio`, RR.`email`,
				(Select `full_phone` From `CLIENT_PHONES` Where `id_client` = RR.`id_client` Order by `sort_order` Limit 1) as `phone`,
				(Select `rubric` From `CLIENT_ACTIVITIES` Where `client` = RR.`id_client` Order by `rubric` Limit 1) as `active`,
				(Select `about` From `CLIENTS_ABOUT` Where `id_client` = RR.`id_client`) as `about`,
				RR.`password`, RR.`add_solt`
			From `REGISTRATION_request` RR
			Where `id_client`='".mysql_real_escape_string($company_id)."' and `checked`='0';
		");
		if (mysql_num_rows($qRes) === 1){
			$Info = mysql_fetch_row($qRes);
		}else
			die('Неверный идентификатор компании');
		@mysql_free_result($qRes);
		
		// проверка телефона, если нет записи то создаем
		if ($Info[5] === null){
			$split_phone = slashPhone($Info[2]);
			SqlQuery("Insert Into `CLIENT_PHONES` Set `id_client`='".mysql_real_escape_string($company_id)."', `full_phone`='".$Info[2]."', `code`='".mysql_real_escape_string($split_phone[0])."', `phone`='".mysql_real_escape_string($split_phone[1])."', `sort_order`='1';");
		}
		
		// проверка деятельности
		if ($Info[6] === null){
			$qRes = SqlQuery("Select `rubric` From `STR` Where `client`='".mysql_real_escape_string($company_id)."' Order by `id` Limit 1;");
			if (mysql_num_rows($qRes) === 1){
				$id_rubric = mysql_fetch_row($qRes);
				SqlQuery("Insert Into `CLIENT_ACTIVITIES` Set `client`='".mysql_real_escape_string($company_id)."', `rubric`='".mysql_real_escape_string($id_rubric[0])."';");
				$end_rubrics = '1';
			}else
				$end_rubrics = '0';
			@mysql_free_result($qRes);
		}else
			$end_rubrics = '1';
		
		// проверка "О компании"
		if ($Info[7] === null){
			SqlQuery("Insert Into `CLIENTS_ABOUT` Set `id_client`='".mysql_real_escape_string($company_id)."', `about`='', `status`='0';");
		}
		
		// создаем запись в таблице MEMBERS
		SqlQuery("Insert Into `MEMBERS` Set `id_client`='".mysql_real_escape_string($company_id)."', `email`='".mysql_real_escape_string($Info[4])."', `password`='".mysql_real_escape_string($Info[8])."', `add_solt`='".mysql_real_escape_string($Info[9])."', `fio`='".mysql_real_escape_string($Info[3])."', `ip`='0', `hash`='', `role`='com', `notice`='0', `date_add`='".date("Y-m-d H:i:s")."';");
				
		// помечаем данные как проверенные
		SqlQuery("Update `REGISTRATION_request` Set `checked`='1' Where `id_client`='".mysql_real_escape_string($company_id)."';");
		
		/* --- отправка письма --- */
		
		require_once '../../extensions/PHPMailer/class.phpmailer.php';
		$message = file_get_contents('../mail_blank/access.html');
		$placeholders = array ('/\[=FIO\]/', '/\[=COMPANY\]/', '/\[=EMAIL\]/');
		$valible = array ($Info[3], $Info[1], $Info[4]);
		$message = preg_replace($placeholders, $valible, $message);
		
		$mail = new PHPMailer();

		$mail->CharSet  = 'windows-1251';
		$mail->Encoding = 'base64';

		$mail->IsSMTP();

		$mail->Host       = 'mail.biznesurfo.ru';
		$mail->SMTPDebug  = false;
		$mail->SMTPAuth   = true;
		$mail->Port       = 25;
		$mail->Username   = $mailBot;
		$mail->Password   = $mailBotPass;
		
		$mail->SetFrom($mailBot, 'Индустрия бизнеса');
		if ($sendForUser)
			$mail->AddAddress($Info[4]);
		else
			$mail->AddAddress('slash@biznesurfo.ru');
		$mail->Subject = 'Индустрия бизнеса - получение доступа - www.biznesurfo.ru';
		$mail->MsgHTML($message);
		$mail->Send();
		
		$mail->ClearAddresses();
		$mail->ClearAttachments();
		
		/* --- /отправка письма --- */
		
		header('Location: /management/adm/company-get-access');
		
	}else
		die('Неверный идентификатор компании');
	
}else
	header('Location: /enter');

?>