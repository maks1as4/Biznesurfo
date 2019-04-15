<?php

require_once '../../includs/head.php';

if (isset($user_id) && CheckIntegerUnsign($user_id) && $user_role == 'adm'){
	
	// проверка информации клиентов
	$qRes = SqlQuery("Select * From `CLIENTS` C left join `CLIENTS_ABOUT` CA on C.`id`=CA.`id_client` Where (C.`status`='1' or CA.`status`='1') and C.`address`!='';");
	$count_clients_change = mysql_num_rows($qRes);
	@mysql_free_result($qRes);
	
	// неназначенная карта
	$qRes = SqlQuery("Select * From `CLIENTS` C Where (C.`coord`='' or C.`map_zoom`='0') and C.`address`!='';");
	$count_clients_map = mysql_num_rows($qRes);
	@mysql_free_result($qRes);
	
	// логотипы компаний
	$qRes = SqlQuery("Select * From `CLIENTS` C Where C.`status_logo`='1' and C.`logo`!='' and C.`address`!='';");
	$count_logo_change = mysql_num_rows($qRes);
	@mysql_free_result($qRes);
	
	// добавленные, измененные товары
	$qRes = SqlQuery("Select * From `STR` Where `status`!='0';");
	$count_products_change = mysql_num_rows($qRes);
	@mysql_free_result($qRes);
	
	// добавленные, измененные новости
	$qRes = SqlQuery("Select * From `CLIENT_NEWS` Where `status`!='0';");
	$count_news_change = mysql_num_rows($qRes);
	@mysql_free_result($qRes);
	
	// компании с неопознаными городами
	$qRes = SqlQuery("Select * From `CLIENTS` Where `city_name`!='';");
	$count_city_unknown = mysql_num_rows($qRes);
	@mysql_free_result($qRes);
	
	// получение доступа
	$qRes = SqlQuery("Select * From `REGISTRATION_request` Where `checked`='0';");
	$count_get_access = mysql_num_rows($qRes);
	@mysql_free_result($qRes);
	
}else
	header('Location: /enter');

$title = 'Панель управления';
require_once '../../includs/manage/a_index.html';

?>