<?php

require_once "../../includs/head.php";

$check = false;
$name_empty = false;
$email_empty = false;
$email_incorrect = false;

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
	$id_product = $_REQUEST['product'];
	$id_client = $_REQUEST['supplier'];
	$hash = $_REQUEST['code'];
	$name = iconv('utf-8', 'cp1251', $_REQUEST['name']);
	$name = preg_replace('/[\s]{2,}/', ' ', trim($name));
	$email = trim($_REQUEST['email']);
	$phone = preg_replace('/[\s]{2,}/', ' ', trim($_REQUEST['phone']));
	$comment = iconv('utf-8', 'cp1251', $_REQUEST['comment']);
	if (CheckIntegerUnsign($id_product) && CheckIntegerUnsign($id_client) && preg_match('/^[a-z0-9]{32}$/', $hash)){
		if (md5($id_product.md5($id_client).$secret_salt) == $hash){
			$qRes = SqlQuery("
				Select M.`email`, S.`name`
				From `STR` S
					join `MEMBERS` M on S.`client` = M.`id_client`
				Where S.`id` = '".mysql_real_escape_string($id_product)."' and S.`client` = '".mysql_real_escape_string($id_client)."' and S.`active` = '1';
			");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$for_email = $Row[0];
				$product_name = $Row[1];
				$check = true;
			}
			@mysql_free_result($qRes);
		}
		if (isset($name) && $name!=''){
			if (!CheckStr(4, $name, 100, false))
				$name = substr($name, 0, 100);
		}else
			$name_empty = true;
		if (isset($email) && $email!=''){
			if (strlen($email) > 100)
				$email = substr($email, 0, 100);
			if (!CheckEmail($email))
				$email_incorrect = true;
		}else
			$email_empty = true;
		if (isset($phone) && $phone!=''){
			if (strlen($phone) > 100)
				$phone = substr($phone, 0, 100);
		}
		if (isset($comment) && $comment!=''){
			$comment = stripslashes(nl2br($comment));
			if (strlen($comment) > 5000)
				$comment = substr($comment, 0, 5000);
		}
		if ($check && !$name_empty && !$email_empty && !$email_incorrect){
			// формируем тело письма
			$body  = 'Товар: <strong>'.$product_name.'</strong><br><br>'.$comment.'<br><br>';
			$body .= 'Контактная информация заказчика:<br>Имя: '.$name.'<br>e-mail: '.$email;
			if (isset($phone) && $phone!='')
				$body .= '<br>Контактный телефон: '.$phone;
			// создаем запись письма в БД - маил
			SqlQuery("Insert Into `MAILS` Set `mailto` = '".mysql_real_escape_string($for_email)."', `title` = 'Заявка на покупку вашего товара с сайта - www.biznesurfo.ru', `body` = '".mysql_real_escape_string($body)."', `type` = '4', `hash` = '', `sent` = '0', `error` = '', `adate` = '".date('Y-m-d H:i:s')."';");
			// формируем уникальный хеш
			$id_last = lastInsertId();
			SqlQuery("Update `MAILS` Set `hash` = '".$id_last.'-'.md5($id_last.$for_email)."' Where `id` = '".$id_last."';");
			// запуск отбработчика писем
			exec_script('http://'.$baseHref.'/mailer.php', array('id_mail'=>$id_last));
		}
	}
}

$json_array = array('error100'=>$name_empty, 'error200'=>$email_empty, 'error300'=>$email_incorrect, 'check'=>$check);
echo json_encode($json_array);

?>