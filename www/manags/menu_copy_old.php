<?php
session_start () || die ('��� cookeis ���������� ������ �� ��������.');
if (count ($_GET) > 0)
	die ('���-�� �� ���...');
@require_once "../../funcs/config.php";
@require_once '../../funcs/dbconnect.php';

if (count ($_POST) > 0) {
	if ( !(isset ($_POST['btcom'])) || ($_POST['btcom'] <> '������') ) 
		die ('��� ���?!?!!');
	if ( !(isset ($_POST['uword'])) || ($_POST['uword'] == '') ) 
		die ('�� ������ ������');
	if ( !(isset ($_POST['uname'])) || ($_POST['uname'] == '') ) 
		die ('�� ������ �����');
	$lg = $_POST['uname'];
	$pw = $_POST['uword'];
	if (!( ($lg == 'manage') || ($lg == 'servman') || ($lg == 'lookstat') ))
		die ('�� ������ ������... ��������.');
	$SecCode = 0;
	switch ($lg) {
		case 'manage':		$ukind = 1; break;
		case 'lookstat':	$ukind = 2; break;
		default:		$ukind = 0;
	}
	if (!(aConnectDB (true, $ukind, $pw, true, $SecCode) ) ) 
		if ($SecCode==0)
			die ('�� ���������� ������');
		else	die ('���������� �����������');
	die('here');
	// mysql_close (); no Link?
	$_SESSION ['SecretCode'] = $SecCode;
	$_SESSION ['UserKind'] = $ukind;
	unset($_POST);
} else {
	@require_once "../../funcs/check_funcs.php";
	$SecCode = -1;
	$ukind = 0;
	if ( (isset ($_SESSION ['SecretCode'])) && (CheckInteger($_SESSION['SecretCode'], 1, 10000)) )
		$SecCode = $_SESSION ['SecretCode'];
	if ( (isset ($_SESSION ['UserKind'])) && (CheckInteger($_SESSION['UserKind'], 1, 10000)) )
		$SecCode = $_SESSION ['UserKind'];
	if (!(aConnectDB (false, $ukind, 'exists', true, $SecCode) ) ) 
		die ('Please wait. Server is busy...');
	switch ($ukind) {
		case 1:	$lg = 'manage'; break;
		case 2: $lg = 'lookstat'; break;
	}
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
			<li><a href="ssclient.php">������� ����������� ��� ��������� ����������</a></li>
			<li><a href="ssrubs.php">�������� ���������� ��������� ������ �����������</a></li>
			<li><a href="ssprices.php">�������� ���������� ��������� �����-������ ��������</a></li>
			<li><a href="ssdowns.php">�������� ���������� ���������� �����-������ ��������</a></li>
			<li><a href="sscarts.php">�������� ���������� ��������� �������� �����������</a></li>
			<li><a href="ssprintc.php">�������� ���������� ������ �������� ��������</a></li>
			<li><a href="sslink.php">�������� ���������� ��������� �� ����� ��������</a></li>
			<li><a href="ssgeneral.php">�������� ������� ���������� �� ����������</a></li>			
<?php } ?>
			</ul>
		</div>
	</div>
</div>

</body>
</html>