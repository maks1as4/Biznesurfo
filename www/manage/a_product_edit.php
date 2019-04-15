<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	if (isset($_GET['tid']) && !empty($_GET['tid']) && CheckIntegerUnsign($_GET['tid'])){
		
		$tovar_id = $_GET['tid'];
		
		$Tovar = array();
		$qRes = SqlQuery("
			Select C.`name`, S.`name`, S.`translit`, S.`price`, S.`text`, SI.`id`, SI.`name`, SI.`ext`
			From `STR` S
				join `CLIENTS` C on S.`client`=C.`id`
				left join `STR_IMG` SI on S.`id`=SI.`id_product`
			Where S.`id`='".mysql_real_escape_string($tovar_id)."';
		");
		if (mysql_num_rows($qRes) === 1)
			$Tovar = mysql_fetch_row($qRes);
		else
			die('Неверный идентификатор товара');
		@mysql_free_result($qRes);
		
		$var_name = $Tovar[1];
		$var_translit = $Tovar[2];
		$var_price = $Tovar[3];
		$var_text = $Tovar[4];
		$var_img_id = ($Tovar[5] != null) ? $Tovar[5] : '';
		$var_img_name = ($Tovar[6] != null) ? $Tovar[6] : '';
		$var_ing_ext = ($Tovar[7] != null) ? $Tovar[7] : '';
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			if (isset($_POST['uname']) && ($_POST['uname']!='')){
				//$var_name = $_POST['uname'];
				$var_name = stripslashes($_POST['uname']); // -gpc-
			}else
				$err .= ' - Не заполнено поле "наименование товара".<br />';
			
			if (isset($_POST['utranslit']) && ($_POST['utranslit']!='')){
				$var_translit = $_POST['utranslit'];
			}else
				$err .= ' - Не заполнено поле "транслит".<br />';
			
			if (isset($_POST['uprice']) && ($_POST['uprice']!='')){
				$var_price = $_POST['uprice'];
			}else
				$var_price = '';
			
			if (isset($_POST['utext']) && ($_POST['utext']!='')){
				//$var_text = $_POST['utext'];
				$var_text = stripslashes($_POST['utext']); // -gpc-
			}else
				$var_text = '';
			
			if ($err == ''){
				// изменяем данные таблицы товаров
				SqlQuery("Update `STR` Set `name`='".mysql_real_escape_string($var_name)."', `translit`='".mysql_real_escape_string($var_translit)."', `price`='".mysql_real_escape_string($var_price)."', `text`='".mysql_real_escape_string($var_text)."' Where `id`='".mysql_real_escape_string($tovar_id)."';");
				// переходим на страницу проверки товаров
				header('Location: /management/adm/products-check');
			}else{
				$var_name = htmlspecialchars($var_name, ENT_QUOTES, 'cp1251');
				$var_translit = htmlspecialchars($var_translit, ENT_QUOTES, 'cp1251');
				$var_price = htmlspecialchars($var_price, ENT_QUOTES, 'cp1251');
				$var_text = htmlspecialchars($var_text, ENT_QUOTES, 'cp1251');
			}
		}
		
	}else
		die('Неверный идентификатор товара');
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_product_edit.html';

?>