<?php

require_once '../includs/head.php';

if (!isset($_SESSION['registration']['tmp_id']) || ($_SESSION['registration']['tmp_id'] == ''))
	require_once 'page404.php';

$lust_id_tmp = $_SESSION['registration']['tmp_id'];
$company_tmp = $company_exists = array();

// ������� ������ �� ��������� �������
$qRes = SqlQuery("Select `opf`, `organisation`, `full_phone`, `email` From `REGISTRATION_tmp` Where `id`='".mysql_real_escape_string($lust_id_tmp)."';");
if (mysql_num_rows($qRes) === 1){
	$company_tmp = mysql_fetch_row($qRes);
}else
	require_once 'page404.php';
@mysql_free_result($qRes);

$full_name = ($company_tmp[0] != '') ? $company_tmp[1].','.$company_tmp[0] : $company_tmp[1];
$nice_name = ($company_tmp[0] != '') ? $company_tmp[0].' �'.$company_tmp[1].'�' : $company_tmp[1];

// ������� ����������
$qRes = SqlQuery("
	Select C.`id`, C.`name`, C.`address`, CP.`full_phone` as phone
	From `CLIENTS` C
		Left join `CLIENT_PHONES` CP on C.`id`=CP.`id_client`     
		Left join `MEMBERS` M on C.`id`=M.`id_client`     
	Where (C.`name` = '".mysql_real_escape_string($full_name)."' or CP.`full_phone` = '".mysql_real_escape_string($company_tmp[2])."')
	and isnull(M.`id`)
	Group by C.`id`
	Limit 5;
");
$count_exists = mysql_num_rows($qRes);
if ($count_exists > 0){
	$i = 0;
	while ($Rows = mysql_fetch_row($qRes)){
		$company_exists[$i][0] = $Rows[0];
		@list($nm, $opf) = explode(',', $Rows[1]);
		$company_exists[$i][1] = (isset($opf)) ? $opf.' �'.$nm.'�' : $nm;
		$company_exists[$i][2] = $Rows[2];
		$company_exists[$i][3] = $Rows[3];
		$i++;
	}
	if ($count_exists > 1)
		$string1 = '�� ����� www.biznesurfo.ru ��� ���������������� ��������� ������� ��������. ���� � ������������ ������ ���� ���� ��������, �� �������� �� � ������� ������ ��������� ������. ���� � ������ ��� ����� ��������, �� ������� ������ ����������������� ����� ���������.';
	else
		$string1 = '�� ����� www.biznesurfo.ru ��� ���������������� ������� ��������. ���� ��� ���� ��������, �� ������� ������ ��������� ������. ���� ��� �� ���� ��������, �� ������� ������ ����������������� ����� ���������.';
}else
	require_once 'page404.php';
@mysql_free_result($qRes);

$jqAdd = <<<EoL

	$('div.choice').hover(function(){
		$(this).css({'background-color':'#e7e7e7'});
	},function(){
		$(this).css({'background-color':'#ffffff'});
	});


EoL;

$title = '������� ����� ������� �������� �� ����� www.biznesurfo.ru';

require_once '../includs/registration_exists.html';

?>