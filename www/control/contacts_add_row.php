<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	if (isset($_GET['type']) && !empty($_GET['type'])){
		
		$type = $_GET['type'];
		switch ($type){
			case 'phone':{
				$val_code = '';
				$val_phone = '';
				$title_suffix = '�������';
				$table_name = 'CLIENT_PHONES';
				$limit = 10;
				break;
			}
			case 'email':{
				$val_mail = '';
				$title_suffix = '����������� �����';
				$table_name = 'CLIENT_EMAILS';
				$limit = 5;
				break;
			}
			case 'site':{
				$val_site = '';
				$title_suffix = '����';
				$table_name = 'CLIENT_SITES';
				$limit = 5;
				break;
			}
			default:{
				require_once 'page404.php';
			}
		}
		
		// ���������� ������� � ��������� �������
		$qRes = SqlQuery("Select count(*) From `".mysql_real_escape_string($table_name)."` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
		if (mysql_num_rows($qRes) > 0){
			$Row = mysql_fetch_row($qRes);
			$count = $Row[0];
		}else
			$count = 0;
		@mysql_free_result($qRes);
		
		// ���� ����� ��������, �� �������������
		if ($count >= $limit) header('Location: /kabinet/contacts-change');
		
		$err = '';
		
		if (count($_POST)>0){
			if (!isset($_POST['btmes']))
				require_once 'page404.php';
			if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
				require_once 'page404.php';
			
			switch ($type){
				case 'phone':{
#---------------- ������� -------------------

					// ���
					if (isset($_POST['u1code']) && ($_POST['u1code']!='')){
						$val_code = $_POST['u1code'];
						if (!CheckStr(4, $val_code, 5, false))
							$err .= ' - ���� "��� ������" ��������� ���������� ����� (����������� 5 ����).<br />';
						if (!CheckIntegerUnsign($val_code))
							$err .= ' - � ���� "��� ������" ����������� ������ ���� ���� (��� ������������).<br />';
					}else
						$err .= ' - �� ��������� ���� "��� ������".<br />';
					
					// �����
					if (isset($_POST['u1phone']) && ($_POST['u1phone']!='')){
						$val_phone = $_POST['u1phone'];
						if (!CheckStr(4, $val_phone, 7, false))
							$err .= ' - ���� "�������" ��������� ���������� ����� (����������� 7 ����).<br />';
						if (!CheckIntegerUnsign($val_phone))
							$err .= ' - � ���� "�������" ����������� ������ ���� ���� (��� ������������).<br />';
					}else
						$err .= ' - �� ��������� ���� "�������".<br />';
					
					// ��� + ����� �� �������
					if ((isset($_POST['u1code']) && ($_POST['u1code']=='')) && (isset($_POST['u1phone'])&&($_POST['u1phone']=='')))
						$err = ' - ������� �������.<br />';
					
					// �������� ����������� ���� � �������� (��� + �����), 10 ��� �������� � ��������, 11 ��� ����������� 8-800
					if ($err == ''){
						$cp_length = strlen($val_code.$val_phone);
						if ($val_code == '8800'){
							if ($cp_length != 11)
								$err .= ' - ������������ ���������� ���� � ��������, ��� ����������� ������ 8-800... ���������� 11 ���� (��� + �����).<br />';
						}else{
							if ($cp_length != 10)
								$err .= ' - ������������ ���������� ���� � ��������, ���������� 10 ���� (��� + �����).<br />';
						}
					}
					
					if ($err == ''){
						// ��������� ������������ ���������� ����� �������� ��������
						$qRes = SqlQuery("Select max(`sort_order`) From `CLIENT_PHONES` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
						if (mysql_num_rows($qRes) > 0){
							$Row = mysql_fetch_row($qRes);
							$max_phones = $Row[0];
						}else
							require_once 'page404.php';
						@mysql_free_result($qRes);
						// ��������� ������ � ��
						SqlQuery("Insert Into `CLIENT_PHONES` Set `id_client`='".mysql_real_escape_string($user_client_id)."', `full_phone`='".mysql_real_escape_string(getPhoneFormat($val_code, $val_phone))."', `code`='".mysql_real_escape_string($val_code)."', `phone`='".mysql_real_escape_string($val_phone)."', `city_code`='', `sort_order`='".mysql_real_escape_string($max_phones+1)."';");
						// ������� �� �������� ���������� ������� ������������
						header('Location: /kabinet/contacts-change');
					}else{
						$val_code  = htmlspecialchars($val_code, ENT_QUOTES, 'cp1251');
						$val_phone = htmlspecialchars($val_phone, ENT_QUOTES, 'cp1251');
					}

#--------------------------------------------
					break;
				}
				case 'email':{
#----------------- Email --------------------

					// ����������� �����
					if (isset($_POST['umail']) && ($_POST['umail']!='')){
						$val_mail = $_POST['umail'];
						if (strlen($val_mail) > 100)
							$err .= ' - Email ������� �������, ����������� 100 ��������.<br />';
						if (!CheckEmail($val_mail))
							$err .= ' - E-mail ����� ������������ ������.<br />';
					}else
						$err .= ' - ������� ����� ����������� �����.<br />';
					
					if ($err == ''){
						// ��������� ������������ ���������� ����� ����� ��������
						$qRes = SqlQuery("Select max(`sort_order`) From `CLIENT_EMAILS` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
						if (mysql_num_rows($qRes) > 0){
							$Row = mysql_fetch_row($qRes);
							$max_emails = $Row[0];
						}else
							$max_emails = 0;
						@mysql_free_result($qRes);
						// ��������� ������ � ��
						SqlQuery("Insert Into `CLIENT_EMAILS` Set `id_client`='".mysql_real_escape_string($user_client_id)."', `email`='".mysql_real_escape_string($val_mail)."', `sort_order`='".mysql_real_escape_string($max_emails+1)."';");
						// ������� �� �������� ���������� ������� ������������
						header('Location: /kabinet/contacts-change');
					}else{
						$val_mail = htmlspecialchars($val_mail, ENT_QUOTES, 'cp1251');
					}

#--------------------------------------------
					break;
				}
				case 'site':{
#------------------ ���� --------------------

					// ����
					if (isset($_POST['usite']) && ($_POST['usite']!='')){
						$val_site = $_POST['usite'];
						if (strlen($val_site) > 100)
							$err .= ' - ���� ������� �������, ����������� 100 ��������.<br />';
						if (!CheckSite($val_site))
							$err .= ' - ���� ����� ������������ ������.<br />';
					}else
						$err .= ' - ������� ����� �����.<br />';
					
					if ($err == ''){
						// ��������� ������������ ���������� ����� ����� ��������
						$qRes = SqlQuery("Select max(`sort_order`) From `CLIENT_SITES` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
						if (mysql_num_rows($qRes) > 0){
							$Row = mysql_fetch_row($qRes);
							$max_sites = $Row[0];
						}else
							$max_sites = 0;
						@mysql_free_result($qRes);
						// ������� http(s)
						$val_site = preg_replace("#^https?://#i", '', ltrim($val_site));
						// ��������� ������ � ��
						SqlQuery("Insert Into `CLIENT_SITES` Set `id_client`='".mysql_real_escape_string($user_client_id)."', `site`='".mysql_real_escape_string($val_site)."', `sort_order`='".mysql_real_escape_string($max_sites+1)."';");
						// ������� �� �������� ���������� ������� ������������
						header('Location: /kabinet/contacts-change');
					}else{
						$val_site = htmlspecialchars($val_site, ENT_QUOTES, 'cp1251');
					}

#--------------------------------------------
					break;
				}
			}
		}
		
	}else
		require_once 'page404.php';
	
}else
	header('Location: /enter?link=/contacts-change');

$jFiles = '<script type="text/javascript" src="/js/validate/jquery.validate.min.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/validate/additional-methods.min.js"></script>'."\n";
switch ($type){
	case 'phone':{
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				u1code: {
					required: true,
					integer: true,
					rangelength: [3, 5]
				},
				u1phone: {
					required: true,
					integer: true,
					rangelength: [5, 7]
				}
			},
			messages: {
				u1code: {
					required: '��� ������ ��� �������� ��������� �� ������ ���� ������',
					integer: '��� ������ ��� �������� ��������� ����� �������� ������ �� ����',
					rangelength: '��� ������ ��� �������� ��������� ������ ���� � ��������� �� 3 �� 5 ����'
				},
				u1phone: {
					required: '����� �������� �� ������ ���� ������',
					integer: '����� �������� ����� �������� ������ �� ���� (��� ������ ����������)',
					rangelength: '����� �������� ������ ���� � ��������� �� 5 �� 7 ����'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});
});

EoL;
		break;
	}
	case 'email':{
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				umail: {
					required: true,
					email2: true
				}
			},
			messages: {
				umail: {
					required: '������� email',
					email2: 'email ����� ������������ ������'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});
});

EoL;
		break;
	}
	case 'site':{
$jqAdd = <<<EoL
$(document).ready(function() {
	$('#forms').each(function() {
		$(this).validate({
			focusInvalid: false,
			rules: {
				usite: {
					required: true,
					url2: true
				}
			},
			messages: {
				usite: {
					required: '������� ����',
					url2: '�������� ����� ����� ������������ ������'
				}
			},
			errorPlacement: function(error, element) {
				var element_id = element.attr('id');
				error.appendTo($('#err-'+element_id));
			}
		});
	});
});

EoL;
		break;
	}
}

$active_tab = 1;
$title = '������ ������� - �������� '.$title_suffix;

require_once '../../includs/control/contacts_add_'.$type.'.html';

?>