<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	// адресс, координаты, о компании
	$qRes = SqlQuery("
		Select C.`address`, CA.`about`, C.`logo`, C.`coord`, C.`map_zoom`, C.`status_logo`
		From `CLIENTS` C left join `CLIENTS_ABOUT` CA on C.`id`=CA.`id_client`
		Where C.`id`='".mysql_real_escape_string($user_client_id)."';
	");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		$client_address = getDataFromDB($Row[0]);
		$client_about   = getDataFromDB($Row[1]);
		$client_logo    = $Row[2];
		$client_coords  = $Row[3];
		$client_zoom    = $Row[4];
		$client_status_logo = $Row[5];
		if ($client_logo != ''){
			if (file_exists('../logo/'.$client_logo)){
				$imgsize = getimagesize('../logo/'.$client_logo);
				$logo_width  = $imgsize[0];
				$logo_height = $imgsize[1];
				if ((count($imgsize) > 1) && ($logo_width > 200)){
					$k = $logo_width/200;
					$logo_width  = '200';
					$logo_height = round($logo_height/$k);
				}
			}
		}
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	// телефоны компании
	$Client_phones = array();
	$qRes = SqlQuery("Select `full_phone` From `CLIENT_PHONES` Where `id_client`='".mysql_real_escape_string($user_client_id)."' Order by `sort_order`;");
	$count_phones = mysql_num_rows($qRes);
	if ($count_phones > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Client_phones[] = $Rows;
	}
	@mysql_free_result($qRes);
	
	// почтовые ящики
	$Client_emails = array();
	$qRes = SqlQuery("Select `email` From `CLIENT_EMAILS` Where `id_client`='".mysql_real_escape_string($user_client_id)."' Order by `sort_order`;");
	$count_emails = mysql_num_rows($qRes);
	if ($count_emails > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Client_emails[] = $Rows;
	}
	@mysql_free_result($qRes);
	
	// сайты компании
	$Client_sites = array();
	$qRes = SqlQuery("Select `site` From `CLIENT_SITES` Where `id_client`='".mysql_real_escape_string($user_client_id)."' Order by `sort_order`;");
	$count_sites = mysql_num_rows($qRes);
	if ($count_sites > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Client_sites[] = $Rows;
	}
	@mysql_free_result($qRes);
	
	// виды деятельности
	$Activities = array();
	$qRes = SqlQuery("Select C.`rubric`, R.`name` From `CLIENT_ACTIVITIES` C left join `RUBRICS` R on C.`rubric`=R.`id` Where C.`client`='".mysql_real_escape_string($user_client_id)."' and !isnull(C.`rubric`);");
	if (mysql_num_rows($qRes) > 0){
		$i = 0;
		while ($Rows = mysql_fetch_row($qRes)){
			$Activities[$i][] = $Rows[1];
			$i++;
		}
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter?link=/about');

$jqAdd = <<<EoL
$(document).ready(function() {
	$('a.delete-logo').click(function() {
		delete_modal('Вы уверенны, что хотите удалить логотип?', '30%', function() {
			$.ajax({
				type: 'POST',
				url: '/ajax/logo_delete.php',
				dataType: 'json',
				success: function(data) {
					if (data != null) {
						if (data.error === false) {
							window.location = '/kabinet/about';
						} else
							alert('Ошибка! Некорректный обмен данными.');
					} else
						alert('Ошибка! Сервер не отвечает. Повторите попытку.');
				},
				timeout: 5000
			});
		});
		return false;
	});
});

function delete_modal(question, top, callback) {
	$('#delete-modal').modal({
		position: [top],
		overlayClose: false,
		opacity: 70,
		overlayId: 'modal-overlay',
		closeClass: 'modal-close',
		onShow: function (dialog) {
			var modal = this;
			$('.question', dialog.data[0]).append(question);
			$('.yes', dialog.data[0]).click(function () {
				if ($.isFunction(callback)) {
					callback.apply();
				}
				modal.close();
			});
			$('a.close', dialog.data).click(function() {
				modal.close();
				return false;
			});
		},
		onOpen: function(dialog) {
			dialog.overlay.fadeIn(100, function() {
				dialog.container.fadeIn(100);
				dialog.data.show();
			});
		},
		onClose: function(dialog) {
			dialog.overlay.fadeOut(100, function() {
				dialog.container.fadeOut(100);
				dialog.data.hide();
				$.modal.close();
			});
		}
	});
}

EoL;

$active_tab = 1;
$title = 'Личный кабинет - контакты';

require_once '../../includs/control/about.html';

?>