<?php
@require_once '../includs/head.php';

$cl_opf = '';
$cl_fio = '';
$cl_con_phone = '';
for ($i=1;$i<6;$i++) {
	$cl_pos{$i} = '';
	$cl_cost{$i} = '';
}
$rows_exists = false;
$file_exists = false;
$fl_name = '';
$err 	 = '';
if (count ($_POST) > 0) {
	if (!(isset ($_POST['btmes']))) die ('�� ������?');
	if ((isset ($_SERVER['HTTP_REFERER'])) && (!CheckRefer ($_SERVER['HTTP_REFERER']))) die ('������?');
	if(!isset($_POST['ucode'])||($_POST['ucode']!=$_SESSION['umm_last_cim_code'])){
		$err .= '�� ������ ��� ������ ������� ��� ������ �� �����.<br />';
	}	
	if (count($_FILES)==1) {
		$valid_types =  array('doc','xls','txt','docx', 'xlsx');
		$tarDir = $_SERVER['DOCUMENT_ROOT'].'price/';
//		$tarDir = $_SERVER['DOCUMENT_ROOT'].'/price/';
		foreach ($_FILES as $fl) {
			if ($fl['error']<4) {
				$file_exists = true;
				if (($fl['error']==0) && (is_uploaded_file($fl['tmp_name'])) ) { // ���� ��������
					$tfn = $fl['tmp_name'];
					$ext = substr ($fl['name'], 1 + strrpos ($fl['name'], '.') );
					if (!in_array($ext, $valid_types)) 
						$err .= '������������ ������ ����� '.$fl['name'].'<br />';
					elseif ($fl['size']<512001) {
							$fl_name = $tarDir.basename($tfn).'.'.$ext;
							@chmod ($tarDir, 0755);
							if (!(@move_uploaded_file($tfn, $fl_name)))
							  $err .= '������ ��� ����������� ����� '.$fl['name'].'<br />';
						} else {
							$err .= '�������� ������ ����� '.$fl['name'].'<br />';
						}
				} else	$err .= '������ ��� �������� ����� '.$fl['name'].' ���: '.$fl['error'].'<br />';
			}
		}
	}
	$Enum = array('������', '������', '�������', '���������', '�����');
	for ($i=1;$i<6;$i++) {
		if ((isset ($_POST["uname$i"]) && ($_POST["uname$i"] != ''))) {
			$cl_pos{$i} = $_POST["uname$i"];
			if (!CheckStr (4, $cl_pos{$i}, 512, false) )
				$cl_pos{$i} = substr($cl_pos{$i}, 0, 512);
			if ((isset ($_POST["ucost$i"]) && ($_POST["ucost$i"] != ''))) {
				$cl_cost{$i} = $_POST["ucost$i"];
				if (CheckStr (4, $cl_cost{$i}, 48, false) ) 
					$cl_cost{$i} = substr($cl_cost{$i}, 0, 48);
				$rows_exists = true;
			} else	$err .= '�� ������� "����" '.$Enum[$i-1].' �������.<br />';
		}
	}
	if ((isset ($_POST['ufullname']) && ($_POST['ufullname'] != ''))) {
		$cl_opf = $_POST['ufullname'];
		if (!CheckStr (4, $cl_opf, 220, false) )
			$err .= '������� ������� �������� ����������� (����������� 220 ��������).<br />';
	} else	$err .= '�� ������� "���, ������ �������� �����������".<br />';
	if ((isset ($_POST['ufio']) && ($_POST['ufio'] != ''))) {
		$cl_fio = $_POST['ufio'];
		if (!CheckStr (4, $cl_fio, 127, false) )
			$cl_fio = substr($cl_fio, 0, 127);
	} else	$err .= '�� ��������� ���� "�������, ���, ��������".<br />';
	if ((isset ($_POST['uconphone']) && ($_POST['uconphone'] != ''))) {
		$cl_con_phone= $_POST['uconphone'];
		if (!CheckStr (4, $cl_con_phone, 20, false) )
			$err .= '���� "���������� �������" ��������� ���������� ����� (����������� 20 ��������).<br />';
	} else	$err .= '�� ��������� ���� "���������� �������".<br />';
	if (get_magic_quotes_gpc()) {
		$cl_opf = stripslashes($cl_opf);
		$cl_fio = stripslashes($cl_fio);
		$cl_con_phone = stripslashes($cl_con_phone);
		for ($i=1;$i<6;$i++) {
			$cl_pos{$i} = stripslashes($cl_pos{$i});
			$cl_cost{$i} = stripslashes($cl_cost{$i});
		}
	}
	if (!($file_exists || $rows_exists))
		$err = '�� �� ���������� �����-���� � �� ������� �� ����� �������.<br>'.$err;
	if ($err=='') {
		@require_once "../includs/class.mailer.php";
		$mail = new Mailer();
		$mail->From     = 'webrobot@biznesurfo.ru';
		$mail->FromName = 'Site BiznesUrfo Robot';
		$mail->Subject  = '��������������� �.�. ������ �� ���������� �����-�����';
		$err = '� ����� www.biznesurfo.ru ������ ������ �� ���������� �����-����� � ��� ������� ��� ���������� �� ��������� ����� �������. ��� ���� ���� �������:'." \n\n".'���, ������ �������� �����������: '.$cl_opf." \n\n".'��� ����������� ����: '.$cl_fio." \n".'���������� ������� ����: '.$cl_con_phone." \n\n";
		if ($file_exists) {
			$err .= '��� �����-���� �������� �� �������� � ������.'."\n\n";
			$mail->AddAttachment ($fl_name , 'Price.'.$ext);
		}
		if ($rows_exists) {
			$err .= '������� ��� ����������:'."\n";
			for ($i=1;$i<6;$i++) {
				$err .= $i.'. ������������:"'.$cl_pos{$i}.'"; ����:"'.$cl_cost{$i}.'"'."\n";
			}
		}
		$err .= " \n\n ----\n".'����� ������ ������ - '.date("H:i:s d.m.Y");
		$mail->Body     = $err;
		$mail->AddAddress('reklama@biznesurfo.ru');
		if ($mail->Send()) 
			$err = '������ ����������';
		else	$err = $mail->ErrorInfo;		
		if ($file_exists) {
			unlink ($fl_name);
			@chmod ($tarDir, 0555);
		}
	} else	$err = '������: '.$err;
}
if (count ($_GET) > 0) {
	if ( (isset ($_GET['rcid'])) && ($_GET['rcid'] != '')) {
		$flCity = $_GET['rcid'];
		$s = $flCity[0];
		$idReg = substr($flCity, 1);
		if (!((($s=='r') || ($s=='c')) && (CheckIntegerZeroUnsign($idReg)))) {
			$idReg = 0;
			$flCity = '';
		}
		$isReg = ($s=='r');
	}
}

$pagNam = 'blank/price';
if ($flCity!='')	$findParStr = '?rcid='.$flCity;
$Cities = GetCities();
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$jqAdd = "\n$('#podpiska').jqTransform({imgPath:'js/jqtransform/img/'});\n";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = '�������';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '���������� ������ �� ���������� �����-�����';
$crumbsQty = 2;
$title = '�������� �����-���� ��������: �������� ��������������� ������� � �����';
$Expos = GetExposBlock();
$expoQty = count($Expos);

@require_once '../includs/price_blank.html'; 
?>