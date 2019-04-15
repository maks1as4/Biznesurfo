<?php

require_once "../../includs/head.php";

$check = false;
$email_empty = false;
$email_incorrect = false;
$message_empty = false;

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
	$id_client = $_REQUEST['destination'];
	$email = trim($_REQUEST['email']);
	$name = iconv('utf-8', 'cp1251', $_REQUEST['name']);
	$name = preg_replace('/[\s]{2,}/', ' ', trim($name));
	$message = iconv('utf-8', 'cp1251', $_REQUEST['message']);
	if (CheckIntegerUnsign($id_client)){
		$qRes = SqlQuery("Select `email` From `MEMBERS` Where `id_client` = '".mysql_real_escape_string($id_client)."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$for_email = $Row[0];
			$check = true;
		}
		@mysql_free_result($qRes);
		if (isset($email) && $email!=''){
			if (strlen($email) > 100)
				$email = substr($email, 0, 100);
			if (!CheckEmail($email))
				$email_incorrect = true;
		}else
			$email_empty = true;
		if (isset($name) && $name!=''){
			if (strlen($name) > 100)
				$name = substr($name, 0, 100);
		}
		if (isset($message) && $message!=''){
			$message = stripslashes(nl2br($message));
			if (strlen($message) > 5000)
				$message = substr($message, 0, 5000);
		}else
			$message_empty = true;
		if ($check && !$email_empty && !$email_incorrect && !$message_empty){
			// формируем тело письма
			$body  = $message.'<br><br>';
			$body .= 'Контактная информация отправителя:';
			if (isset($name) && $name!='')
				$body .= '<br>Имя: '.$name;
			$body .= '<br>e-mail: '.$email;
			// создаем запись письма в БД - маил
			SqlQuery("Insert Into `MAILS` Set `mailto` = '".mysql_real_escape_string($for_email)."', `title` = 'Сообщение с вашей карточки компании на сайте - www.biznesurfo.ru', `body` = '".mysql_real_escape_string($body)."', `type` = '5', `hash` = '', `sent` = '0', `error` = '', `adate` = '".date('Y-m-d H:i:s')."';");
			// формируем уникальный хеш
			$id_last = lastInsertId();
			SqlQuery("Update `MAILS` Set `hash` = '".$id_last.'-'.md5($id_last.$for_email)."' Where `id` = '".$id_last."';");
			// запуск отбработчика писем
			exec_script('http://'.$baseHref.'/mailer.php', array('id_mail'=>$id_last));
		}
	}
}

$json_array = array('error100'=>$email_empty, 'error200'=>$email_incorrect, 'error300'=>$message_empty, 'check'=>$check);
echo json_encode($json_array);

?>