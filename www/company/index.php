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
	header('Location: http://'.$translit_company.'.'.$url3Href.'/');
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

if ($translit_company == ''){
#---- компания или не зарегистрированна или не имеет домен 3го уровня ----#

$forPred = true;

$Client = $Phones = $Emails = $Sites = array();
// получаем нужную информацию компании
$sql = "
	Select C.`name`, C.`address`, C.`logo`, CA.`about`, T.`name`, C.`city_name`, C.`coord`, C.`map_zoom`, C.`goods_qty`, C.`region`, C.`status_logo`
	From `CLIENTS` C
	left join `CITIES` T on T.`id`=C.`city`
	left join `CLIENTS_ABOUT` CA on CA.`id_client`=C.`id`
	Where C.`id`='".mysql_real_escape_string($id_company)."';
";
$qRes = SqlQuery($sql);
if (mysql_num_rows($qRes) === 1){
	$Client = mysql_fetch_row($qRes);
	if (($Client[4] == null) && ($Client[5] == ''))
		$Client[4] = GetCityException($Client[9]);
}else
	require_once '../page404.php';
@mysql_free_result($qRes);

// получаем все телефоны компании (минимум 1)
$qRes = SqlQuery("Select `full_phone` From `CLIENT_PHONES` Where `id_client`='".mysql_real_escape_string($id_company)."' Order by `sort_order`;");
$count_phones = mysql_num_rows($qRes);
if ($count_phones > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Phones[] = $Rows;
}
@mysql_free_result($qRes);

// получаем все емейлы компании
$qRes = SqlQuery("Select `email` From `CLIENT_EMAILS` Where `id_client`='".mysql_real_escape_string($id_company)."' Order by `sort_order`;");
$count_emails = mysql_num_rows($qRes);
if ($count_emails > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Emails[] = $Rows;
}
@mysql_free_result($qRes);

// получаем все сайты компании
$qRes = SqlQuery("Select `site` From `CLIENT_SITES` Where `id_client`='".mysql_real_escape_string($id_company)."' Order by `sort_order`;");
$count_sites = mysql_num_rows($qRes);
if ($count_sites > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Sites[] = $Rows;
}
@mysql_free_result($qRes);

@list($nm, $opf) = explode(',', $Client[0]);

// Подгружаем данные вкладок
$tabsContent = getDataFromDB($Client[3]);

// Обработка логотипа
if ($Client[2] != ''){
	$imgsize = getimagesize('../logo/'.$Client[2]);
	$Client[2] = 'alt="'.$Client[0].'" src="logo/'.$Client[2].'"';
	if ((count($imgsize)>1)&&($imgsize[0]>270))
		$Client[2] .= ' width="270"';
	if ((count($imgsize)>1)&&($imgsize[1])>90)
		$Client[2] .= ' height="90"';
}

// Обработка телефонов
$first_phone = $Phones[0][0];
if ($count_phones > 1){
	$first_phone .= ',';
	$all_phones = '';
	for ($i=1; $i<$count_phones; $i++){
		$all_phones .= $Phones[$i][0].',<br />';
	}
	$all_phones = substr($all_phones, 0, -7);
}

// Обработка сайтов
if ($count_sites > 0){
	$first_site = $Sites[0][0];
	$all_sites = '';
	foreach ($Sites as $site){
		$all_sites .= '<a href="goto/'.$id_company.'?url='.$site[0].'" rel="nofollow" target="_blank" class="web">'.$site[0].'</a> , ';
	}
	$all_sites = substr($all_sites, 0, -3);
}

// Обработка ящиков
if ($count_emails > 0){
	$all_emails = '';
	foreach ($Emails as $email){
		$all_emails .= '<a href="mailto/'.$id_company.'" rel="nofollow" target="_blank">'.$email[0].'</a> , ';
	}
	$all_emails = substr($all_emails, 0, -3);
}

// Список рубрик в которых размещается компания
$Rubs = array ();
$qRes = SqlQuery('Select R.new_url, R.name, R.id_parent, R.id, C.cnt_str From CLIENT_RUBRICS C join RUBRICS R on R.id=C.rubric Where C.client='.$id_company.' and C.cnt_str>0;');
if ((mysql_num_rows($qRes)>0)&&($qRes)){
	while ($Rows = mysql_fetch_row($qRes))
		$Rubs[] = $Rows;
	@mysql_free_result($qRes);
}
$rubsQty = count($Rubs);
$rubrics = $rubsQty>0;

for ($i = 0; $i<$rubsQty; $i++)
	if (($Rubs[$i][2]==62)&&($Rubs[$i][3]!=149)&&($Rubs[$i][3]!=150)&&($Rubs[$i][3]!=156))
		$Rubs[$i][1] .= ' инструмент';

PutClientStat($id_company, 0);
$pagNam = 'company/'.$id_company.'.html';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";

// YandexMap
if (($Client[6]!='') && ($Client[7]!='')){
	// YandexMap - static
	$YandexStatImg = '<img xmlns="http://www.w3.org/1999/xhtml" xmlns:lego="https://lego.yandex-team.ru" xmlns:dev="http://dev.yandex.ru/xmlns" alt="" src="http://static-maps.yandex.ru/1.x/?l=map&ll='.$Client[6].'&pt='.$Client[6].',pmors&z='.$Client[7].'&size=256,180&key='.$yandex_key.'" class="noborder" />';
	
	// YandexMap - dinamic
	$addScripts = '<script src="http://api-maps.yandex.ru/1.1/index.xml?key='.$yandex_key.'" type="text/javascript"></script>'."\n";
	$jsFuncs = <<<EoL
<script type="text/javascript">
	YMaps.jQuery(function() {
		var container = YMaps.jQuery("#YBigShow"),
		map = new YMaps.Map(container[0]);

		map.enableScrollZoom();

		var zoom = new YMaps.Zoom({
			customTips: [
				{ index: 11, value: "город" },
				{ index: 14, value: "улица" },
				{ index: 17, value: "дом" }
			]
		});
		map.addControl(zoom);
		map.addControl(new YMaps.TypeControl([YMaps.MapType.MAP,YMaps.MapType.SATELLITE,YMaps.MapType.HYBRID],[0,1,2]));

		YMaps.jQuery('a.show-map').bind('click', function() {
			$('#YBigBox').modal({
				minWidth: 800,
				minHeight: 600,
				overlayClose: true,
				persist: true,
				opacity: 30,
				overlayCss: {backgroundColor:"#aabbcc"},
				onOpen: function(dialog) {
					map.setCenter(new YMaps.GeoPoint($Client[6]), $Client[7]+1);
					var placemark = new YMaps.Placemark(new YMaps.GeoPoint($Client[6]), {style: "default#houseIcon"});
					placemark.name = "$opf &laquo;$nm&raquo;";
					placemark.description = '<i>$Client[1]</i>';
					map.addOverlay(placemark);
					
					dialog.overlay.fadeIn(150, function() {
						dialog.container.fadeIn(150);
						dialog.data.show();
						map.redraw();
					});
				},
				onClose: function(dialog) {
					dialog.container.fadeOut(150, function() {
						dialog.overlay.fadeOut(150, function() {
							dialog.data.hide();
							map.removeAllOverlays();
							$.modal.close();
						});
					});
				},
			});
			return false;
		});
		
		$('a.modal-close').bind('click', function() {
			$.modal.close();
			return false;
		});
	});
</script>
EoL;
}

// Получение рекламы клиента
$Bans = array();
$qRes = SqlQuery('Select B.id, B.name, B.width, B.height From BANNERS B Where B.client='.$id_company.' and B.kind=2 and B.rubric>0 Order by B.id Limit 0, 4;');
if ((mysql_num_rows($qRes)>0)&&($qRes)){
	while ($Rows = mysql_fetch_row($qRes))
		$Bans[] = $Rows;
	@mysql_free_result($qRes);
}
$bansQty = count($Bans);
if ($bansQty>0){ // Регистрация показов баннеров
	$s = '';
	for ($i = 0; $i<$bansQty; $i++)
		$s.=$Bans[$i][0].',';
	// НУЖНО ПЕРЕДЕЛАТЬ С УЧЕТОМ ИЗМЕНЕНИЙ В ТАБЛИЦЕ COUNTERS_BT		
	//SqlQuery('Insert into COUNTERS_BT (module, is_banner) Select B.id, 1 From BANNERS B Where B.id IN('.substr($s, 0, -1).');');
}
$Moduls = array();
if ($bansQty<4){
	$i = 0;
	$qRes = SqlQuery('Select distinct T.head, T.txt, T.phone From TEXTMODULS T where T.client='.$id_company.' order by T.id limit 0, '.(4-$bansQty).';');
	if ((mysql_num_rows($qRes)>0)&&($qRes)){
		while ($Rows = mysql_fetch_row($qRes)){
			$Moduls[$i][0] = $Rows[0];
			$Moduls[$i][1] = $Rows[1];
			$Moduls[$i][2] = $Rows[2];
			$Moduls[$i][3] = '';
			$Moduls[$i][4] = '';
			$qRes2 = SqlQuery('Select T.id, T.link From TEXTMODULS T where T.client='.$id_company.' and T.head='."'{$Moduls[$i][0]}'".' and T.txt='."'{$Moduls[$i][1]}'".' and T.phone='."'{$Moduls[$i][2]}'".' order by T.id limit 0, 1;');
			if ((mysql_num_rows($qRes2)==1)&&($qRes2)){
				if ($Rows2 = mysql_fetch_row($qRes2)){
					$Moduls[$i][3] = $Rows2[0];
					$Moduls[$i++][4] = $Rows2[1];
				} else
					$i++;
				@mysql_free_result($qRes2);
			}else
				$i++;
		}
		@mysql_free_result($qRes);
	}
}
$modulsQty = count($Moduls);
if ($modulsQty>0) { // Регистрация показов текстовых модулей
	$s = '';
	for ($i = 0; $i<$modulsQty; $i++)
		$s .= $Moduls[$i][3].',';
	// НУЖНО ПЕРЕДЕЛАТЬ С УЧЕТОМ ИЗМЕНЕНИЙ В ТАБЛИЦЕ COUNTERS_BT
	//SqlQuery('Insert into COUNTERS_BT (module, is_banner) Select T.id, 0 From TEXTMODULS T Where T.id IN('.substr($s, 0, -1).');');
}
$onlyBanners = $modulsQty==0;

$cityFormAction = $pagNam;
$Crumbs[0][0] = 'firms';
$Crumbs[0][1] = 'Предприятия';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Карточка компании';
$crumbsQty = 2;

$tagH1 = $opf.' '.$nm;

$keyws = $Client[0].' карточка компании';
$descr = 'Вся информация о компании '.$nm.' : адреса, телефоны, вид деятельности';
$title = $nm.' - '.$Client[4].' - прайс-лист компании '.$nm;

//$isDirect = true;

$template = 'public/index.html';

}else{
#--------------------- компания имеет домен 3го уровня ---------------------#

// общие данные о компании
$Client = $Rubrics = $Client_phones = array();
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

// коротко о компании
$qRes = SqlQuery("Select left(`about`, 350), `status` From `CLIENTS_ABOUT` Where `id_client` = '".mysql_real_escape_string($id_company)."';");
if (mysql_num_rows($qRes) === 1){
	$Row = mysql_fetch_row($qRes);
	$about_short = strip_tags(getDataFromDB($Row[0]));
	$about_status = $Row[1];
}else
	require_once 'page404.php';
@mysql_free_result($qRes);

// 6 последних обновленных товаров
$sql_filter = (!$isOwner) ? " and S.`status` = '0'" : "";
$odd = true; $count = 1;
$Products_short = array();
$qRes = SqlQuery("
	Select S.`name`, S.`translit`, S.`dop`, S.`price`, S.`dog`,
		(Select `name` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1),
		(Select `ext` From `STR_IMG` Where `id_product` = S.`id` Order by `sort_order` limit 1),
		S.`status`
	From `STR` S
	Where S.`client` = '".mysql_real_escape_string($id_company)."' and S.`active` = '1'".$sql_filter." Order by S.`udate` desc Limit 6;
");
$count_products_short = mysql_num_rows($qRes);
if ($count_products_short > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$Products_short[] = $Rows;
}
@mysql_free_result($qRes);

// 2 последние добавленные новости
$sql_filter = (!$isOwner) ? " and `status` = '0'" : "";
$News_short = array();
$qRes = SqlQuery("Select `title`, `url`, `img`, `ext`, left(`text`, 120), `status` From `CLIENT_NEWS` Where `id_client` = '".mysql_real_escape_string($id_company)."' and `visible` = '1'".$sql_filter." Order by `adate` desc Limit 2;");
if (mysql_num_rows($qRes) > 0){
	while ($Rows = mysql_fetch_row($qRes))
		$News_short[] = $Rows;
}
@mysql_free_result($qRes);

$action = 'index';
$title = $name.' - '.$sity_name.', товары, цены, прайс компании '.$name;
$descr = 'Вся информация о компании '.$name.' - '.$sity_name.', прайс-лист, контакты, цены';
$keyws = $name.', информация о компании, контакты, адрес, товары, цена';

$template = 'default/index.html';

}

require_once '../../includs/company/'.$template;

?>