<?php
@require_once "../../funcs/config.php";
@require_once "../../funcs/biz_funcs.php";
@require_once "../../funcs/check_funcs.php";
$n = 1;
$doUpdate = false;
if (count ($_GET) > 0) {
	if ( (isset ($_GET['id'])) && ($_GET['id'] != '') && (CheckInteger ($_GET['id'], 1, 16) ) ) {
		$n = $_GET['id'];
	}
	if  (isset ($_GET['myact'])) 
		$doUpdate = true;
}

@require_once "../../funcs/dbconnect.php"; 
if (!(ConnectDB () ) ) 
	Show_Critical_Error ('Необходима авторизация');
if ($n==2)
	$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/comp2.txt';
elseif (($n>2) && ($n<9)) {
		$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/wrds'.($n-2).'.txt';
		$n = 0;
}
elseif (($n>8) && ($n<14)) {
		$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/wout'.($n-8).'.txt';
		$n = 0;
}
elseif ($n=14) {
		$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/added.txt';
		$n = 0;
}
else	$fName = $_SERVER['DOCUMENT_ROOT'].'/manags/comp1.txt';
$fl = fopen($fName, 'r');
$cr = "\n\t";
?>
<html>
<head>
<title>Проверка актуальности запросов с контекстных объявлений. Индустрия Бизнеса.</title>
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
<h1>Рекламная кампания №<?php echo $n;?></h1>
<h2>Проверка актуальности запросов с контекстных объявлений</h2>
<ul>
<li><a href="chkrequests.php?id=1">Кампания №1</a></li>
<li><a href="chkrequests.php?id=2">Кампания №2</a></li>
<li><a href="chkrequests.php?id=3">Алф. указатель весь, часть 1</a></li>
<li><a href="chkrequests.php?id=4">Алф. указатель весь, часть 2</a></li>
<li><a href="chkrequests.php?id=5">Алф. указатель весь, часть 3</a></li>
<li><a href="chkrequests.php?id=6">Алф. указатель весь, часть 4</a></li>
<li><a href="chkrequests.php?id=7">Алф. указатель весь, часть 5</a></li>
<li><a href="chkrequests.php?id=8">Алф. указатель весь, часть 6</a></li>
<li><a href="chkrequests.php?id=9">Алф. указатель из НЕвыбранных рубрик, часть 1</a></li>
<li><a href="chkrequests.php?id=10">Алф. указатель из НЕвыбранных рубрик, часть 2</a></li>
<li><a href="chkrequests.php?id=11">Алф. указатель из НЕвыбранных рубрик, часть 3</a></li>
<li><a href="chkrequests.php?id=12">Алф. указатель из НЕвыбранных рубрик, часть 4</a></li>
<li><a href="chkrequests.php?id=13">Алф. указатель из НЕвыбранных рубрик, часть 5</a></li>
<li><a href="chkrequests.php?id=14">Список добавленных позиций в последнем обновлении</a></li>
</ul>
<table align="center" border="1" cellspacing="0" cellpadding="5" width="1000">
<col width="80" />
<col width="250" />
<col width="80" />
<col width="100" />
<col width="490" />
<tr><td align="center">Номер<br>строки</td><td align="center">Запрос</td><td align="center">Кол-во<br>позиций</td><td align="center">SQL время<br>(sec)</td><td align="center">URL</td></tr>
<?php
$n = 0;
$qCols	= 'S.name, S.dop, S.price, C.name, C.id, T.name, S.rubric, R.name, C.bold_rows, S.id, S.dog, R.id_parent, C.phones ';
$st_tot = gettime();
while (!feof($fl)) {
	$s = trim(fgets($fl));
	$n++;
	if ($s!='') {
		$wrds = GetWords($s);
		$qJoin1	= 'From STR S join RUBRICS R on R.id = S.rubric join CLIENTS C on C.id = S.client join CITIES T on T.id = C.city ';
		$qJoin	= '';
		for ($i=0;$i<count($wrds);$i++) 
			$qJoin .= ' join STR_INDEX S'.$i.' on S'.$i.'.str = S.id join WORDFORMS W'.$i.' on W'.$i.'.base = S'.$i.'.word and W'.$i.".val=UPPER('$wrds[$i]') ";
		$qTxt = 'Select distinct '.$qCols.$qJoin1.$qJoin.';';
		$st_sql = gettime();
		$qRes = mysql_query ($qTxt);
		$numRows = mysql_num_rows ($qRes);
		$RowsQty = 0;
		if (($numRows != 0) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)) ) {
			$RowsQty = $numRows;
			@mysql_free_result ($qRes);
		}
		$dt_sql = bcsub (gettime(), $st_sql, 6);
		if ($RowsQty==0)	$cl = 'err';
		elseif ($RowsQty<5)	$cl = 'warn';
		else			$cl = 'norm';
		echo ($cr.'<tr class="'.$cl.'"><td>'.$n.'</td><td>'.$s.'</td><td>'.$RowsQty.'</td><td>'.$dt_sql.'</td><td>http://www.biznesurfo.ru/find.php?fnd='.urlencode($s).'</td></tr>');
		if ($doUpdate) {
			@mysql_query ('Update ALPH_INDEX set qty = '.$RowsQty.' where UPPER(word) = '."UPPER('$s');");
			$i = mysql_affected_rows();
			$wrds = explode (' ', $s);
			if ((count($wrds)==2) && ($i==0)) {	
				// получим id первого слова
				$i = 0;
				$qRes = mysql_query ('Select A.id from ALPH_INDEX A where UPPER(A.word) = '."UPPER('$wrds[0]');");
				if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)) ) {
					$i = $Rows[0];
					@mysql_free_result ($qRes);
				}
				// получим id второстепенного слова
				if ($i > 0) {
					$qRes = mysql_query ('Select A.id from ALPH_INDEX A where A.parent = '.$i.' and UPPER(A.word) = '."UPPER('$wrds[1]');");
					if ((mysql_num_rows ($qRes) == 1) && ($qRes) && ($Rows = mysql_fetch_row ($qRes)) ) {
//				echo ('<br><br>'.$i); die();
						if ($Rows[0] > 0) 
							@mysql_query ('Update ALPH_INDEX set qty = '.$RowsQty.' where id = '.$Rows[0].';');
						@mysql_free_result ($qRes);
					}					
				}
			}
		}
	}
}
fclose ($fl);
$dt_tot = bcsub (gettime(), $st_tot, 6);
?>
</table>
<p>Общее время выполнения запросов: <?php echo $dt_tot; ?> (сек)</p>
</body>
</html>