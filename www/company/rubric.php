<?php

require_once '../../includs/head.php';

$id_company = 0;

if (count($_GET)>0){
	if ((isset($_GET['sfid'])) && ($_GET['sfid']!='')){
		$getData = CheckIDTranslit($_GET['sfid']);
		$id_company = $getData[0];
		$translit_company = $getData[1];
		$go301 = $getData[2];
	}
}

if ($id_company === 0)
	require_once '../page404.php';

if ($go301){
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://'.$translit_company.'.'.$url3Href.'/news');
	exit();
}

// проверка, если компания имеет доступ к кабинету и при этом End=0,
// то смотрим является ли пользователь владельцем страницы
// если нет, то 404
/*$end = -1;
$qRes = SqlQuery("Select (`rubrics` and `address` and `contacts`) From `END` Where `id_client`='".mysql_real_escape_string($id_company)."';");
if (mysql_num_rows($qRes) === 1){
	$Row = mysql_fetch_row($qRes);
	$end = $Row[0];
}
@mysql_free_result($qRes);
if ($end == 0){
	if (!isset($user_client_id))
		require_once '../page404.php';
	elseif ($user_client_id != $id_company)
		require_once '../page404.php';
}*/

// получаем транслит
if ((isset($_GET['trnsl']))&&($_GET['trnsl']!='')&&(CheckStr(11, $_GET['trnsl'], 0, false))) {
	$translit = $_GET['trnsl'];
	if (get_magic_quotes_gpc())
		$translit = stripslashes($translit);
	if (strlen($translit)>100)
		$translit = substr($translit,0,100);
	$translit = rtrim($translit,'/');
}

// получаем id и название рубрики
$qRes = SqlQuery("Select `id`, `name` From `RUBRICS` Where `new_url` = '".mysql_real_escape_string($translit)."';");
if (mysql_num_rows($qRes) === 1){
	$Row = mysql_fetch_row($qRes);
	$rubric_id = $Row[0];
	$rubric_name = getDataFromDB($Row[1]);
}else
	require_once 'page404.php';
@mysql_free_result($qRes);

// общие данные о компании
$Client = $Rubrics = array();
$name = $opf = $full_name = '';
$logo_width = $logo_height = $products_qty = 0;
$isOwner =	(isset($user_client_id) && CheckIntegerUnsign($user_client_id) && $user_client_id==$id_company) ||
			(isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='adm') ? 
			true : false;

$Client_data = getClientContent($id_company, $isOwner);
$Client = $Client_data[0];
if (empty($Client)) require_once 'page404.php';
$Client_phones = $Client_data[7];
$name = getDataFromDB($Client_data[1][0]);
$opf = (isset($Client_data[1][1])) ? getDataFromDB($Client_data[1][1]) : '';
$full_name = ($opf != '') ? $opf.' '.$name : $name;
if ($full_name == '') require_once 'page404.php';
$logo_width = $Client_data[2][0];
$logo_height = $Client_data[2][1];
$Rubrics = $Client_data[3];
if (!empty($Client_data[4])) $products_qty = $Client_data[4];
$sity_name = getDataFromDB($Client_data[5]);
$sity_name_where = getDataFromDB($Client_data[6]);

// номер страницы
if ((isset($_GET['p']))&&($_GET['p']!='')&&(CheckIntegerUnsign($_GET['p']) ))
	$curPageNo = $_GET['p'];

// сортировка
$product_page = 'http://'.$translit_company.'.'.$url3Href.'/products/'.$translit;
$link_name = $product_page.'?order=name&by=desc&p='.$curPageNo;
$arr_name = 'asc';
$link_price = $product_page.'?order=price&by=asc&p=1';
$arr_price = '';

$order = 'S.`name`';
$by = 'asc';

if (isset($_GET['by']) && !empty($_GET['by'])){
	if (!isset($_GET['order'])){
		header('Location: '.$product_page);
		exit();
	}
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
			header('Location: '.$product_page);
			exit();
		}
	}
}

if (isset($_GET['order']) && !empty($_GET['order'])){
	if (!isset($_GET['by'])){
		header('Location: '.$product_page);
		exit();
	}
	switch ($_GET['order']){
		case 'name':{
			$link_name = $product_page.'?order=name&by='.$not_by.'&p='.$curPageNo;
			$arr_name = $by;
			$link_price = $product_page.'?order=price&by=asc&p=1';
			$arr_price = '';
			$parStr = '?order=name&by='.$by;
			$order = 'S.`name`';
			break;
		}
		case 'price':{
			$link_name = $product_page.'?order=name&by=asc&p=1';
			$arr_name = '';
			$link_price = $product_page.'?order=price&by='.$not_by.'&p='.$curPageNo;
			$arr_price = $by;
			$parStr = '?order=price&by='.$by;
			$order = 'S.`price`';
			break;
		}
		default:{
			header('Location: '.$product_page);
			exit();
		}
	}
}

// Сборка URL для пейджинга
$pagNam = 'http://'.$translit_company.'.'.$url3Href.'/products/'.$translit;
$qtyPerPage = $itemLimits['company_products']; // Число строк на странице
$countPages = 10; // Число страниц в ленте

$sql_filter = (!$isOwner) ? " and S.`status` = '0'" : "";

$qRes = SqlQuery("Select count(*) From `STR` S Where S.`client`='".mysql_real_escape_string($id_company)."' and S.`rubric` = '".mysql_real_escape_string($rubric_id)."' and S.`active` = '1'".$sql_filter.";");
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)&&($Rows = mysql_fetch_row($qRes))){
	$RowsQty = $Rows[0];
	$PageNav = CreateBootstrapNav($RowsQty, $countPages, $pagNam, $parStr);
	$pageNavQty = count($PageNav);
}
@mysql_free_result($qRes);

$Products = array();
$qRes = SqlQuery("
	Select S.`name`, S.`translit`, S.`dop`, S.`price`, S.`dog`,
		(Select `name` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1),
		(Select `ext` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1),
		S.`id`, S.`status`
	From `STR` S
		join `RUBRICS` R on S.`rubric` = R.`id`
	Where S.`client` = '".mysql_real_escape_string($id_company)."' and `rubric` = '".mysql_real_escape_string($rubric_id)."' and S.`active` = '1'".$sql_filter."
	Order by ".$order." ".$by."
	Limit ".($curPageNo-1)*$qtyPerPage.", ".$qtyPerPage.";
");
if (mysql_num_rows($qRes) > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Products[] = $Rows;
}
@mysql_free_result($qRes);

// jquery
$jq = <<<EoL
$('a.order').bind('click', function(e) {
	e.preventDefault();
	var product = $(e.target).attr('product').substr(2);
	var supplier = $(e.target).attr('supplier').substr(2);
	var code = $(e.target).attr('code').substr(1);
	$.ajax({
		type: 'POST',
		url: '../ajax/buy_get_product_info.php',
		data: {
			'product': product,
			'supplier': supplier,
			'code': code
		},
		dataType: 'json',
		success: function(data) {
			if (data != null) {
				if (data.check) {
					var Name_obj = $('#buy-name');
					var Email_obj = $('#buy-email');
					var Phone_obj = $('#buy-phone');
					var Comment_obj = $('#buy-comment');
					var Price_obj = $('#product-price');
					Comment_obj.val('Здравствуйте, $full_name.\\n\\n'+'Мы хотим заказать: "' + data.name + '"\\nв количестве 1 шт.');
					$('#product-title').text(data.name);
					Price_obj.empty();
					if (data.price != '')
						Price_obj.append('&nbsp;<span class="label label-info">' + data.price + '</span>');
					$('#window-order').modal({
						overlayClose: false,
						opacity: 50,
						overlayCss: {backgroundColor:'#333333'},
						closeClass: 'simplemodal-close',
						onShow: function (dialog) {
							$('#ok-order').click(function() {
								$.ajax({
									type: 'POST',
									url: '../ajax/buy_send_email.php',
									data: {
										'product': product,
										'supplier': supplier,
										'code': code,
										'name': Name_obj.val(),
										'email': Email_obj.val(),
										'phone': Phone_obj.val(),
										'comment': Comment_obj.val()
									},
									dataType: 'json',
									success: function(data) {
										if (data != null) {
											if (data.check) {
												if (data.error100 || data.error200 || data.error300) {
													if (data.error100)
														Name_obj.addClass('error').parent('div').children('span.error').show();
													if (data.error200)
														Email_obj.addClass('error').parent('div').children('span.m-empty').show();
													if (data.error300)
														Email_obj.addClass('error').parent('div').children('span.m-incorrect').show();
												} else {
													dialog.overlay.fadeOut(100);
													dialog.data.hide();
													$.modal.close();
													$('.notification-center').notify({
														message: {text: 'Ваша заявка отправлена'},
														fadeOut: {enabled: true, delay: 3000},
														type: 'notification'
													}).show();
												}
											} else {
												alert('Заявка не отправленна! Ошибка: были переданы некорректные данные');
												$.modal.close();
											}
										} else {
											alert('Заявка не отправленна! Ошибка: передачи данных не завершена');
											$.modal.close();
										}
									},
									error: function() {
										alert('Ошибка запроса к базе данных');
										$.modal.close();
									},
									timeout: 5000
								});
								return false;
							});
							$('#window-order input.edit').bind('focus', function() {
								$(this).removeClass('error').parent().children('span.error').hide();
							});
						},
						onOpen: function(dialog) {
							dialog.overlay.fadeIn(100, function() {
								dialog.container.fadeIn(100);
								dialog.data.show();
								$('#buy-name').focus();
							});
						},
						onClose: function(dialog) {
							dialog.container.fadeOut(100, function() {
								dialog.overlay.fadeOut(100);
								dialog.data.hide();
								$.modal.close();
							});
						}
					});
				} else
					alert('Ошибка: были переданы некорректные данные');
			} else
				alert('Ошибка: передачи данных не завершена');
		},
		error: function() {
			alert('Ошибка запроса к базе данных');
		},
		timeout: 5000
	});
	return false;
});

EoL;

$order_box = true;
$action = 'catalog';
$title = $rubric_name.' компании '.$name.'. '.$rubric_name.' '.$sity_name_where.' - цена, прайс-лист.';
$descr = $rubric_name.' '.$sity_name_where.'. Прайс-лист компании '.$name.' с информацией о стоимости.';
$keyws = $rubric_name.', прайс-лист, '.$name.', '.$sity_name;

$template = 'default/rubric.html';

require_once '../../includs/company/'.$template;

?>