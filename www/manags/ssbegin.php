<?php
session_start () || die ('��� cookeis ���������� ������ �� ��������.');
@require_once "../../funcs/check_funcs.php";
@require_once "../../funcs/config.php"; 
@require_once "../../funcs/dbconnect.php"; 
//@require_once "../../funcs/biz_funcs.php"; // ������ ��� �������� ������� ����������
if (isset($woAuth)) {
	if (!(ConnectDB () ) ) 
		Show_Critical_Error ('���������� �����������');
} else {
	$SecCode = -1;
	if ( (isset ($_SESSION ["SecretCode"])) && (CheckInteger($_SESSION["SecretCode"], 1, 10000)) )
		$SecCode = $_SESSION ["SecretCode"];
	if (!(aConnectDB (false, 2, 'exists', true, $SecCode) ) ) 
		Show_Critical_Error ('���������� �����������');
}
?>