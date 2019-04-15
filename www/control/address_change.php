<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	// ������� ����������
	$Exceptions_id = array(84, 91);
	
	// ��������� ��� �������
	$Regions = array();
	$qRes = SqlQuery("Select `region`, `type`, `name` From `LOCATION` Where `lvl`='1' and `deleted`='0' Order by `name`;");
	if (mysql_num_rows($qRes) > 0){
		while ($Rows = mysql_fetch_row($qRes)){
			$Regions[] = $Rows;
		}
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	// ��������� ������ ������������
	$Client_address = array();
	$qRes = SqlQuery("Select `region`, `city`, `city_name`, `index`, `street`, `house`, `office`, `aya`, `address` From `CLIENTS` Where `id`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Client_address = mysql_fetch_row($qRes);
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	// �������� �������� ������
	if ($Client_address[1] != 0){ // ���� ���� ������������� ������ (�� ����� 0), �� �������� �������� �� ����
		$qRes = SqlQuery("Select `name` From `LOCATION` Where `city`='".mysql_real_escape_string($Client_address[1])."' and `lvl`='2' and `deleted`='0';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$def_city = $Row[0];
		}
		@mysql_free_result($qRes);
	}elseif ($Client_address[2] != ''){ // ���� ������������� �������, �� ���� �������� ������...
		$def_city = $Client_address[2];
	}elseif (in_array($Client_address[0], $Exceptions_id)){ // ���� ������������� �������, �� ������ �������� ��� ����������
		switch ($Client_address[0]){
			case 84 :{
				$def_city = '�����-���������';
				break;
			}
			case 91 :{
				$def_city = '������';
				break;
			}
			default :{
				$def_city = '';
			}
		}
	}else // ������ �� ������
		$def_city = '';
	
	$add_city_str = false; // �������� ����� ��� ������, �� ��������� ���
	$val_region = ($Client_address[0] != 0) ? $Client_address[0] : '';
	$val_city   = $def_city;
	$val_index  = $Client_address[3];
	$val_street = $Client_address[4];
	$val_house  = $Client_address[5];
	$val_office = $Client_address[6];
	$val_aya    = $Client_address[7];
	$log_old    = 'region - '.$Client_address[0].'; city - '.$Client_address[1].'; address - '.$Client_address[8];
	$err = '';
	
	if (count($_POST)>0){
		if (!isset($_POST['btmes']))
			require_once 'page404.php';
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		
		// ������
		if (isset($_POST['uregion']) && ($_POST['uregion']!='')){
			$val_region = $_POST['uregion'];
			$qRes = SqlQuery("Select * From `LOCATION` Where `region`='".mysql_real_escape_string($val_region)."' and `lvl`='1' and `deleted`='0';");
			if (mysql_num_rows($qRes) === 0){
				$err .= ' - ������� ������.<br />';
				$val_region = '';
				$val_city   = '';
			}
			@mysql_free_result($qRes);
		}else
			$err .= ' - �� ��������� ���� "������".<br />';
		
		// �����
		if (isset($_POST['ucity']) && ($_POST['ucity']!='')){
			$val_city = $_POST['ucity'];
			if (!CheckStr(4, $val_city, 200, false))
				$err .= ' - ���� "�����" ��������� ���������� ����� (����������� 200 ��������).<br />';
			if (!preg_match("/^[a-zA-Z�-��-߸�0-9 .,\"\-��]+$/", $val_city))
				$err .= ' - "�����" ����� �������� ������ �� ����, ����, �������, ����, �������, �����, �������.<br />';
			if ($err == ''){
				if (isset($_POST['cityid']) && ($_POST['cityid']!='')){ // ����� ������ �� ������ (���� ������������� ������)
					$val_city_id = $_POST['cityid'];
					$qRes = SqlQuery("Select * From `LOCATION` Where `city`='".mysql_real_escape_string($val_city_id)."' and `name`='".mysql_real_escape_string($val_city)."' and `lvl`='2' and `deleted`='0';");
					if (mysql_num_rows($qRes) === 1){ // ������������� ������������� ��������
						@mysql_free_result($qRes);
						$qRes = SqlQuery("Select * From `LOCATION` Where `city`='".mysql_real_escape_string($val_city_id)."' and `region`='".mysql_real_escape_string($val_region)."' and `lvl`='2' and `deleted`='0';");
						if (mysql_num_rows($qRes) === 0)
							$err .= ' - �������� ������������� ������.<br />';
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
			$err .= ' - �� ��������� ���� "�����".<br />';
		
		// �������� ������
		if (isset($_POST['uindex']) && ($_POST['uindex']!='')){
			$val_index = $_POST['uindex'];
			if (!CheckIntegerUnsign($val_index))
				$err .= ' - � ���� "�������� ������" ����������� ������ ���� ����.<br />';
			if (mb_strlen($val_index) != 6)
				$err .= ' - � ���� "�������� ������" ����������� ���� 6 ����.<br />';
		}else
			$val_index = '';
		
		// �����
		if (isset($_POST['ustreet']) && ($_POST['ustreet']!='')){
			$val_street = $_POST['ustreet'];
			if (!CheckStr(4, $val_street, 126, false))
				$err .= ' - ���� "�����" ��������� ���������� ����� (����������� 126 ��������).<br />';
			if (!preg_match("/^[a-zA-Z�-��-߸�0-9 .,()\"\-��]+$/", $val_street))
				$err .= ' - "�����" ����� �������� ������ �� ����, ����, �������, ����, �������, �����, �������.<br />';
		}else
			$err .= ' - �� ��������� ���� "�����".<br />';
		
		// ���
		if (isset($_POST['uhouse']) && ($_POST['uhouse']!='')){
			$val_house = $_POST['uhouse'];
			if (!CheckStr(4, $val_house, 30, false))
				$err .= ' - ���� "���" ��������� ���������� ����� (����������� 30 ��������).<br />';
			if (!preg_match("/^[a-zA-Z�-��-߸�0-9 .,\-]+$/", $val_house))
				$err .= ' - "���" ����� �������� ������ �� ����, ����, �������, ����, �����, �������.<br />';
		}else
			$val_house = '';
		
		// ����
		if (isset($_POST['uoffice']) && ($_POST['uoffice']!='')){
			$val_office = $_POST['uoffice'];
			if (!CheckStr(4, $val_office, 15, false))
				$err .= ' - ���� "����" ��������� ���������� ����� (����������� 15 ��������).<br />';
			if (!preg_match("/^[a-zA-Z�-��-߸�0-9 .,\-]+$/", $val_office))
				$err .= ' - "����" ����� �������� ������ �� ����, ����, �������, ����, �����, �������.<br />';
		}else
			$val_office = '';
		
		// ����������� ����
		if (isset($_POST['uaya']) && ($_POST['uaya']!='')){
			$val_aya = $_POST['uaya'];
			if (!CheckStr(4, $val_aya, 15, false))
				$err .= ' - ���� "����������� ����" ��������� ���������� ����� (����������� 15 ��������).<br />';
			if (!preg_match("/^[a-zA-Z�-��-߸�0-9 .,\-]+$/", $val_aya))
				$err .= ' - "����" ����� �������� ������ �� ����, ����, �������, ����, �����, �������.<br />';
		}else
			$val_aya = '';
		
		if ($err == ''){
			$now = date('Y-m-d H:i:s');
			// �������� �������� �������
			$qRes = SqlQuery("Select `type`, `name` From `LOCATION` Where `region`='".mysql_real_escape_string($val_region)."' and `lvl`='1' and `deleted`='0';");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$region_name = $Row[1].' '.$Row[0];
			}else
				require_once 'page404.php';
			@mysql_free_result($qRes);
			
			// �������� �������� ������
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
					if ($val_city == '�����-���������' || $val_city == '������'){
						$city_name = $city_name_db = '';
					}
				}else{
					$city_name = $val_city.', ';
					$city_name_db = $val_city;
				}
			}
						
			/*
			// ������� �� ������ (��� �������� � ���� ��������), ������ ��� ���������
			if ($Client_address[0] > 0){
				$qRes = SqlQuery("Select * From `CLIENTS` Where `region`='".mysql_real_escape_string($Client_address[0])."';");
				if (mysql_num_rows($qRes) < 2){
					SqlQuery("Delete From `REGIONS` Where `id`='".mysql_real_escape_string($Client_address[0])."';");
				}
				@mysql_free_result($qRes);
			}
			
			// ���������� ������� � REGIONS, ���� �����������
			$qRes = SqlQuery("Select * From `REGIONS` Where `id`='".mysql_real_escape_string($val_region)."';");
			if (mysql_num_rows($qRes) === 0){
				SqlQuery("Insert Into `REGIONS` Set `id`='".mysql_real_escape_string($val_region)."', `name`='".mysql_real_escape_string($region_name)."', `name_rod`='".mysql_real_escape_string($region_name)."';");
			}
			@mysql_free_result($qRes);
			
			// ������� �� ����� (��� �������� � ���� �������), ������ ��� ���������
			if ($Client_address[1] > 0){
				$qRes = SqlQuery("Select * From `CLIENTS` Where `city`='".mysql_real_escape_string($Client_address[1])."';");
				if (mysql_num_rows($qRes) < 2){
					SqlQuery("Delete From `CITIES` Where `id`='".mysql_real_escape_string($Client_address[1])."';");
				}
				@mysql_free_result($qRes);
			}
			
			// ���������� ������ � CITIES, ���� �����������
			$qRes = SqlQuery("Select * From `CITIES` Where `id`='".mysql_real_escape_string($val_city_id)."';");
			if (mysql_num_rows($qRes) === 0){
				SqlQuery("Insert Into `CITIES` Set `id`='".mysql_real_escape_string($val_city_id)."', `region`='".mysql_real_escape_string($val_region)."', `is_capital`='".mysql_real_escape_string($is_capital)."', `name`='".mysql_real_escape_string($city_name2)."', `name_rod`='".mysql_real_escape_string($city_name2)."';");
			}
			@mysql_free_result($qRes);
			*/
			
			// ��������� ������ �����
			$address_db  = $region_name.', '.$city_name.$val_street;
			$address_db .= ($val_house != '') ? ', �.'.$val_house : '';
			$address_db .= ($val_office != '') ? ', ��.'.$val_office : '';
			$address_db .= ($val_aya != '') ? ', �/� '.$val_aya : '';
			
			// ��������� �������� ������ ��������
			SqlQuery("Update `CLIENTS` Set `region`='".mysql_real_escape_string($val_region)."', `city`='".mysql_real_escape_string($city_db)."', `city_name`='".mysql_real_escape_string($city_name_db)."', `index`='".mysql_real_escape_string($val_index)."', `street`='".mysql_real_escape_string($val_street)."', `house`='".mysql_real_escape_string($val_house)."', `office`='".mysql_real_escape_string($val_office)."', `aya`='".mysql_real_escape_string($val_aya)."', `address`='".mysql_real_escape_string($address_db)."', `coord`='',`map_zoom`='0', `status`='1', `udate`='".$now."' Where `id`='".mysql_real_escape_string($user_client_id)."';");
			
			// �������� ���������� ������ ��������
			$log_new = 'region - '.$val_region.'; city - '.$city_db.'; address - '.$address_db;
			SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='0', `action`='1', `old`='".mysql_real_escape_string($log_old)."', `new`='".mysql_real_escape_string($log_new)."', `adate`='".$now."';");
			
			// ������� �� �������� ���������� ������� ������������
			header('Location: /kabinet/about');
		}else{
			$val_city   = htmlspecialchars($val_city, ENT_QUOTES, 'cp1251');
			$val_index  = htmlspecialchars($val_index, ENT_QUOTES, 'cp1251');
			$val_street = htmlspecialchars($val_street, ENT_QUOTES, 'cp1251');
			$val_house  = htmlspecialchars($val_house, ENT_QUOTES, 'cp1251');
			$val_office = htmlspecialchars($val_office, ENT_QUOTES, 'cp1251');
			$val_aya    = htmlspecialchars($val_aya, ENT_QUOTES, 'cp1251');
		}
	}

}else
	header('Location: /enter?link=/address-change');

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
					pattern: /^[a-zA-Z�-��-߸�0-9 .,\"\-��]+$/i
				},
				uindex: {
					integer: true,
					rangelength: [6, 6]
				},
				ustreet: {
					required: true,
					pattern: /^[a-zA-Z�-��-߸�0-9 .,()\"\-��]+$/i
				},
				uhouse: {
					pattern: /^[a-zA-Z�-��-߸�0-9 .,\-]+$/i
				},
				uoffice: {
					pattern: /^[a-zA-Z�-��-߸�0-9 .,\-]+$/i
				},
				uaya: {
					pattern: /^[a-zA-Z�-��-߸�0-9 .,\-]+$/i
				}
			},
			messages: {
				uregion: '������� ������',
				ucity: {
					required: '������� �����',
					pattern: '������ �����, �����, ������, ����, �������'
				},
				uindex: {
					integer: '�������� ������ ������ �������� ������ �� ����',
					rangelength: '���������� ������ 6 ����'
				},
				ustreet: {
					required: '������� �������� �����',
					pattern: '������ �����, �����, ������, ����, �������'
				},
				uhouse: {
					pattern: '������ �����, �����, ������, ����'
				},
				uoffice: {
					pattern: '������ �����, �����, ������, ����'
				},
				uaya: {
					pattern: '������ �����, �����, ������, ����'
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
		if (id == '84') $('#ucity').val('�����-���������');
		if (id == '91') $('#ucity').val('������');
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

$active_tab = 1;
$title = '������ ������� - �������� ����� ��������';

require_once '../../includs/control/address_change.html';

?>