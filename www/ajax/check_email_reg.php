<?php

require_once "../../includs/head.php";

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){

	$email = mysql_real_escape_string($_REQUEST['umail']);
	$qRes1 = SqlQuery("Select `id` From `REGISTRATION_tmp` Where `email`='".$email."' Limit 1;");
	$qRes2 = SqlQuery("Select `id` From `MEMBERS` Where `email`='".$email."' Limit 1;");
	$count1 = mysql_num_rows($qRes1);
	$count2 = mysql_num_rows($qRes2);
	@mysql_free_result($qRes1);
	@mysql_free_result($qRes2);

	if ($count1 === 1 || $count2 === 1)
		echo 'false';
	else
		echo 'true';

}

?>