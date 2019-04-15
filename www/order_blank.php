<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$cl_name = '';
$cl_work = 0;
$cl_region = '';
$cl_raion = '';
$cl_index = '';
$cl_city = '';
$cl_street = '';
$cl_house = '';
$cl_office = '';
$cl_aya = '';
$cl_phone = '';
$cl_mail = '';
$cl_site = '';
$cl_fio = '';
$cl_post = '';
$cl_src = '';
$cl_what = true;
$err = '';
if (count($_POST)>0) {
	if (!(isset($_POST['btmes'])))
		die('�� ������?');
	if ((isset($_SERVER['HTTP_REFERER']))&&(!CheckRefer($_SERVER['HTTP_REFERER'])))
		die('������?');
	if (!isset($_POST['ucode'])||($_POST['ucode']!=$_SESSION['umm_last_cim_code'])) {
		$err .= '�� ������ ��� ������ ������� ��� ������ �� �����.<br />';
	}
	$cl_what = (isset($_POST['what'])&&($_POST['what']!='jrn'));
	if ((isset($_POST['uname'])&&($_POST['uname']!=''))) {
		$cl_name = $_POST['uname'];
		if (!CheckStr(4, $cl_name, 511, false))
			$err .= '������� ������� �������� ����������� (����������� 220 ��������).<br />';
	} else
		$err .= '�� ������� �������� �����������.<br />';
	if ((isset($_POST['uwork'])&&($_POST['uwork']!=''))) {
		if (!CheckInteger($cl_work, 0, 12))
			$cl_work = 13;
		else
			$cl_work = $_POST['uwork'];
	} else
		$err .= '�� ������� ����� ������������ �����������.<br />';
	if ((isset($_POST['uregion'])&&($_POST['uregion']!=''))) {
		$cl_region = $_POST['uregion'];
		if (!CheckStr(4, $cl_region, 255, false))
			$err .= '���� "������" ��������� ���������� ����� (����������� 254 �������).<br />';
	} else
		$err .= '�� ��������� ���� "������".<br />';
	if ((isset($_POST['uraion'])&&($_POST['uraion']!=''))) {
		$cl_raion = $_POST['uraion'];
		if (!CheckStr(4, $cl_raion, 255, false))
			$err .= '���� "�����" ��������� ���������� ����� (����������� 254 �������.)<br />';
	} else
		$err .= '�� ��������� ���� "�����".<br />';
	if ((isset($_POST['uindex'])&&($_POST['uindex']!=''))) {
		$cl_index = $_POST['uindex'];
		if (!CheckIntegerUnsign($cl_index))
			$err .= '� ���� "�������� ������" ����������� ������ ���� ����.<br />';
	} else
		$err .= '�� ��������� ���� "�������� ������".<br />';
	if ((isset($_POST['ucity'])&&($_POST['ucity']!=''))) {
		$cl_city = $_POST['ucity'];
		if (!CheckStr(4, $cl_city, 127, false))
			$err .= '���� "�����" ��������� ���������� ����� (����������� 126 ��������).<br />';
	} else
		$err .= '�� ��������� ���� "�����".<br />';
	if ((isset($_POST['ustreet'])&&($_POST['ustreet']!=''))) {
		$cl_street = $_POST['ustreet'];
		if (!CheckStr(4, $cl_street, 127, false))
			$err .= '���� "�����" ��������� ���������� ����� (����������� 126 ��������).<br />';
	}
	if ((isset($_POST['uhouse'])&&($_POST['uhouse']!=''))) {
		$cl_house = $_POST['uhouse'];
		if (!CheckStr(4, $cl_house, 15, false))
			$err .= '���� "���" ��������� ���������� ����� (����������� 15 ��������).<br />';
	}
	if ((isset($_POST['uoff'])&&($_POST['uoff']!=''))) {
		$cl_office = $_POST['uoff'];
		if (!CheckStr(4, $cl_office, 15, false))
			$err .= '���� "����" ��������� ���������� ����� (����������� 15 ��������).<br />';
	}
	if ((isset($_POST['uaya'])&&($_POST['uaya']!=''))) {
		$cl_aya = $_POST['uaya'];
		if (!CheckStr(4, $cl_aya, 15, false))
			$err .= '���� "����������� ����" ��������� ���������� ����� (����������� 15 ��������).<br />';
	}
	if ($cl_street=='') {
		if ($cl_aya=='')
			$err .= '���� �� �� ������� �����, ��������� ���� "����������� ����".<br />';
	} else {
		if ($cl_house=='')
			$err .= '���� �� ������� �����, ��������� ���� "���".<br />';
	}
	if ((isset($_POST['uphone'])&&($_POST['uphone']!=''))) {
		$cl_phone = $_POST['uphone'];
		if (!CheckStr(4, $cl_phone, 127, false))
			$err .= '���� "�������/����" ��������� ���������� ����� (����������� 126 ��������).<br />';
	} else
		$err .= '�� ��������� ���� "�������/����".<br />';
	if ((isset($_POST['umail'])&&($_POST['umail']!=''))) {
		$cl_mail = $_POST['umail'];
		if (!CheckEmail($cl_mail))
			$err .= 'E-mail ����� ������������ ������.<br />';
	}
	if ((isset($_POST['usite'])&&($_POST['usite']!=''))) {
		$cl_site = $_POST['usite'];
		if (!CheckStr(4, $cl_site, 63, false))
			$cl_site = substr($cl_site, 0, 63);
	}
	if ((isset($_POST['ufio'])&&($_POST['ufio']!=''))) {
		$cl_fio = $_POST['ufio'];
		if (!CheckStr(4, $cl_fio, 221, false))
			$err .= '���� "����������" ��������� ���������� ����� (����������� 220 ��������).<br />';
	} else
		$err .= '�� ��������� ���� "����������".<br />';
	if ((isset($_POST['upost'])&&($_POST['upost']!=''))) {
		$cl_post = $_POST['upost'];
		if (!CheckStr(4, $cl_post, 221, false))
			$err .= '������� ������� �������� ��������� (����������� 220 ��������).<br />';
	}
	if ((isset($_POST['usrc'])&&($_POST['usrc']!=''))) {
		$cl_src = $_POST['usrc'];
		if (!CheckStr(4, $cl_src, 512, false))
			$cl_src = substr($cl_src, 0, 512);
	}
	if (get_magic_quotes_gpc()) {
		$cl_name = stripslashes($cl_name);
		$cl_work = stripslashes($cl_work);
		$cl_region = stripslashes($cl_region);
		$cl_raion = stripslashes($cl_raion);
		$cl_index = stripslashes($cl_index);
		$cl_city = stripslashes($cl_city);
		$cl_street = stripslashes($cl_street);
		$cl_house = stripslashes($cl_house);
		$cl_office = stripslashes($cl_office);
		$cl_aya = stripslashes($cl_aya);
		$cl_phone = stripslashes($cl_phone);
		$cl_mail = stripslashes($cl_mail);
		$cl_site = stripslashes($cl_site);
		$cl_fio = stripslashes($cl_fio);
		$cl_post = stripslashes($cl_post);
		$cl_src = stripslashes($cl_src);
	}
	if ($err=='') {
		require_once "../includs/class.mailer.php";
		$mail = new Mailer();
		$mail->From = 'webrobot@biznesurfo.ru';
		$mail->FromName = 'Site BiznesUrfo Robot';
		$mail->Subject = '��������������� �.�. ������ �� ���������� ��������';
		$err = '� ����� www.biznesurfo.ru ������ ������ �� ��������. ��� ���� ���� ������� ��������� ���������:'." \n\n".'�������� �����������: '.$cl_name." \n\n".'����� ������������: ';
		switch ($cl_work) {
			case 0 : {
					$err .= '�������������, ������, �������';
					break;
				}
			case 1 : {
					$err .= '���������������� ������������';
					break;
				}
			case 2 : {
					$err .= '�������������������, ������������ �����������';
					break;
				}
			case 3 : {
					$err .= '������������ ������������������ ��������';
					break;
				}
			case 4 : {
					$err .= '������������ ���������';
					break;
				}
			case 5 : {
					$err .= '������������ � ���������� ���������';
					break;
				}
			case 6 : {
					$err .= '������������ ��� ������������� � ���';
					break;
				}
			case 7 : {
					$err .= '����������';
					break;
				}
			case 8 : {
					$err .= '������, ������������� ���������';
					break;
				}
			case 9 : {
					$err .= '����������, ���������';
					break;
				}
			case 10 : {
					$err .= '�������� � ��������� ������������';
					break;
				}
			case 11 : {
					$err .= '������� ������������';
					break;
				}
			case 12 : {
					$err .= '��������� ��������';
					break;
				}
		}
		$err .= " \n\n".'����������� ����� -'."\n".'������ (�������, ����������): '.$cl_region." \n".'�����: '.$cl_raion." \n".'�������� ������: '.$cl_index." \n".'����� (���������� �����): '.$cl_city." \n".'�����: ';
		if ($cl_street=='')
			$err .= '�� �������';
		else
			$err .= $cl_street;
		$err .= " \n".'���: ';
		if ($cl_house=='')
			$err .= '�� ������';
		else
			$err .= $cl_house;
		$err .= " \n".'����: ';
		if ($cl_office=='')
			$err .= '�� ������';
		else
			$err .= $cl_office;
		$err .= " \n".'�/�: ';
		if ($cl_aya=='')
			$err .= '�� ������';
		else
			$err .= $cl_aya;
		$err .= " \n\n".'�������: '.$cl_phone." \n".'����������� �����: ';
		if ($cl_mail=='')
			$err .= '�� �������';
		else
			$err .= $cl_mail;
		$err .= " \n".'����: ';
		if ($cl_site=='')
			$err .= '�� ������';
		else
			$err .= $cl_site;
		$err .= " \n\n".'���������� : '.$cl_fio." \n".'��������� : ';
		if ($cl_post=='')
			$err .= '�� �������';
		else
			$err .= $cl_post;
		$err .= " \n\n".'�������� : ';
		if ($cl_what==true)
			$err .= '����';
		else
			$err .= '������';
		$err .= " \n\n".'����� �� ������ � ����������� : ';
		if ($cl_src=='')
			$err .= '�� ������';
		else
			$err .= $cl_src;
		$err .= " \n\n ----\n".'����� ������ ������ - '.date("H:i:s d.m.Y");
		$mail->Body = $err;
		$mail->AddAddress('reklama@biznesurfo.ru');
		if ($mail->Send())
			$err = '������ ����������';
		else
			$err = $mail->ErrorInfo;
	} else
		$err = '������: '.$err;
}
if (count($_GET)>0) {
	if ((isset($_GET['rcid']))&&($_GET['rcid']!='')) {
		$flCity = $_GET['rcid'];
		$s = $flCity[0];
		$idReg = substr($flCity, 1);
		if (!((($s=='r')||($s=='c'))&&(CheckIntegerZeroUnsign($idReg)))) {
			$idReg = 0;
			$flCity = '';
		}
		$isReg = ($s=='r');
	}
}

$pagNam = 'subscribe';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$jqAdd = "\n$('#podpiska').jqTransform({imgPath:'js/jqtransform/img/'});\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = '�������';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '���������� �������� �� �������';
$crumbsQty = 2;
$title = '�������� �� ���������� ��������� �������';
$Expos = GetExposBlock();
$expoQty = count($Expos);

require_once '../includs/order_blank.html';

?>