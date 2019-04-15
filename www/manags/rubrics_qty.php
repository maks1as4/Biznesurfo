<?php
@require_once "../../funcs/config.php"; 
@require_once "../../funcs/biz_funcs.php";
@require_once "../../funcs/check_funcs.php";
@require_once "../../funcs/dbconnect.php"; 
if (!(ConnectDB () ) ) 
	Show_Critical_Error ('Необходима авторизация');
$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/rubrics_qty.txt';
$fl = fopen($fName, 'r');
$cr = "\n\t";
?>
<html>
<head>
<title>Сколько в рубриках позиций (для ссылок из тематических статей)</title>
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
</style>
<body>
<h2>Проверка наличия позиций в рубриках</h2>
<table align="center" border="1" cellspacing="0" cellpadding="5" width="850">
<tr><td align="center">Номер статьи</td><td align="center">Рубрика</td><td align="center">Кол-во<br>позиций</td></tr>
<?php
$ar = array();
while (!feof($fl)) {
	$s = trim(fgets($fl));
	if ($s!='') {
		$Rubr = explode ("\t", $s);		
		$qRes = mysql_query ('Select R.new_url, SUM(N.qty) From RUBRICS R join RUBRICS_CNT N on N.rubric=R.id where R.name='."'".trim($Rubr[1])."'".'group by 1;');
		if ((mysql_num_rows ($qRes) == 1) && ($qRes)) {
			if ($Rows = mysql_fetch_row ($qRes))  {
?>
<tr>
	<td align="center"><a href="/stories/<?php echo($Rubr[0]);?>.html">Статья № <?php echo($Rubr[0]);?></a></td>
	<td align="left"><a href="/prices/<?php echo($Rows[0]);?>"><?php echo($Rubr[1]);?></a></td>
	<td align="center"<?php if($Rows[1]<15) echo(' class="err"'); elseif($Rows[1]<50) echo(' class="warn"');?>><?php echo($Rows[1]);?></td>
</tr>
<?php			}
			@mysql_free_result ($qRes);
		}
	}
}
fclose ($fl);
?>
</table>
</body>
</html>