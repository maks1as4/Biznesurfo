<?php

require_once '../../includs/head.php';

$id_company = 0;
$id_product = 0;

if (count($_GET)>0){
	if ((isset($_GET['sfid'])) && ($_GET['sfid']!='')){
		$getData = CheckIDTranslit($_GET['sfid']);
		$id_company = $getData[0];
		$translit_company = $getData[1];
		$go301 = $getData[2];
	}
	if ((isset($_GET['pid'])) && ($_GET['pid']!='') && (CheckIntegerUnsign($_GET['pid'])))
		$id_product = $_GET['pid'];
	if (isset($_GET['translit']))
		$translit = $_GET['translit'];
}

if (($id_company === 0) || ($id_product === 0))
	require_once '../page404.php';

/*if ($go301){
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: http://'.$translit_company.'.'.$url3Href.'/news/'.$translit.'.html');
	exit();
}*/

$isOwner =	(isset($user_client_id) && CheckIntegerUnsign($user_client_id) && $user_client_id==$id_company) ||
			(isset($_SESSION['user']['role']) && $_SESSION['user']['role']==='adm') ? 
			true : false;

// получаем данные товара
$sql_filter = (!$isOwner) ? " and S.`status` = '0'" : "";
$Product = array();
$qRes = SqlQuery("
	Select S.`name`, S.`dop`, S.`price`, S.`dog`, S.`text`, S.`active`, S.`status`, S.`client`, R.`name`, R.`new_url`,
		(Select `name` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1),
		(Select `ext` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1),
		S.`adate`, S.`udate`, S.`translit`
	From `STR` S
		join `RUBRICS` R on S.`rubric` = R.`id`
	Where S.`id` = '".mysql_real_escape_string($id_product)."' and S.`client` = '".mysql_real_escape_string($id_company)."'".$sql_filter.";
");
if (mysql_num_rows($qRes) === 1)
	$Product = mysql_fetch_row($qRes);
else{
	if ($translit_company != '')
		require_once 'page404.php';
	else
		require_once '../page404.php';
}
@mysql_free_result($qRes);

if ((isset($translit))&&($translit!='')&&(strcasecmp($Product[14],$translit)!=0)){
	$loc = 'Location: /product/'.$Product[14].'.html';
	header("HTTP/1.1 301 Moved Permanently");
	header($loc);
	exit;
}

// общие данные о компании
$Client = $Rubrics = array();
$name = $opf = $full_name = '';
$logo_width = $logo_height = $products_qty = 0;

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

// похожие товары
$Goods = $ids = array();
$string = getDataFromDB($Product[0]);
$delim = strlen($string);
$ids[0] = $id_product;
$qty = 0;
do {
	$qRes = SqlQuery("
		Select S.`id`, S.`name`, S.`translit`, S.`dop`, S.`price`, S.`dog`,
			(Select `name` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1),
			(Select `ext` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1)
		From `STR` S
		Where S.`id` not IN(".implode(',', $ids).") and S.`client`='".mysql_real_escape_string($id_company)."' and S.`active`='1' and S.`status`='0' and S.`name` like '".substr($string, 0, $delim)."%'
		Order by S.`name`;
	");
	if (mysql_num_rows($qRes) > 0) {
		while (($Rows = mysql_fetch_row($qRes))&&($qty<4)) {
			if (!in_array($Rows[0], $ids)) {
				$ids[] = $Rows[0];
				$Goods[] = $Rows;
				$qty++;
			}
		}
	}
	@mysql_free_result($qRes);
	$delim = (integer) ceil($delim/2);
} while (($delim>5)&&($qty<4));
$count_goods = count($Goods);
$odd = true;
$count = 1;

// сокращение "крошки"
if ((integer)(strlen($Product[8]) + strlen($Product[0])) > 65)
	$product_krsh = substr(getDataFromDB($Product[0]), 0, ((integer)(65 - strlen($Product[8])) - 5)).'...';
else
	$product_krsh = getDataFromDB($Product[0]);

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
$title = getDataFromDB($Product[0]).' купить '.$sity_name_where.' цена, описание от компании '.$name;
$descr = 'Описание товара, цена, купить '.getDataFromDB($Product[0]).' '.$sity_name_where.' компания '.$name;
$keyws = getDataFromDB($Product[0]).' описание, цена, купить '.$sity_name_where;

$template = 'default/product.html';

require_once '../../includs/company/'.$template;

?>