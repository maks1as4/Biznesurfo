<?php
@require_once "../../funcs/config.php";
@require_once "../../funcs/biz_funcs.php";
@require_once "../../funcs/check_funcs.php";
@require_once "../../funcs/dbconnect.php"; 
if (!(ConnectDB () ) ) 
	Show_Critical_Error ('Необходима авторизация');
$qty = 0;
$idNew = 0;
if (count ($_GET) > 0) {
	if ( (isset ($_GET['up'])) && ($_GET['up'] != '') && (CheckInteger ($_GET['up'], 1, 1000) ) ) {
		$qty = $_GET['up'];
	}
	if ( (isset ($_GET['news'])) && ($_GET['news'] != '') && (CheckIntegerUnsign ($_GET['news']) ) ) {
		$idNew = $_GET['news'];
	}
} else Show_Critical_Error ('Работает!!');
if ($qty==0)
	Show_Critical_Error ('Некорректный параметр UP');
$qRes = mysql_query ('Select N.id from NEWS N Where N.id = '.$idNew.';');
if (mysql_num_rows ($qRes) != 1)
	Show_Critical_Error ('Цель не определена');
mysql_query ('update NEWS set visits=visits+'.$qty.' where id='.$idNew.';');

$nQty = 0;
$qRes = mysql_query ('select visits from NEWS where id='.$idNew.';');
if (($qRes)&&($Rows = mysql_fetch_row($qRes))) {
	$nQty = $Rows[0];
}
@mysql_free_result($qRes);

?>
<html>
<head>
<title>Работаем с новостями</title>
<meta http-equiv="content-type" content="text/html; charset=windows-1251">
</head>
<style type="text/css">
</style>
<body>
<h1>Новость номер <?php echo($idNew); ?></h1>
<h2>Добавлено <?php echo($qty); ?> показов</h2>
<h3>Стало <?php echo($nQty); ?></h3>
</body>
</html>