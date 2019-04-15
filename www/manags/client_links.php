<?php
@require_once "../../funcs/config.php";
@require_once "../../funcs/biz_funcs.php";
@require_once "../../funcs/check_funcs.php";
@require_once "../../funcs/dbconnect.php"; 
if (!(ConnectDB () ) ) 
	Show_Critical_Error ('Необходима авторизация');
$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/clients.txt';
$fl = fopen($fName, 'r');
$cr = "\n\t";
?>
<html>
<head>
<title>Ссылки на карточки клиентов, локально.</title>
<meta http-equiv="content-type" content="text/html; charset=windows-1251">
</head>
<style type="text/css">
.norm {
background-color: #FFFFFF;
}
.err {
background-color: #F0A0B0;
}
.warn {
background-color: #CFC438;
}
.empt {
background-color: #EEEEDA;
}
</style>
<body>
<h1>Ссылки на карточки клиентов</h1>
<h2>Файлик /manags/clients.txt</h2>
<?php
$Clients = array();
while (!feof($fl)) {
	$ar = array();
	$s = trim(fgets($fl));
	if (strlen($s)>4) {
		$ar = explode("	", $s);
		$qRes = mysql_query ('Select A.tadd From CLIENTS_ABOUT A Where A.id = '.$ar[0].';');
		if ((mysql_num_rows ($qRes) == 1) && ($qRes)) {
			if ($Rows = mysql_fetch_row ($qRes))  
				$ar[] = $Rows[0];
			else	$ar[] = 'e';
			@mysql_free_result ($qRes);
		} else	$ar[] = 'e';
		$Clients[] = $ar;
	}
}
fclose ($fl);
?>
<table>
<?php
for($i=0;$i<count($Clients);$i++):?>
<tr><td><span style="font-size:1.5em"><?php echo($Clients[$i][0]);?></span>&nbsp;&nbsp;
<a href="http://biznesurfo/company/<?php echo($Clients[$i][0]);?>.html" target="_blank" class="<?php if ($Clients[$i][2]=='e') echo('empt');?>"><?php echo($Clients[$i][1]);?></a></td>
<td><?php if ($Clients[$i][2]!='e') echo($Clients[$i][2]); else echo('&nbsp;'); ?></td>
</tr>
<?php endfor;?>
</table>
</body>
</html>