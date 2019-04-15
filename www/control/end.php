<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	// если пользователь завершил регистрацию то 404
	$qRes = SqlQuery("Select `address` From `CLIENTS` Where `id`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		if ($Row[0] != '')
			require_once 'page404.php';
	}
	@mysql_free_result($qRes);
	
	// регионы исключения
	$Exceptions_id = array(84, 91);
	
	// загружаем все регионы
	$Regions = array();
	$qRes = SqlQuery("Select `region`, `type`, `name` From `LOCATION` Where `lvl`='1' and `deleted`='0' Order by `name`;");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Regions[] = $Rows;
		}
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	$add_city_str = false; // добавить город как строку, по умолчанию нет
	$val_region = '';
	$val_city   = '';
	$val_index  = '';
	$val_street = '';
	$val_house  = '';
	$val_office = '';
	$val_aya    = '';
	$val_url3   = '';
	$err = '';
	
	if (count($_POST)>0){
		if (!isset($_POST['btmes']))
			require_once 'page404.php';
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		
		// Регион
		if (isset($_POST['uregion']) && ($_POST['uregion']!='')){
			$val_region = $_POST['uregion'];
			$qRes = SqlQuery("Select * From `LOCATION` Where `region`='".mysql_real_escape_string($val_region)."' and `lvl`='1' and `deleted`='0';");
			if (mysql_num_rows($qRes) === 0){
				$err .= ' - Укажите регион.<br />';
				$val_region = '';
				$val_city   = '';
			}
			@mysql_free_result($qRes);
		}else
			$err .= ' - Не заполнено поле "Регион".<br />';
		
		// Город
		if (isset($_POST['ucity']) && ($_POST['ucity']!='')){
			$val_city = $_POST['ucity'];
			if (!CheckStr(4, $val_city, 200, false))
				$err .= ' - Поле "Город" превышает допустимую длину (ограничение 200 символов).<br />';
			if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9 .,\"\-«»]+$/", $val_city))
				$err .= ' - "Город" может состоять только из букв, цифр, пробела, тире, кавычек, точки, запятой.<br />';
			if ($err == ''){
				if (isset($_POST['cityid']) && ($_POST['cityid']!='')){ // город выбран из списка (есть идентификатор города)
					$val_city_id = $_POST['cityid'];
					$qRes = SqlQuery("Select * From `LOCATION` Where `city`='".mysql_real_escape_string($val_city_id)."' and `name`='".mysql_real_escape_string($val_city)."' and `lvl`='2' and `deleted`='0';");
					if (mysql_num_rows($qRes) === 1){ // идентификатор соответствует названию
						@mysql_free_result($qRes);
						$qRes = SqlQuery("Select * From `LOCATION` Where `city`='".mysql_real_escape_string($val_city_id)."' and `region`='".mysql_real_escape_string($val_region)."' and `lvl`='2' and `deleted`='0';");
						if (mysql_num_rows($qRes) === 0)
							$err .= ' - Неверный идентификатор города.<br />';
					}else
						$add_city_str = true;
					@mysql_free_result($qRes);
				}else{
					$qRes = SqlQuery("Select `city` From `LOCATION` Where `name`='".mysql_real_escape_string($val_city)."' and `region`='".mysql_real_escape_string($val_region)."' and `lvl`='2' and `deleted`='0';");
					if (mysql_num_rows($qRes) === 1){
						$Row = mysql_fetch_row($qRes);
						$val_city_id = $Row[0];
					}else
						$add_city_str = true;
				}
			}
		}else
			$err .= ' - Не заполнено поле "Город".<br />';
		
		// Почтовый индекс
		if (isset($_POST['uindex']) && ($_POST['uindex']!='')){
			$val_index = $_POST['uindex'];
			if (!CheckIntegerUnsign($val_index))
				$err .= ' - В поле "Почтовый индекс" допускается только ввод цифр.<br />';
			if (mb_strlen($val_index) != 6)
				$err .= ' - В поле "Почтовый индекс" допускается ввод 6 цифр.<br />';
		}else
			$val_index = '';
		
		// Улица
		if (isset($_POST['ustreet']) && ($_POST['ustreet']!='')){
			$val_street = $_POST['ustreet'];
			if (!CheckStr(4, $val_street, 126, false))
				$err .= ' - Поле "Улица" превышает допустимую длину (ограничение 126 символов).<br />';
			if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9 .,()\"\-«»]+$/", $val_street))
				$err .= ' - "Улица" может состоять только из букв, цифр, пробела, тире, кавычек, точки, запятой.<br />';
		}else
			$err .= ' - Не заполнено поле "Улица".<br />';
		
		// Дом
		if (isset($_POST['uhouse']) && ($_POST['uhouse']!='')){
			$val_house = $_POST['uhouse'];
			if (!CheckStr(4, $val_house, 30, false))
				$err .= ' - Поле "Дом" превышает допустимую длину (ограничение 30 символов).<br />';
			if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9 .,\-]+$/", $val_house))
				$err .= ' - "Дом" может состоять только из цифр, букв, пробела, тире, точки, запятой.<br />';
		}else
			$val_house = '';
		
		// Офис
		if (isset($_POST['uoffice']) && ($_POST['uoffice']!='')){
			$val_office = $_POST['uoffice'];
			if (!CheckStr(4, $val_office, 15, false))
				$err .= ' - Поле "Офис" превышает допустимую длину (ограничение 15 символов).<br />';
			if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9 .,\-]+$/", $val_office))
				$err .= ' - "Офис" может состоять только из цифр, букв, пробела, тире, точки, запятой.<br />';
		}else
			$val_office = '';
		
		// Абонентский ящик
		if (isset($_POST['uaya']) && ($_POST['uaya']!='')){
			$val_aya = $_POST['uaya'];
			if (!CheckStr(4, $val_aya, 15, false))
				$err .= ' - Поле "Абонентский ящик" превышает допустимую длину (ограничение 15 символов).<br />';
			if (!preg_match("/^[a-zA-Zа-яА-ЯёЁ0-9 .,\-]+$/", $val_aya))
				$err .= ' - "Офис" может состоять только из цифр, букв, пробела, тире, точки, запятой.<br />';
		}else
			$val_aya = '';
		
		// домен 3го уровня
		$exception_words = array('www', 'mail', 'my-sql', 'mysql', 'mysqli', 'php', 'pdo', 'admin', 'administrator', 'moder', 'moderator', 'root', 'test', 'biznesurfo');
		
		if ((isset($_POST['url3']) && ($_POST['url3']!=''))){
			$val_url3 = $_POST['url3'];
										
			$qRes1 = SqlQuery("Select `id` From `CLIENTS` Where `translit`='".mysql_real_escape_string($val_url3)."' Limit 1;");
			$qRes2 = SqlQuery("Select `id` From `RC_TRANSLIT` Where `translit`='".mysql_real_escape_string($val_url3)."' Limit 1;");
			if ((mysql_num_rows($qRes1)===1) || (mysql_num_rows($qRes2)===1))
				$err .= ' - Такой адрес уже занят, пожалуйста, введите другой.<br />';
			elseif (in_array($val_url3, $exception_words))
				$err .= ' - Такой адрес уже занят, пожалуйста, введите другой.<br />';
			@mysql_free_result($qRes1);
			@mysql_free_result($qRes2);
			
			if ($err == '') {
				if ((strlen($val_url3)<2) || (strlen($val_url3)>20))
					$err .= ' - Адрес должен быть от 2 до 20 символов.<br />';
				if (!preg_match("/^[a-z0-9\-]+$/", $val_url3)){
					$err .= ' - Адрес может состоять только из строчных букв английского алфавита, цифр, знака тире (-).<br />';
				}elseif (!preg_match("/^[a-z0-9][a-z0-9\-]+[a-z0-9]$/", $val_url3)){
					$err .= ' - Адрес не может начинаться или заканчиваться знаком тире (-).<br />';
				}elseif (preg_match("/-{2,}/", $val_url3))
					$err .= ' - Нельзя вводить подряд более 1го символа тире (-).<br />';
			}
		}else
			$err .= ' - Адрес сайта не может быть пустым.<br />';
		
		if ($err == ''){
			$now = date('Y-m-d H:i:s');
			// получаем название региона
			$qRes = SqlQuery("Select `type`, `name` From `LOCATION` Where `region`='".mysql_real_escape_string($val_region)."' and `lvl`='1' and `deleted`='0';");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$region_name = $Row[1].' '.$Row[0];
			}else
				require_once 'page404.php';
			@mysql_free_result($qRes);
			
			// получаем название города
			if (!$add_city_str){
				$qRes = SqlQuery("Select `type`, `name`, `capital` From `LOCATION` Where `city`='".mysql_real_escape_string($val_city_id)."' and `lvl`='2' and `deleted`='0';");
				if (mysql_num_rows($qRes) === 1){
					$Row = mysql_fetch_row($qRes);
					$city_name = $Row[0].' '.$Row[1].', ';
					$city_name2 = $Row[1];
					$is_capital = $Row[2];
				}else
					require_once 'page404.php';
				@mysql_free_result($qRes);
				$city_db = $val_city_id;
				$city_name_db = '';
			}else{
				$city_db = 0;
				if (in_array($val_region, $Exceptions_id)){
					if ($val_city == 'Санкт-Петербург' || $val_city == 'Москва'){
						$city_name = $city_name_db = '';
					}
				}else{
					$city_name = $val_city.', ';
					$city_name_db = $val_city;
				}
			}
			
			// формируем строку адрес
			$address_db  = $region_name.', '.$city_name.$val_street;
			$address_db .= ($val_house != '') ? ', д.'.$val_house : '';
			$address_db .= ($val_office != '') ? ', оф.'.$val_office : '';
			$address_db .= ($val_aya != '') ? ', а/я '.$val_aya : '';
			
			// добавляем адресные данные кампании
			SqlQuery("Update `CLIENTS` Set `region`='".mysql_real_escape_string($val_region)."', `city`='".mysql_real_escape_string($city_db)."', `city_name`='".mysql_real_escape_string($city_name_db)."', `translit`='".mysql_real_escape_string($val_url3)."', `index`='".mysql_real_escape_string($val_index)."', `street`='".mysql_real_escape_string($val_street)."', `house`='".mysql_real_escape_string($val_house)."', `office`='".mysql_real_escape_string($val_office)."', `aya`='".mysql_real_escape_string($val_aya)."', `address`='".mysql_real_escape_string($address_db)."', `coord`='',`map_zoom`='0', `status`='1', `udate`='".$now."' Where `id`='".mysql_real_escape_string($user_client_id)."';");
			
			// логируем добавленные данные компании
			$log_new = 'region - '.$val_region.'; city - '.$city_db.'; address - '.$address_db;
			SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='0', `action`='0', `old`='-', `new`='".mysql_real_escape_string($log_new)."', `adate`='".$now."';");
			if ($val_url3 != ''){
				SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='6', `action`='0', `old`='-', `new`='".mysql_real_escape_string($val_url3)."', `adate`='".$now."';");
			}
			
			header('Location: /kabinet/about');
		}else{
			$val_city   = htmlspecialchars($val_city, ENT_QUOTES, 'cp1251');
			$val_index  = htmlspecialchars($val_index, ENT_QUOTES, 'cp1251');
			$val_street = htmlspecialchars($val_street, ENT_QUOTES, 'cp1251');
			$val_house  = htmlspecialchars($val_house, ENT_QUOTES, 'cp1251');
			$val_office = htmlspecialchars($val_office, ENT_QUOTES, 'cp1251');
			$val_aya    = htmlspecialchars($val_aya, ENT_QUOTES, 'cp1251');
			$val_url3   = htmlspecialchars($val_url3, ENT_QUOTES, 'cp1251');
		}
	}
	
}else
	header('Location: /enter');

$jFiles = '<link type="text/css" rel="stylesheet" href="/css/autocomplete/jquery.autocomplete.css" />'."\n".
		  '<script type="text/javascript" src="/js/jquery.autocomplete.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/jquery.validate.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/additional-methods.min.js"></script>'."\n";
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				uregion: 'required',
				ucity: {
					required: true,
					pattern: /^[a-zA-Zа-яА-ЯёЁ0-9 .,\"\-«»]+$/i
				},
				uindex: {
					integer: true,
					rangelength: [6, 6]
				},
				ustreet: {
					required: true,
					pattern: /^[a-zA-Zа-яА-ЯёЁ0-9 .,()\"\-«»]+$/i
				},
				uhouse: {
					pattern: /^[a-zA-Zа-яА-ЯёЁ0-9 .,\-]+$/i
				},
				uoffice: {
					pattern: /^[a-zA-Zа-яА-ЯёЁ0-9 .,\-]+$/i
				},
				uaya: {
					pattern: /^[a-zA-Zа-яА-ЯёЁ0-9 .,\-]+$/i
				},
				url3: {
					required: true,
					pattern: /^[a-z0-9\-]+$/,
					minlength: 2,
					maxlength: 20,
					remote: {
						url: '/ajax/check_url3.php'
					}
				}
			},
			messages: {
				uregion: 'введите регион',
				ucity: {
					required: 'введите город',
					pattern: 'только буквы, цифры, пробел, тире, кавычки'
				},
				uindex: {
					integer: 'почтовый индекс должен состоять только из цифр',
					rangelength: 'необходимо ввести 6 цифр'
				},
				ustreet: {
					required: 'введите название улицы',
					pattern: 'только буквы, цифры, пробел, тире, кавычки'
				},
				uhouse: {
					pattern: 'только цифры, буквы, пробел, тире'
				},
				uoffice: {
					pattern: 'только цифры, буквы, пробел, тире'
				},
				uaya: {
					pattern: 'только цифры, буквы, пробел, тире'
				},
				url3: {
					required: 'адрес не должен быть пустым',
					pattern: 'только строчные буквы английского алфавита, цифры, знак тире (-)',
					minlength: 'не менее 2 символов',
					maxlength: 'не более 20 символов',
					remote: 'такой адрес уже занят, пожалуйста, введите другой'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});

	$('#uregion').change(function() {
		var id = $(this).val();
		$('#ucity').val('');
		if (id == '84') $('#ucity').val('Санкт-Петербург');
		if (id == '91') $('#ucity').val('Москва');
	});

	var region_id = '';

	$('#ucity').autocomplete('ajax/autocomplete_city.php', {
		minChars: 2,
		delay: 300,
		width: 310,
		selectFirst: true,
		autoFill: true,
		maxItemsToShow: 10,
		cacheLength: 1,
		extraParams: {region: function() {
			region_id = parseInt($('#uregion').val());
			return region_id;
		}},
		formatItem: function(row, i, num) {
			var result = '<div style="clear:both"><div style="float:left; width:25px; text-align:center;">' + row[1] + '</div><div style="float:left; width:250px; margin-left:5px;">' + row[0] + '</div></div>';
			return result;
		}
	}).result(function(event, row, formatted) {
		$('#city-id').val(row[2]);
	});
});

EoL;

$active_tab = -1;
$title = 'Личный кабинет - завершение регистрации';

require_once '../../includs/control/end.html';

?>