<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$letter = 'Z';
if (count($_GET)>0) {
	if ((isset($_GET['id']))&&($_GET['id']!='')&&(strlen($_GET['id'])==1))
		$letter = $_GET['id'];
}

$Lins = array ();
// После написания определений для большинства имеющихся, убрать ограничение по количеству qty>2
$qTxt = 'Select A.id, A.word, A1.parent, A1.word, A.qty, A1.qty, A.translit, A1.translit From ALPH_INDEX A left join ALPH_INDEX A1 on A1.parent = A.id and A1.qty>2 Where A.parent is null and A.letter=UPPER('."'$letter'".') and A.qty>2 Order by A.word, A1.word;';
$qRes = SqlQuery($qTxt);
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	while ($Rows = mysql_fetch_row($qRes)) {
		$Lins[] = $Rows;
	}
	@mysql_free_result($qRes);
}
$oldParId = 0;
$Wrds = array ();
$WrdsQty = 0;
$m = 0;
if ($numRows>0) {
	$j = 0;
	for ($i = 0; $i<(count($Lins)); $i++) {
		if (isset($Lins[$i][2])) {
			$parId = $Lins[$i][2];
			if ($parId!=$oldParId) {
				$oldParId = $parId;
				$Wrds[$j][0] = $Lins[$i][1]; // рус. слово
				$Wrds[$j][1] = $Lins[$i][6]; // транслит
				$Wrds[$j][2] = ''; //urlencode($Lins[$i][1]);
				$Wrds[$j][3] = $Lins[$i][4]; // кол-во
				$Wrds[$j++][4] = 0; // level
			}
			$Wrds[$j][0] = $Lins[$i][3];
			$Wrds[$j][1] = $Lins[$i][7]; // $Lins[$i][6].'_'.$Lins[$i][7];
			$Wrds[$j][2] = ''; //urlencode($Lins[$i][1].' '.$Lins[$i][3]);
			$Wrds[$j][3] = $Lins[$i][5];
			$Wrds[$j++][4] = 1;
		} else {
			$Wrds[$j][0] = $Lins[$i][1];
			$Wrds[$j][1] = $Lins[$i][6];
			$Wrds[$j][2] = ''; //urlencode($Lins[$i][1]);
			$Wrds[$j][3] = $Lins[$i][4];
			$Wrds[$j++][4] = 0;
		}
	}
	$WrdsQty = count($Wrds);
	$m = (integer) (ceil(($WrdsQty)/2))-1;
	while ($m<$WrdsQty&&isset($Wrds[$m+1][4])&&$Wrds[$m+1][4]==1)
		$m++;
	$m++;
}
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";

if ($letter=='Z')
	$letter = 'A..Z';

$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Алфавитный указатель';
$Crumbs[1][0] = '';
$Crumbs[1][1] = $letter;
$crumbsQty = count($Crumbs);
$pagNam = 'map/'.urlencode($letter);
$cityFormAction = $pagNam;
$tagH1 = $letter;
$title = 'Алфавитный указатель. &laquo;'.$letter.'&raquo; - Индустрия Бизнеса';
$descr = 'Карта сайта Алфавитный указатель Каталога предприятий, товаров и услуг Индустрия Бизнеса уральского региона. Контакты фирм. Прайс-листы. Выставки. Размещение рекламы. Новости компаний и регионов';
$keyws = 'Карта сайта Алфавитный указатель:'.$letter.'. компаний регионов уральского товары услуги предприятия. Индустрия Бизнеса. Выставки. Размещение рекламы';

$Letters = GetAlphabit();
$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
if ($m>16) {
	$i = $m-16;
	$vertDirectQty = ($i<8)?$i:7;
	$isDirect = true;
}

require_once '../includs/map.html';

?>