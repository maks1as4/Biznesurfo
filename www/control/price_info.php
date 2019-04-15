<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){

	$client_info = getClientInfo($user_client_id);

	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}

	// таймаут для лодера
	$timeOut = ($client_info['count_price'] > 1000) ? 4000 : 100;

	// нет товаров, чтобы скачать прайс-лист
	$show_price_download_error = false;
	if (isset($_SESSION['price_download_err']) && ($_SESSION['price_download_err'])){
		$show_price_download_error = true;
		unset ($_SESSION['price_download_err']);
	}

	// ошибка в загрузке файла
	$show_price_upload_error = false;
	if (isset($_SESSION['price_upload_err']) && ($_SESSION['price_upload_err'])){
		$show_price_upload_error = true;
		unset ($_SESSION['price_upload_err']);
	}

	// фаил не выбран
	$show_price_empty_error = false;
	if (isset($_SESSION['price_empty_err']) && ($_SESSION['price_empty_err'])){
		$show_price_empty_error = true;
		unset ($_SESSION['price_empty_err']);
	}

	// загружен неверный формат или пустой лист
	$show_price_format_error = false;
	if (isset($_SESSION['price_format_err']) && ($_SESSION['price_format_err'])){
		$show_price_format_error = true;
		unset ($_SESSION['price_format_err']);
	}

	// ничего не добавилось и не загрузилось
	$show_price_upload_none = false;
	if (isset($_SESSION['price_upload_none']) && ($_SESSION['price_upload_none'])){
		$show_price_upload_none = true;
		unset ($_SESSION['price_upload_none']);
	}

	// товары или обновились или загрузились
	$show_price_uploaded = false;
	if (isset($_SESSION['price_uploaded']) && ($_SESSION['price_uploaded'])){
		$show_price_uploaded = true;
		unset ($_SESSION['price_uploaded']);
	}

	// есть пропущенные строчки
	$show_price_upload_ignor = false;
	if (isset($_SESSION['price_upload_ignor']) && ($_SESSION['price_upload_ignor'])){
		$show_price_upload_ignor = true;
		unset ($_SESSION['price_upload_ignor']);
	}

	// колличество добавленных, обновленных, проигнорированных строчек
	$added_rows = $updated_rows = $not_updated_rows = '';
	if (isset($_SESSION['price_upload_counts']) && ($_SESSION['price_upload_counts'])){
		list($added_rows, $updated_rows, $not_updated_rows) = explode('|', $_SESSION['price_upload_counts']);
		unset ($_SESSION['price_upload_counts']);
	}

}else
	header('Location: /enter?link=/price-info');

$jFiles = '<script type="text/javascript" src="/js/jquery.fileDownload.js"></script>'."\n";
$jqAdd = <<<EoL
$(document).ready(function() {
	$(document).on('click', 'a.get-my-price', function () {
		loading_modal('Файл подготавливается для загрузки,<br />пожалуйста подождите...');
		$.fileDownload($(this).prop('href'), {
			successCallback: function (url) {
				$.modal.close();
			},
			failCallback: function (html, url) {
				$.modal.close();
				alert('Ошибка загрузки, повторите попытку.');
			},
			checkInterval: $timeOut
		});
		return false;
	});

	$(document).on('submit', 'form.send-my-price', function () {
		loading_modal('Загрузка файла, обработка строк,<br />пожалуйста подождите...');
	});
});

function loading_modal(text) {
	$('#progres-modal').modal({
		position: ['30%'],
		overlayClose: false,
		opacity: 70,
		overlayId: 'modal-overlay',
		closeClass: 'modal-close',
		onShow: function (dialog) {
			var modal = this;
			$('div.text', dialog.data[0]).append(text);
			$('a.close', dialog.data).click(function() {
				$.modal.close();
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

$active_tab = 0;
$title = 'Личный кабинет - загрузить прайс-лист - информация';

require_once '../../includs/control/price_info.html';

?>