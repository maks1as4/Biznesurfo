<?php

require_once "../../includs/head.php";

$email = mysql_real_escape_string($_REQUEST['umail']);
$qRes  = SqlQuery("Select `id` From `MEMBERS` Where `email`='".$email."' Limit 1;");
$count = mysql_num_rows($qRes);
@mysql_free_result($qRes);

if ($count === 1)
	echo 'true';
else
	echo 'false';

?>