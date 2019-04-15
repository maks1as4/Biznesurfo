<?php

require_once "../../includs/head.php";

$email_err = false;
$password_err = false;
$ok = false;
$id_client = '';
$link = '';

$email = (isset($_POST['email'])) ? $_POST['email'] : '';
$password = (isset($_POST['pass'])) ? $_POST['pass'] : '';

$query = "Select `add_solt` From `MEMBERS` Where `email`='".mysql_real_escape_string($email)."' Limit 1";
$qRes = SqlQuery($query);

if (mysql_num_rows($qRes) === 1 && $email != ''){
	
	$Row = mysql_fetch_row($qRes);
	$password = md5($password.$Row[0]);
	@mysql_free_result($qRes);
	
	$query = "Select `id`, `id_client`, `email`, `role` From `MEMBERS` Where `email`='".mysql_real_escape_string($email)."' and `password`='".$password."' Limit 1;"; // проверка пароля
	$qRes = SqlQuery($query);
	
	if (mysql_num_rows($qRes) === 1 && $password != ''){
		
		$Row = mysql_fetch_row($qRes);
		$id = $Row[0];
		$id_client = $Row[1];
		
		/*switch ($Row[3]){
			case 'adm':{
				$link = '/management/adm';
				break;
			}
			default:
				$link = '/kabinet';
		}*/
		
		// заносим данные в сессию
		$_SESSION['user']['id']    = $id;
		$_SESSION['user']['id_client'] = $id_client;
		$_SESSION['user']['email'] = $Row[2];
		$_SESSION['user']['role']  = $Row[3];
		$_SESSION['user']['ip']    = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user']['browser'] = getBrowser();
		
		if ($_POST['remembeMe'] == 'checked'){ // заносим данные в куки если это необходимо
			$hash = md5(generateCode(10));
			SqlQuery("Update `MEMBERS` Set `ip`=Inet_aton('".$_SERVER['REMOTE_ADDR']."'), `hash`='".$hash."' Where `id`='".mysql_real_escape_string($id)."';");
			setcookie('u_id', $id, time()+3600*24*$cookie_days, '/', $cookieHost);
			setcookie('u_key', $hash, time()+3600*24*$cookie_days, '/', $cookieHost);
		}else
			SqlQuery("Update `MEMBERS` Set `ip`='0', `hash`='' Where `id`='".mysql_real_escape_string($id)."';");
		
		$ok = true;
	}else
		$password_err = true;
	
	@mysql_free_result($qRes);
	
}else
	$email_err = true;

$json_array = array('err100'=>$email_err, 'err200'=>$password_err, 'ok'=>$ok, 'goLink'=>$link);
echo json_encode($json_array);

?>