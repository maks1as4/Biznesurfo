<?php

require_once '../../includs/head.php';
require_once '../../funcs/refresh_counts.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	refreshCounts();
	header('Location: /management/adm');
	
}else
	header('Location: /enter');

?>