<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id)){
	
	$client_info = getClientInfo($user_client_id);
	
	if ($client_info['end']){
		header('Location: /kabinet/end');
		exit;
	}
	
	$qRes = SqlQuery("Select `about` From `CLIENTS_ABOUT` Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
	}else
		require_once 'page404.php';
	@mysql_free_result($qRes);
	
	$val_text = $old_text = $Row[0];
	$err = '';
	
	if (count($_POST)>0){
		if (!isset($_POST['btmes']))
			require_once 'page404.php';
		if ((isset($_SERVER['HTTP_REFERER'])) && (!CheckRefer($_SERVER['HTTP_REFERER'])))
			require_once 'page404.php';
		
		// � ��������
		if ((isset($_POST['text']) && ($_POST['text']!=''))){
			//$val_text = $_POST['text'];
			$val_text = stripslashes($_POST['text']); // -gpc-
			// ���������� ���������� ��� ����������
			require_once '../../extensions/html_purifier/HTMLPurifier.auto.php';
			// ������ ��������� � cp1251 �� utf8
			$val_text = iconv('windows-1251', 'UTF-8', $val_text);
			// ��������� ����������� ��������
			$config = HTMLPurifier_Config::createDefault();
			$config->set('HTML.Allowed', '');
			$purifier = new HTMLPurifier($config);
			$str_length = iconv_strlen($purifier->purify($val_text), 'cp1251');
			unset($config, $purifier);
			if ($str_length <= 10000){
				// ������� �������� ���� ��� ���������� � ����
				$config = HTMLPurifier_Config::createDefault();
				$config->set('HTML.Allowed', 'p,strong,b,em,i,ul,ol,li,h3,hr,table,thead,tbody,tr,td,th'); // ���������� ���� (a[href])
				//$config->set('HTML.Nofollow', true);
				//$config->set('URI.MakeAbsolute', true);
				//$config->set('URI.DefaultScheme', 'http');
				//$config->set('URI.Base', 'www.biznesurfo.ru');
				$config->set('AutoFormat.RemoveEmpty', true);
				$purifier = new HTMLPurifier($config);
				$val_text = $purifier->purify($val_text);
				// ������ ��������� � utf8 �� cp1251
				$val_text = iconv('UTF-8', 'windows-1251', $val_text);
				unset($config, $purifier);
			}else
				$err .= ' - ����� ������� �������, ��������� 10000 ��������, ����������, ��������� ����� � ���������� �����.<br />';
		}else
			$val_text = '';
		
		if ($err == ''){
			// ����� ������ ��������� - ���������� � ����
			SqlQuery("Update `CLIENTS_ABOUT` Set `about`='".mysql_real_escape_string($val_text)."', `status`='0' Where `id_client`='".mysql_real_escape_string($user_client_id)."';");
			
			// �������� ������ � ��������
			SqlQuery("Insert Into `LOG` SET `id_member`='".mysql_real_escape_string($user_id)."', `type`='1', `action`='1', `old`='".mysql_real_escape_string($old_text)."', `new`='".mysql_real_escape_string($val_text)."', `adate`='".date('Y-m-d H:i:s')."';");
			
			// ������� �� �������� ���������� ������� ������������
			header('Location: /kabinet/about');
		}else{
			$val_text  = htmlspecialchars($val_text, ENT_QUOTES, 'cp1251');
		}
	}
	
}else
	header('Location: /enter?link=/about-company-change');

$jFiles = '<script type="text/javascript" src="/js/tiny_mce/tiny_mce.js"></script>'."\n".
		  '<script type="text/javascript" src="/js/tiny.init.js"></script>'."\n";

$active_tab = 1;
$title = '������ ������� - �������� ������ � ��������';

require_once '../../includs/control/about_company_change.html';

?>