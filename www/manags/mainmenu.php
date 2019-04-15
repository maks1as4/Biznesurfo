<?php
session_start () || die ('Без cookeis дальнейшая работа не возможна.');
if (count ($_GET) > 0)
	die ('Что-то не так...');
if (count ($_POST) > 0) {
	if ( !(isset ($_POST['btcom'])) || ($_POST['btcom'] <> 'Дальше') ) 
		die ('Как так?!?!!');
	if ( !(isset ($_POST['uword'])) || ($_POST['uword'] == '') ) 
		die ('Не указан пароль');
	if ( !(isset ($_POST['uname'])) || ($_POST['uname'] == '') ) 
		die ('Не указан логин');
	$lg = $_POST['uname'];
	$pw = $_POST['uword'];
	if (!( ($lg == 'manage') || ($lg == 'servman') || ($lg == 'lookstat') ))
		die ('Не верный пароль... Извините.');
	@require_once '../../funcs/config.php';
	@require_once '../../funcs/dbconnect.php';
	$SecCode = 0;
	switch ($lg) {
		case 'manage':		$ukind = 1; break;
		case 'lookstat':	$ukind = 2; break;
		default:		$ukind = 0;
	}
	if (!(aConnectDB (true, $ukind, $pw, true, $SecCode) ) ) 
		if ($SecCode==0)
			die ('Не правильный пароль');
		else	die ('Необходима авторизация');
	@mysql_close (); 
	$_SESSION ['SecretCode'] = $SecCode;
	$_SESSION ['UserKind'] = $ukind;
	header('Location: http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/menu.php');
}
die ('Please wait...');
?>