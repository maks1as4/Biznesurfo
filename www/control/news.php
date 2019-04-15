<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	// провер€ем домен 3го уровн€
	$qRes = SqlQuery("Select `translit` From `CLIENTS` Where `id`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		$translit_company = ($Row[0] !== NULL) ? $Row[0] : '';
	}
	@mysql_free_result($qRes);
	
#---------------------- ‘ильтры ----------------------

	$conut_pages = 20; // колличество загружаемого материала
	
	if (isset($_SESSION['count_news']) && !empty($_SESSION['count_news'])){
		$conut_pages = $_SESSION['count_news'];
	}
	
	if (count($_POST)>0){
		if (isset($_POST['action']) && !empty($_POST['action'])){
			if ($_POST['action'] == 'count_pages'){
				if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
					require_once 'page404.php';
				if (isset($_POST['count']) && !empty($_POST['count'])){
					$counts_validate = array(10, 20, 40, 60, 100, 999999);
					if (in_array($_POST['count'], $counts_validate) && $_POST['count'] != 20){
						$_SESSION['count_news'] = $_POST['count'];
						$conut_pages = $_POST['count'];
					}else{
						if (isset($_SESSION['count_news'])) unset($_SESSION['count_news']);
						$conut_pages = 20;
					}
				}else{
					if (isset($_SESSION['count_news'])) unset($_SESSION['count_news']);
					$conut_pages = 20;
				}
			}
		}
	}
	
#---------------------- —ортировка новостей ----------------------
	
	// номер страницы
	if ((isset($_GET['p'])) && ($_GET['p'] != '') && (CheckIntegerUnsign($_GET['p'])))
		$curPageNo = $_GET['p'];
	
	// иницилизаци€ параметров ссылок
	$page_url = 'http://'.$baseHref.'/kabinet/news';
	$link_date = $page_url.'?order=date&by=asc&p='.$curPageNo;
	$arr_date = 'desc';
	$link_name = $page_url.'?order=name&by=asc&p=1';
	$arr_name = '';
	
	// инициализаци€ параметров сортировки
	$order = '`adate`'; // сортировка в SQL
	$by = 'desc'; // пор€док сортировки
	
	// получаем пор€док сортировки
	if (isset($_GET['by']) && !empty($_GET['by'])){
		if (!isset($_GET['order'])) header('Location: '.$page_url);
		switch ($_GET['by']){
			case 'asc':{
				$by = 'asc';
				$not_by = 'desc';
				break;
			}
			case 'desc':{
				$by = 'desc';
				$not_by = 'asc';
				break;
			}
			default:{
				header('Location: '.$page_url);
			}
		}
	}
	
	if (isset($_GET['order']) && !empty($_GET['order'])){
		if (!isset($_GET['by'])) header('Location: '.$page_url);
		switch ($_GET['order']){
			case 'date':{
				$link_date = $page_url.'?order=date&by='.$not_by.'&p='.$curPageNo;
				$arr_date = $by;
				$link_name = $page_url.'?order=name&by=asc&p=1';
				$arr_name = '';
				$parStr = '?order=date&by='.$by;
				$order = '`adate`';
				break;
			}
			case 'name':{
				$link_date = $page_url.'?order=date&by=desc&p=1';
				$arr_date = '';
				$link_name = $page_url.'?order=name&by='.$not_by.'&p='.$curPageNo;
				$arr_name = $by;
				$parStr = '?order=name&by='.$by;
				$order = '`title`';
				break;
			}
			default:{
				header('Location: '.$page_url);
			}
		}
	}
	
#---------------------- ѕостранична€ разбивка ----------------------
	
	$pagNam = '/kabinet/news'; // —борка URL дл€ пейджинга
	$qtyPerPage = $conut_pages; // „исло строк на странице
	$countPages = 10; // „исло страниц в ленте
	
	$qRes = SqlQuery("Select count(*) From `CLIENT_NEWS` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		$RowsQty = $Row[0];
		$PageNav = CreateKabinetNav($RowsQty, $countPages, $pagNam, $parStr);
		$pageNavQty = count($PageNav);
	}
	@mysql_free_result($qRes);
	
	// получаем список новостей
	$News = array();
	$qRes = SqlQuery("
		Select `id`, `title`, `img`, `ext`, `adate`, `visible`, `url`, `status`
		From `CLIENT_NEWS`
		Where `id_client`='".mysql_real_escape_string($user_client_id)."'
		Order by ".$order." ".$by."
		Limit ".($curPageNo-1)*$qtyPerPage.", ".$qtyPerPage.";
	");
	$count_news = mysql_num_rows($qRes);
	if ($count_news > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$News[] = $Rows;
	}
	
}else
	header('Location: /enter?link=/news');

$jqAdd = <<<EoL
$(document).ready(function() {
	$('a.delete-news').bind('click', function(e) {
		e.preventDefault();
		var id = $(e.target).attr('id').substr(2);
		delete_modal('¬ы уверенны, что хотите удалить новость?', '30%', function() {
			window.location = '/kabinet/delete-news/' + id + '?confirm';
		});
		return false;
	});

	$('table.table-kabinet tr.tr-hover div.actions-links').css({'opacity':'.6'});

	$('table.table-kabinet tr.tr-hover').hover(
		function() {
			$(this).find('div.actions-links').css({'opacity':'1'});
			$(this).find('div.actions-links a.set-red').removeClass('gray').addClass('red');
		},
		function() {
			$(this).find('div.actions-links').css({'opacity':'.6'});
			$(this).find('div.actions-links a.set-red').removeClass('red').addClass('gray');
		}
	);

	$('#show-all').tooltip({
		title: '¬озможно долга€ загрузка страницы.',
		trigger: 'hover'
	});

	$('#show-all').click(function() {
		$("#page-selector option").filter(function() {
			return $(this).text() == 'все';
		}).prop('selected', true);
		$('#set-count-pages').submit();
	});

	$('#set-count-pages').change(function() {
		$(this).submit();
	});

	var icon = $('#change-checked').children('i');

	$('#change-checked').click(function() {
		var toggle;
		icon.toggleClass('icon-chekbox-none icon-chekbox-check');
		toggle = (icon.hasClass('icon-chekbox-check')) ? 1 : 0;
		var checkboxs = $('table.table-kabinet input:checkbox');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = toggle;
		});
		actions();
	});

	$('#all-checked').click(function() {
		icon.removeClass('icon-chekbox-none').addClass('icon-chekbox-check');
		var checkboxs = $('table.table-kabinet input:checkbox');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 1;
		});
		actions();
	});

	$('#all-unchecked').click(function() {
		icon.removeClass('icon-chekbox-check').addClass('icon-chekbox-none');
		var checkboxs = $('table.table-kabinet input:checkbox');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 0;
		});
		actions();
	});

	$('#check-checked').click(function() {
		var checkboxs = $('table.table-kabinet input:checkbox');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 0;
		});
		icon.removeClass('icon-chekbox-check').addClass('icon-chekbox-none');
		checkboxs = $('table.table-kabinet input:checkbox').not('.review');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 1;
		});
		actions();
	});

	$('#review-checked').click(function() {
		var checkboxs = $('table.table-kabinet input:checkbox');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 0;
		});
		icon.removeClass('icon-chekbox-check').addClass('icon-chekbox-none');
		checkboxs = $('table.table-kabinet input:checkbox.review');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 1;
		});
		actions();
	});

	$('#show-checked').click(function() {
		var checkboxs = $('table.table-kabinet input:checkbox');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 0;
		});
		icon.removeClass('icon-chekbox-check').addClass('icon-chekbox-none');
		checkboxs = $('table.table-kabinet input:checkbox').not('.hidden');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 1;
		});
		actions();
	});

	$('#hide-checked').click(function() {
		var checkboxs = $('table.table-kabinet input:checkbox');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 0;
		});
		icon.removeClass('icon-chekbox-check').addClass('icon-chekbox-none');
		checkboxs = $('table.table-kabinet input:checkbox.hidden');
		jQuery.each(checkboxs, function(i) {
			checkboxs[i].checked = 1;
		});
		actions();
	});

	$('table.table-kabinet input:checkbox').bind('click', function() {
		actions();
	});

	$('.multi-action-button').bind('click', function(e) {
		e.preventDefault();
		if (!$(this).hasClass('disabled')) {
			var todo = $(this).attr('todo');
			$('#multi-action').val(todo);
			$('#multi-form').submit();
		}
	});

	$('#multi-delete').click(function() {
		if (!$(this).hasClass('disabled')) {
			delete_modal('”далить выбранные новости?', '30%', function() {
				$('#multi-action').val('deleteAll');
				$('#multi-form').submit();
			});
		}
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

function actions() {
	var active_buttons = false;
	var checkboxs = $('table.table-kabinet input:checkbox');
	jQuery.each(checkboxs, function(i) {
		if (checkboxs[i].checked == 1) {
			active_buttons = true;
			return false;
		}
	});
	if (active_buttons) {
		$('#actions').children('button').removeClass('disabled');
	} else {
		$('#actions').children('button').addClass('disabled');
	}
}

EoL;

$active_tab = 2;
$title = 'Ћичный кабинет - все новости';

require_once '../../includs/control/news.html';

?>