<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}

	$Phones = array();
	$qRes = SqlQuery("Select `id`, `full_phone` From `CLIENT_PHONES` Where `id_client`='".mysql_real_escape_string($user_client_id)."' Order by `sort_order`;");
	$count_phones = mysql_num_rows($qRes);
	if ($count_phones > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Phones[] = $Rows;
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	$Emails = array();
	$qRes = SqlQuery("Select `id`, `email` From `CLIENT_EMAILS` Where `id_client`='".mysql_real_escape_string($user_client_id)."' Order by `sort_order`;");
	$count_emails = mysql_num_rows($qRes);
	if ($count_emails > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Emails[] = $Rows;
	}
	@mysql_free_result($qRes);
	
	$Sites = array();
	$qRes = SqlQuery("Select `id`, `site` From `CLIENT_SITES` Where `id_client`='".mysql_real_escape_string($user_client_id)."' Order by `sort_order`;");
	$count_sites = mysql_num_rows($qRes);
	if ($count_sites > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Sites[] = $Rows;
	}
	@mysql_free_result($qRes);

}else
	header('Location: /enter?link=/contacts-change');

$jqAdd = <<<EoL
$(document).ready(function() {
	$('a.delete-phone').bind('click', function(e) {
		e.preventDefault();
		var id = $(e.target).attr('id').substr(2);
		delete_modal('Вы уверенны, что хотите удалить телефон?', '30%', function() {
			$.ajax({
				type: 'POST',
				url: '/ajax/contacts_delete.php',
				data: {'id':id, 'action':'p'},
				dataType: 'json',
				success: function(data) {
					if (data != null) {
						if (!data.error) {
							location.reload();
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

	$('a.delete-email').bind('click', function(e) {
		e.preventDefault();
		var id = $(e.target).attr('id').substr(2);
		delete_modal('Вы уверенны, что хотите удалить email?', '35%', function() {
			$.ajax({
				type: 'POST',
				url: '/ajax/contacts_delete.php',
				data: {'id':id, 'action':'e'},
				dataType: 'json',
				success: function(data) {
					if (data != null) {
						if (data.error === false) {
							location.reload();
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

	$('a.delete-site').bind('click', function(e) {
		e.preventDefault();
		var id = $(e.target).attr('id').substr(2);
		delete_modal('Вы уверенны, что хотите удалить сайт?', '35%', function() {
			$.ajax({
				type: 'POST',
				url: '/ajax/contacts_delete.php',
				data: {'id':id, 'action':'s'},
				dataType: 'json',
				success: function(data) {
					if (data != null) {
						if (data.error === false) {
							location.reload();
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
$title = 'Личный кабинет - изменить контакты компании';

require_once '../../includs/control/contacts_change.html';

?>