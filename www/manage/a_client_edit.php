<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['cid']) && !empty($_GET['cid']) && CheckIntegerUnsign($_GET['cid'])){
		
		$client_id = $_GET['cid'];
		
		$Client = array();
		$qRes = SqlQuery("
			Select C.`id`, C.`name`, C.`index`, C.`street`, C.`house`, C.`office`, C.`aya`, C.`address`, CA.`about`
			From `CLIENTS` C
				left join `CLIENTS_ABOUT` CA on C.`id`=CA.`id_client`
			Where `id`='".mysql_real_escape_string($client_id)."';
		");
		if (mysql_num_rows($qRes) === 1)
			$Client = mysql_fetch_row($qRes);
		else
			die('Неверный идентификатор клиента');
		@mysql_free_result($qRes);
		
		$val_name    = $Client[1];
		$val_index   = $Client[2];
		$val_street  = $Client[3];
		$val_house   = $Client[4];
		$val_office  = $Client[5];
		$val_aya     = $Client[6];
		$val_address = $Client[7];
		$val_about   = $Client[8];
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			if (isset($_POST['uname']) && ($_POST['uname']!='')){
				//$val_name = $_POST['uname'];
				$val_name = stripslashes($_POST['uname']); // -gpc-
			}else
				$err .= ' - Не заполнено поле "наименование".<br />';
			
			if (isset($_POST['uindex']) && ($_POST['uindex']!='')){
				$val_index = $_POST['uindex'];
			}else
				$val_index = '';
			
			if (isset($_POST['ustreet']) && ($_POST['ustreet']!='')){
				$val_street = $_POST['ustreet'];
			}else
				$err .= ' - Не заполнено поле "улица".<br />';
			
			if (isset($_POST['uhouse']) && ($_POST['uhouse']!='')){
				$val_house = $_POST['uhouse'];
			}else
				$val_house = '';
			
			if (isset($_POST['uoffice']) && ($_POST['uoffice']!='')){
				$val_office = $_POST['uoffice'];
			}else
				$val_office = '';
			
			if (isset($_POST['uaya']) && ($_POST['uaya']!='')){
				$val_aya = $_POST['uaya'];
			}else
				$val_aya = '';
			
			if (isset($_POST['uaddress']) && ($_POST['uaddress']!='')){
				$val_address = $_POST['uaddress'];
			}else
				$err .= ' - Не заполнено поле "адрес".<br />';
			
			if (isset($_POST['uabout']) && ($_POST['uabout']!='')){
				//$val_about = $_POST['uabout'];
				$val_about = stripslashes($_POST['uabout']); // -gpc-
			}else
				$val_about = '';
			
			if ($err == ''){
				// изменяем данные таблицы клиентов
				SqlQuery("Update `CLIENTS` Set `name`='".mysql_real_escape_string($val_name)."', `index`='".mysql_real_escape_string($val_index)."', `street`='".mysql_real_escape_string($val_street)."', `house`='".mysql_real_escape_string($val_house)."', `office`='".mysql_real_escape_string($val_office)."', `aya`='".mysql_real_escape_string($val_aya)."', `address`='".mysql_real_escape_string($val_address)."' Where `id`='".mysql_real_escape_string($client_id)."';");
				// изменяем данные о компании, если нужно
				if ($val_about != ''){
					SqlQuery("Update `CLIENTS_ABOUT` Set `about`='".mysql_real_escape_string($val_about)."' Where `id_client`='".mysql_real_escape_string($client_id)."';");
				}
				// переходим на страницу проверки компаний
				header('Location: /management/adm/clients-check');
			}else{
				$val_coords = htmlspecialchars($val_coords, ENT_QUOTES, 'cp1251');
				$val_zoom = htmlspecialchars($val_zoom, ENT_QUOTES, 'cp1251');
			}
		}
		
	}else
		die('Неверный идентификатор клиента');
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_client_edit.html';

?>