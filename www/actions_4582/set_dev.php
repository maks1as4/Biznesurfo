<?php

require_once '../../funcs/config.php';

if (($_SERVER['REMOTE_ADDR'])&&($_SERVER['REMOTE_ADDR']=='79.172.27.132')) {
	if (!isset($_GET['delete']))
		setcookie('iamdeveloper','1337',time()+31536000,'/',$cookieHost);		// куки на год
	else
		setcookie('iamdeveloper','1337',1,'/',$cookieHost);
	header('HTTP/1.1 302 Moved Temporarily');
	header('Location: /');
} else
	echo '<b>EPIC FAIL</b>';
?>