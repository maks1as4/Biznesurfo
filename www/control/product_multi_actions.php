<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	if (count($_POST)>0){
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		if (isset($_POST['multi_action']) && !empty($_POST['multi_action'])){
			switch ($_POST['multi_action']){
				case 'archiveAll':{
					foreach ($_POST['multi_items'] as $tovar_id){
						$log_old = '';
						$qRes = SqlQuery("Select `id` From `STR` Where `id`='".mysql_real_escape_string($tovar_id)."' and `client`='".mysql_real_escape_string($user_client_id)."' and `active`='1';");
						if (mysql_num_rows($qRes) === 1){
							$Row = mysql_fetch_row($qRes);
							$log_old = 'id - '.$Row[0];
						}
						@mysql_free_result($qRes);
						SqlQuery("Update `STR` Set `active`='0', `udate`='".date('Y-m-d H:i:s')."' Where `id`='".mysql_real_escape_string($tovar_id)."' and `client`='".mysql_real_escape_string($user_client_id)."';");
						// логируем архивируемый товар
						SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='2', `action`='3', `old`='".mysql_real_escape_string($log_old)."', `new`='-', `adate`='".date('Y-m-d H:i:s')."';");
					}
					break;
				}
				case 'deleteAll':{
					foreach ($_POST['multi_items'] as $tovar_id){
						$log_old = '';
						$qRes = SqlQuery("Select `rubric`, `name`, `price`, `text` From `STR` Where `id`='".mysql_real_escape_string($tovar_id)."' and `client`='".mysql_real_escape_string($user_client_id)."' and `active`='1';");
						if (mysql_num_rows($qRes) === 1){
							$Row = mysql_fetch_row($qRes);
							$log_old = 'rubric - '.$Row[0].'; name - '.$Row[1].'; price - '.$Row[2].'; text - '.$Row[3];
						}
						@mysql_free_result($qRes);
						// если есть картинка для товара, то удаляем
						$qRes = SqlQuery("Select `name`, `ext` From `STR_IMG` Where `id_product`='".mysql_real_escape_string($tovar_id)."';");
						if (mysql_num_rows($qRes) === 1){
							$Row = mysql_fetch_row($qRes);
							// удаляем фото
							chmod('../i/products', 0777);
							deleteImages('../i/products/', $Row[0], $Row[1]);
							deleteImages('../i/products/', $Row[0], $Row[1], '_medium');
							deleteImages('../i/products/', $Row[0], $Row[1], '_small');
							chmod('../i/products', 0555);
							// удаляем строку из БД
							SqlQuery("Delete From `STR_IMG` Where `id_product`='".mysql_real_escape_string($tovar_id)."';");
						}
						@mysql_free_result($qRes);
						// удаляем данные из БД
						SqlQuery("Delete From `STR` Where `id`='".mysql_real_escape_string($tovar_id)."' and `client`='".mysql_real_escape_string($user_client_id)."' and `active`='1';");
						// логируем удаляемый товар
						if ($log_old != '')
							SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='2', `action`='2', `old`='".mysql_real_escape_string($log_old)."', `new`='-', `adate`='".date('Y-m-d H:i:s')."';");
					}
					break;
				}
				default:{
					require_once 'page404.php';
				}
			}
			header('Location: /kabinet');
		}else
			require_once 'page404.php';
	}else
		require_once 'page404.php';
}else
	header('Location: /enter');

?>