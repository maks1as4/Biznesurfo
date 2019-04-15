<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['cid']) && !empty($_GET['cid']) && CheckIntegerUnsign($_GET['cid'])){
		
		$client_id = $_GET['cid'];
		
		$Client = array();
		$qRes = SqlQuery("Select `id`, `name`, `coord`, `map_zoom`, `address` From `CLIENTS` Where `id`='".mysql_real_escape_string($client_id)."';");
		if (mysql_num_rows($qRes) === 1)
			$Client = mysql_fetch_row($qRes);
		else
			require_once 'page404.php';
		@mysql_free_result($qRes);
		
		$val_coords = $Client[2];
		$val_zoom = $Client[3];
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			if (isset($_POST['ucoords']) && ($_POST['ucoords']!='')){
				$val_coords = $_POST['ucoords'];
			}else
				$err .= ' - Не заполнено поле "координаты".<br />';
			
			if (isset($_POST['uzoom']) && ($_POST['uzoom']!='')){
				$val_zoom = $_POST['uzoom'];
			}else
				$err .= ' - Не заполнено поле "зум".<br />';
			
			if ($err == ''){
				// данные прошли проверку - обновляем запись
				SqlQuery("Update `CLIENTS` Set `coord`='".mysql_real_escape_string($val_coords)."', `map_zoom`='".mysql_real_escape_string($val_zoom)."' Where `id`='".mysql_real_escape_string($client_id)."';");
				// переходим на страницу кпроверки координат
				header('Location: /management/adm/maps-check');
			}else{
				$val_coords = htmlspecialchars($val_coords, ENT_QUOTES, 'cp1251');
				$val_zoom = htmlspecialchars($val_zoom, ENT_QUOTES, 'cp1251');
			}
		}
		
	}else
		die('Неверный идентификатор товара');
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_map_edit.html';

?>