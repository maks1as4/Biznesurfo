<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	// проверка на вывод ошибки публикации, (ограничение тарифа)
	$show_add_warning = false;
	if (isset($_SESSION['add_warning_arch']) && !empty($_SESSION['add_warning_arch']) && $_SESSION['add_warning_arch'] == 'show'){
		$show_add_warning = true;
		unset($_SESSION['add_warning_arch']);
	}
	
#---------------------- ‘ильтр ----------------------
	
	// загрузка всех уникальных рубрик товаров компании
	$Rubrics_filter = array();
	$qRes = SqlQuery("Select distinct(S.`rubric`), R.`name` From `STR` S join `RUBRICS` R on S.`rubric`=R.`id` Where S.`client`='".mysql_real_escape_string($user_client_id)."' and S.`active`='0' Order by R.`id`;");
	if (mysql_num_rows($qRes) > 1){
		while ($Rows = mysql_fetch_row($qRes))
			$Rubrics_filter[] = $Rows;
	}
	@mysql_free_result($qRes);
	
	$filter = ''; // фильтр по рубрикам, по умолчанию показывать все рубрики
	
	if (isset($_SESSION['rfilter_arch']) && !empty($_SESSION['rfilter_arch'])){
		$filter .= 'S.`rubric` in (';
		foreach ($_SESSION['rfilter_arch'] as $f)
			$filter .= $f.',';
		$filter = substr($filter, 0, -1);
		$filter .= ') and ';
	}
	
	$conut_pages = 20; // колличество загружаемого материала
	
	if (isset($_SESSION['count_archive']) && !empty($_SESSION['count_archive'])){
		$conut_pages = $_SESSION['count_archive'];
	}
	
	if (count($_POST)>0){
		if (isset($_POST['action']) && !empty($_POST['action'])){
			if ($_POST['action'] == 'filter_rubrics'){
				if (!isset($_POST['btmes']))
					require_once 'page404.php';
				if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
					require_once 'page404.php';
				if (isset($_POST['rub_filter']) && !empty($_POST['rub_filter'])){
					$_SESSION['rfilter_arch'] = $_POST['rub_filter'];
					$filter = '';
					$filter .= 'S.`rubric` in (';
					foreach ($_POST['rub_filter'] as $f)
						$filter .= $f.',';
					$filter = substr($filter, 0, -1);
					$filter .= ') and ';
				}else{
					if (isset($_SESSION['rfilter_arch'])) unset($_SESSION['rfilter_arch']);
					$filter = '';
				}
			}elseif ($_POST['action'] == 'count_pages'){
				if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
					require_once 'page404.php';
				if (isset($_POST['count']) && !empty($_POST['count'])){
					$counts_validate = array(10, 20, 40, 60, 100, 999999);
					if (in_array($_POST['count'], $counts_validate) && $_POST['count'] != 20){
						$_SESSION['count_archive'] = $_POST['count'];
						$conut_pages = $_POST['count'];
					}else{
						if (isset($_SESSION['count_archive'])) unset($_SESSION['count_archive']);
						$conut_pages = 20;
					}
				}else{
					if (isset($_SESSION['count_archive'])) unset($_SESSION['count_archive']);
					$conut_pages = 20;
				}
			}
		}
	}
	
	// очистить фильтр
	if (isset($_GET['fclear']) && empty($_GET['fclear'])){
		if (isset($_SESSION['rfilter_arch'])) unset($_SESSION['rfilter_arch']);
		header('Location: /kabinet/archive');
		exit;
	}
	
#---------------------- —ортировка товаров ----------------------
	
	// номер страницы
	if ((isset($_GET['p'])) && ($_GET['p'] != '') && (CheckIntegerUnsign($_GET['p'])))
		$curPageNo = $_GET['p'];
	
	// иницилизаци€ параметров ссылок
	$page_url = 'http://'.$baseHref.'/kabinet/archive';
	$link_rubric = $page_url.'?order=rubric&by=desc&p='.$curPageNo;
	$arr_rubric = 'asc';
	$link_name = $page_url.'?order=name&by=asc&p=1';
	$arr_name = '';
	$link_price = $page_url.'?order=price&by=asc&p=1';
	$arr_price = '';
	
	// инициализаци€ параметров сортировки
	$order = 'R.`name`'; // сортировка в SQL
	$by = 'asc'; // пор€док сортировки
	$order_add = ', S.`name`'; // под сортировка
	$split = true; // разбиваем на рубрики
	$rubric_id = '0'; // идентификатор рубрики
	
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
			case 'rubric':{
				$link_rubric = $page_url.'?order=rubric&by='.$not_by.'&p='.$curPageNo;
				$arr_rubric = $by;
				$link_name = $page_url.'?order=name&by=asc&p=1';
				$arr_name = '';
				$link_price = $page_url.'?order=price&by=asc&p=1';
				$arr_price = '';
				$parStr = '?order=rubric&by='.$by;
				$order = 'R.`name`';
				$order_add = ', S.`name`';
				$split = true;
				break;
			}
			case 'name':{
				$link_rubric = $page_url.'?order=rubric&by=asc&p=1';
				$arr_rubric = '';
				$link_name = $page_url.'?order=name&by='.$not_by.'&p='.$curPageNo;
				$arr_name = $by;
				$link_price = $page_url.'?order=price&by=asc&p=1';
				$arr_price = '';
				$parStr = '?order=name&by='.$by;
				$order = 'S.`name`';
				$order_add = '';
				$split = false;
				break;
			}
			case 'price':{
				$link_rubric = $page_url.'?order=rubric&by=asc&p=1';
				$arr_rubric = '';
				$link_name = $page_url.'?order=name&by=asc&p=1';
				$arr_name = '';
				$link_price = $page_url.'?order=price&by='.$not_by.'&p='.$curPageNo;
				$arr_price = $by;
				$parStr = '?order=price&by='.$by;
				$order = 'S.`price`';
				$order_add = '';
				$split = false;
				break;
			}
			default:{
				header('Location: '.$page_url);
			}
		}
	}
	
#---------------------- ѕостранична€ разбивка ----------------------
	
	$pagNam = '/kabinet/archive'; // —борка URL дл€ пейджинга
	$qtyPerPage = $conut_pages; // „исло строк на странице
	$countPages = 10; // „исло страниц в ленте
	
	$qRes = SqlQuery("Select count(*) from `STR` S Where ".$filter."S.`client`='".mysql_real_escape_string($user_client_id)."' and S.`active`='0';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		$RowsQty = $Row[0];
		$PageNav = CreateKabinetNav($RowsQty, $countPages, $pagNam, $parStr);
		$pageNavQty = count($PageNav);
	}
	@mysql_free_result($qRes);
	
	// получаем списов товаров
	$Products = array();
	$qRes = SqlQuery("
		Select S.`id`, S.`name`, S.`dop`, S.`price`, R.`name`,
			(Select `name` From `STR_IMG` Where `id_product`=S.`id` Limit 1),
			(Select `ext` From `STR_IMG` Where `id_product`=S.`id` Limit 1),
			S.`translit`, S.`status`, S.`rubric`
		From `STR` S
			join `RUBRICS` R on S.`rubric` = R.`id`
		Where ".$filter."S.`client`='".mysql_real_escape_string($user_client_id)."' and S.`active`='0'
		Order by ".$order." ".$by."".$order_add."
		Limit ".($curPageNo-1)*$qtyPerPage.", ".$qtyPerPage.";
	");
	$count_products = mysql_num_rows($qRes);
	if ($count_products > 0){
		while ($Rows = mysql_fetch_row($qRes))
			$Products[] = $Rows;
	}
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter?link=/archive');

$jqAdd = <<<EoL
$(document).ready(function() {
	$('a.delete-product').bind('click', function(e) {
		e.preventDefault();
		var id = $(e.target).attr('id').substr(2);
		delete_modal('¬ы уверенны, что хотите удалить товар?', '30%', function() {
			window.location = '/kabinet/archive/product-delete/' + id + '?confirm';
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

	$('table.table-kabinet input:checkbox').bind('click', function() {
		actions();
	});

	$('#multi-publish').click(function() {
		if (!$(this).hasClass('disabled')) {
			$('#multi-action').val('publishAll');
			$('#multi-form').submit();
		}
	});

	$('#multi-delete').click(function() {
		if (!$(this).hasClass('disabled')) {
			delete_modal('”далить выбранные товары?', '30%', function() {
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

$rubric_filtre_type = 'rfilter_arch';
$active_tab = 4;
$title = 'Ћичный кабинет - архив товаров';

require_once '../../includs/control/archive.html';

?>