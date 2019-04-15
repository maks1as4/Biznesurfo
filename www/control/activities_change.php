<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	$err = '';
	
	$Current_rub = $Need_open = $All_categoryes = $Subcategoryes = $id_rub = array();
	
	// Проверяем, есть ли выбранные рубрики
	$qRes = SqlQuery("Select CA.`rubric`, R.`id_parent`, CA.`client` From `CLIENT_ACTIVITIES` CA Left join `RUBRICS` R on CA.`rubric`=R.`id` Where CA.`client`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Current_rub[] = $Rows[0];
			$Need_open[]   = $Rows[0];
			if ($Rows[1] != 0)
				$Need_open[] = $Rows[1];
		}
	}
	@mysql_free_result($qRes);
	
	// Формируем массив всех рубрик
	$qRes = SqlQuery("Select `id`, `name` From `RUBRICS` Where `level`='1' and `visible`='1' Order by `sort_order`;");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$All_categoryes[] = $Rows;
	}
	@mysql_free_result($qRes);
	
	// Формируем двумерный массив всех подрубрик с привязкой на рубрики
	foreach ($All_categoryes as $key=>$categoryes){
		$qRes = SqlQuery("Select `id`, `name` From `RUBRICS` Where `id_parent`='".mysql_real_escape_string($categoryes[0])."' and `visible`='1' Order by `sort_order`;");
		if (mysql_num_rows($qRes) > 0){
			while ($Rows = mysql_fetch_row($qRes))
				$Subcategoryes[$key][] = $Rows;
		}
		@mysql_free_result($qRes);
	}
	
	if (count($_POST)>0){
		if (!isset($_POST['btmes']))
			require_once 'page404.php';
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		
		if (isset($_POST['rub']) && count($_POST['rub'])>0){
			foreach ($_POST['rub'] as $id){
				// проверяем на рубрику или подрубрику
				$qRes = SqlQuery("Select `id_parent` From `RUBRICS` Where `id`='".mysql_real_escape_string($id)."';");
				if (mysql_num_rows($qRes) === 1){
					$Row = mysql_fetch_row($qRes);
					$id_parent = $Row[0];
					$is_second_rub = ($id_parent !== 0) ? true : false;
				}else
					continue; // если рубрика не найдена, то пропускаем шаг цикла
				@mysql_free_result($qRes);
				// если подрубрика, то проверяем на соответствие подрубрики к рубрике
				if ($is_second_rub){
					$qRes = SqlQuery("Select * From `RUBRICS` Where `id`='".mysql_real_escape_string($id)."' and `id_parent`='".mysql_real_escape_string($id_parent)."';");
					if (mysql_num_rows($qRes) > 0){
						$id_rub[] = $id;
					}else
						continue; // если подрубрика не соответствует рубрике, то пропускаем шаг цикла
					@mysql_free_result($qRes);
				}
			}
		}
		
		if ($err == ''){
			// удаляем старые рубрики, если есть
			if (count($Current_rub) > 0)
				SqlQuery("Delete From `CLIENT_ACTIVITIES` Where `client`='".mysql_real_escape_string($user_client_id)."';");
			// записываем рубрики
			$count = 1;
			foreach ($id_rub as $id){
				if ($count <= 100){
					SqlQuery("Insert Into `CLIENT_ACTIVITIES` Set `client`='".mysql_real_escape_string($user_client_id)."', `rubric`='".mysql_real_escape_string($id)."';");
					$count++;
				}else
					break;
			}
			
			// переход на страницу управления данными пользователя
			header('Location: /kabinet/about');
		}
	}
	
}else
	header('Location: /enter?link=/activities-change');

$jqAdd = <<<EoL
$(document).ready(function() {
	var Ul_obj = $('div.activities-list ul');
	var Ul_unclosed_obj = $('div.activities-list ul li.unclosed').parent();
	Ul_obj.hide();
	Ul_unclosed_obj.show();
	Ul_unclosed_obj.next().text('закрыть');

	$('div.activities-list a.play').bind('click', function() {
		$(this).prev().slideToggle(100);
		$(this).text(function(i, text){
			return text === 'подробнее' ? 'закрыть' : 'подробнее';
		})
		return false;
	});

	$('#sections_show').click(function() {
		Ul_obj.show(100);
		Ul_obj.next().text('закрыть');
	});

	$('#sections_hide').click(function() {
		Ul_obj.hide(100);
		Ul_obj.next().text('подробнее');
	});
});

EoL;

$active_tab = 1;
$title = 'Личный кабинет - выбор вида деятельности';

require_once '../../includs/control/activities_change.html';

?>