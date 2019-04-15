<?php

require_once '../includs/head.php';

if (!isset($_SESSION['registration']['tmp_id']) || ($_SESSION['registration']['tmp_id'] == ''))
	require_once 'page404.php';

if (!isset($_GET['action']) || empty($_GET['action']))
	require_once 'page404.php';

$lust_id_tmp = $_SESSION['registration']['tmp_id'];
unset ($_SESSION['registration']);

$qRes = SqlQuery("Select `opf`, `organisation`, `full_phone`, `fio`, `email`, `password`, `add_solt`, `hash` From `REGISTRATION_tmp` Where `id`='".mysql_real_escape_string($lust_id_tmp)."';");
if (mysql_num_rows($qRes) === 1){
	$company_tmp = mysql_fetch_row($qRes);
}else
	require_once 'page404.php';
@mysql_free_result($qRes);

switch ($_GET['action']){
	case 'access':{
#---------------access---------------

if (!isset($_GET['client']) || empty($_GET['client']))
	require_once 'page404.php';

// переносим данные в таблицу заявок
$full_name = ($company_tmp[0] != '') ? $company_tmp[1].','.$company_tmp[0] : $company_tmp[1];
SqlQuery("Insert Into `REGISTRATION_request` Set `id_client`='".mysql_real_escape_string($_GET['client'])."', `name`='".mysql_real_escape_string($full_name)."', `phone`='".mysql_real_escape_string($company_tmp[2])."', `fio`='".mysql_real_escape_string($company_tmp[3])."', `email`='".mysql_real_escape_string($company_tmp[4])."', `password`='".mysql_real_escape_string($company_tmp[5])."', `add_solt`='".mysql_real_escape_string($company_tmp[6])."', `checked`='0', `adate`='".date("Y-m-d H:i:s")."';");

// удаляем временные данные
SqlQuery("Delete From `REGISTRATION_tmp` Where `id`='".mysql_real_escape_string($lust_id_tmp)."';");

$_SESSION['registration']['email_req'] = $company_tmp[4];

// переходим на страницу подтверждения заявки на получение доступа
header('Location: /access-confirm');

#------------------------------------
		break;
	}
	case 'continue':{
#--------------continue--------------

$_SESSION['registration']['email'] = $company_tmp[4];
$_SESSION['registration']['fio']   = $company_tmp[3];
$_SESSION['registration']['hash']  = $company_tmp[7];
header('Location: /add-company/thanks');

#------------------------------------
		break;
	}
	default:
		require_once 'page404.php';
}

?>