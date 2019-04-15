<?php

require_once '../includs/head.php';

if (isset($_POST['id_mail']) && CheckIntegerUnsign($_POST['id_mail']) && $_SERVER["REMOTE_ADDR"] == $serverIP){
	$check = false;
	$qRes = SqlQuery("Select `mailto`, `title`, `body`, `hash`, `imgs` From `MAILS` Where `id` = '".mysql_real_escape_string($_POST['id_mail'])."' and `sent` = '0';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		$mailto = $Row[0];
		$title = $Row[1];
		$message = $Row[2];
		$hash = $Row[3];
		$imgs = $Row[4];
		$check = true;
	}
	@mysql_free_result($qRes);
	if ($check){
		require_once '../extensions/PHPMailer/class.phpmailer.php';
		$mail = new PHPMailer();
		$mail->CharSet  = 'windows-1251';
		$mail->Encoding = 'base64';
		
		$mail->IsSMTP();
		$mail->Host      = 'mail.biznesurfo.ru';
		$mail->SMTPDebug = false;
		$mail->SMTPAuth  = true;
		$mail->Port      = 25;
		$mail->Username  = $mailBot;
		$mail->Password  = $mailBotPass;
		
		$mail->addCustomHeader('X-Code: '.$hash);
		$mail->SetFrom($mailBot, 'Индустрия бизнеса');
		if ($sendForUser)
			$mail->AddAddress($mailto);
		else
			$mail->AddAddress('slash@biznesurfo.ru');
		$mail->Subject = $title;
		if ($imgs != ''){
			// прикрепляем аттачи
		}
		$mail->MsgHTML($message);
		
		if ($mail->Send())
			SqlQuery("Update `MAILS` Set `sent` = '1' Where `id` = '".mysql_real_escape_string($_POST['id_mail'])."';");
		else
			SqlQuery("Update `MAILS` Set `error` = '".$mail->ErrorInfo."' Where `id` = '".mysql_real_escape_string($_POST['id_mail'])."';");
		
		$mail->ClearAddresses();
		if ($imgs != '') $mail->ClearAttachments();
	}
}

?>