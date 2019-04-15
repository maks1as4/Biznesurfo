<?php

require_once "../../includs/head.php";

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
	
	$exception_words = array('www', 'mail', 'my-sql', 'mysql', 'mysqli', 'php', 'pdo', 'admin', 'administrator', 'moder', 'moderator', 'root', 'test');

	$url3 = mysql_real_escape_string($_REQUEST['url3']);
	$qRes1 = SqlQuery("Select `id` From `CLIENTS` Where `translit`='".$url3."' Limit 1;");
	$qRes2 = SqlQuery("Select `id` From `RC_TRANSLIT` Where `translit`='".$url3."' Limit 1;");
	$count1 = mysql_num_rows($qRes1);
	$count2 = mysql_num_rows($qRes2);
	@mysql_free_result($qRes1);
	@mysql_free_result($qRes2);

	if ($count1 === 1 || $count2 === 1)
		echo 'false';
	elseif (in_array($url3, $exception_words))
		echo 'false';
	else
		echo 'true';

}

?>