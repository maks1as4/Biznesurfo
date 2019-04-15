<?php
session_start () || die ('��� cookeis ���������� ������ �� ��������.');
if (count ($_GET) > 0)
	die ('���-�� �� ���...');
@require_once '../../funcs/config.php';
@require_once '../../funcs/dbconnect.php';
@require_once "../../funcs/check_funcs.php";
$SecCode = -1;
$ukind = 0;
if ( (isset ($_SESSION ['SecretCode'])) && (CheckInteger($_SESSION['SecretCode'], 1, 10000)) )
	$SecCode = $_SESSION ['SecretCode'];
if ( (isset ($_SESSION ['UserKind'])) && (CheckInteger($_SESSION['UserKind'], 1, 10000)) )
	$ukind = $_SESSION ['UserKind'];
if (!(aConnectDB (false, $ukind, 'exists', true, $SecCode) ) ) 
	die ('Please wait. Server is busy...');
switch ($ukind) {
	case 1:	$lg = 'manage'; break;
	case 2: $lg = 'lookstat'; break;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>������������</title>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<link href="manags.css" rel="stylesheet" type="text/css" />
</head>
<body>

<div class="main-top"><h1>���� ������:</h1></div>

<div class="main-center">
	<div class="leftbar">
		<div class="menu"><ul>
<?php if ($lg == 'manage') { ?>
			<li><a href="cfnews.php">���������� ��������</a></li>
<?php } elseif ($lg == 'lookstat') { ?>
			<li><a href="ssclient.php">������� <span>�����������</span> ��� ��������� ����������</a></li>
			<li><a href="ssrubs.php">�������� ���������� ��������� <span>������</span> �����������</a></li>
			<li><a href="sspredrubs.php">�������� ���������� ��������� <span>������</span> ����������� � ������ <span>"�����������"</span></a></li>
			<li><a href="ssprices.php">�������� ���������� ��������� <span>�����-������</span> ��������</a></li>
			<li><a href="ssdowns.php">�������� ���������� <span>���������� �����-������</span> ��������</a></li>
			<li><a href="sscarts.php">�������� ���������� ��������� <span>�������� �����������</span></a></li>
			<li><a href="ssprintc.php">�������� ���������� <span>������ ��������</span> ��������</a></li>
			<li><a href="sslink.php">�������� ���������� <span>��������� �� �����</span> ��������</a></li>
			<li><a href="ssgeneral.php">�������� <span>�������</span> ���������� �� ����������</a></li>
			<li><a href="ssbanners.php">������� <span>������</span> ��� ��������� ����������</a></li>
			<li><a href="sstmoduls.php">������� <span>��������� ������</span> ��� ��������� ����������</a></li>
			<li><a href="sstopfind.php">�������� ���������� <span>���-100 ���������</span> �������� �� <span>�������</span></a></li>
			<li><a href="sstopfind.php?prd=1">�������� ���������� <span>���-100 ���������</span> �������� �� <span>������������</span></a></li>
<?php } ?>
			</ul>
		</div>
	</div>
</div>

</body>
</html>