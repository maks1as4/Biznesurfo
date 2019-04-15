<?php

// $_GET['fnd'] - поисковый запрос
// $_GET['pno'] - номер страницы считая от единицы
// $_GET['col'] - столбец по которому идет сортировка
// $_GET['desc'] - если есть, то сортировать в обратном порядке
// $_GET['rcid'] - регион, город в котором осуществляется поиск
// $_SESSION ['ccfind'] = 1; - флаг, сигнализирующий, что клик произошел в поиске
// $_SESSION ['prart']

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$st = gettime();
$dt_sql = '';
$fText = '';
$pagNam = 'find';
$forMeta = '';
$aKeys = array ();
$Lins = array ();
$_SESSION ['ccfind'] = 1;

if (count($_GET)>0) {
	if (isset($_GET['fnd'])) {
		$fText = $_GET['fnd'];
		$fText = urldecode($fText);
		// Если передан UTF8, переводим его в cp1251
		if (preg_match('//u', $fText))
			$fText = ICONV('UTF-8','Windows-1251',$fText);
		if (get_magic_quotes_gpc())
			$fText = stripslashes($fText);
		$fText = preg_replace('/\s+/',' ',$fText);
		$fText = trim($fText);
		if (strlen($fText)>127)
			$fText = substr($fText, 0, 127);
		$forMeta = htmlspecialchars($fText);
		$findText = $fText;
		$parStr = AddParStr($parStr, 'fnd='.urlencode($fText));
	}
	if ($flCity!='')
		$parStr = AddParStr($parStr, 'rcid='.$flCity);
	if ((isset($_GET['pno']))&&($_GET['pno']!='')&&(CheckInteger($_GET['pno'],1,10000) ))
		$curPageNo = $_GET['pno'];
	if ((isset($_GET['col']))&&($_GET['col']!='')&&(CheckInteger($_GET['col'], 1, 5) ))
		$sortCol = $_GET['col'];
	$sortDesc = isset($_GET['desc']);
}

if (isset($_SESSION ['prart']))
	unset($_SESSION ['prart']); // Признак перехода из прайс-листа для Карточки товара

$isFindPage = true;
$relCol = 0;
$RowsQty = 0;
$doRelav = ($sortCol==1)&&(!$sortDesc);
$arrRubrics = array (1024);
$arrFirms = array ();
$firmQty = 0;

$toFirmSwitch = 'find_firms'.$parStr;
$toMatSwitch = $pagNam.$parStr;


$wrds = GetWords($fText);
$cnt_wrds = count($wrds);
if ($cnt_wrds>0) {
	if ($cnt_wrds>8)
		$cnt_wrds = 8;

	$qTxt = 'select distinct '.
			'S.id,'.			// 0
			'S.name,'.			// 1
			'UPPER(S.name),'.	// 2
			'S.translit,'.		// 3
			'S.dop,'.			// 4
			'S.dog,'.			// 5
			'S.price,'.			// 6
			'S.rubric,'.		// 7
			'C.id,'.			// 8
			'C.name,'.			// 9
			'COALESCE(T.name,REGIONS.name),'. // 10
			'C.bold_rows,'.		// 11
			'(Select full_phone From CLIENT_PHONES Where id_client = C.id Order by sort_order Limit 1),'. // 12
			'R.name,'.			// 13
			'R.id_parent,'.		// 14
			'R.new_url '.		// 15
			'from STR S '.
			'join RUBRICS R on R.id = S.rubric '.
			'join CLIENTS C on C.id = S.client '.
			'left join CITIES T on T.id = C.city '.
			'left join REGIONS on REGIONS.id = C.region ';
			for ($i=0; $i<$cnt_wrds; $i++)
				$qTxt .= 'join STR_INDEX S'.$i.' on S'.$i.'.str = S.id join WORDFORMS W'.$i.' on W'.$i.'.base = S'.$i.'.word and W'.$i.".val=UPPER('$wrds[$i]') ";
			$qTxt .= 'Where S.active=1 and S.status=0 ';
			if ($idReg>0) {
				if ($isReg)
					$qTxt .= 'and C.region='.$idReg;
				else
					$qTxt .= 'and C.city='.$idReg;
			}

	$st_sql = gettime();
	$qRes = SqlQuery($qTxt);
	$dt_sql = bcsub(gettime(), $st_sql, 6);
	if (($qRes)&&mysql_num_rows($qRes)>0) {
		$sortOrder = !$sortDesc;
		$Ress = array ();
		$Idx = array ();

		// Заполнение массива результатами запроса (получение всех данных на клиент)
		while ($Rows = mysql_fetch_row($qRes)) {
			$Rows[] = 0;
			$Ress[$RowsQty] = $Rows;
			$Idx[$RowsQty] = $RowsQty;
			$RowsQty++;
			// Заполняем массив клиентов
			if (!in_array($Rows[8], $arrFirms))
				$arrFirms[] = $Rows[8];
			// Заполняем массив рубрик
			if (!in_array($Rows[7], $arrRubrics))
				$arrRubrics[] = $Rows[7];
		}
		@mysql_free_result($qRes);

		$firmQty = count($arrFirms);
		$relCol = count($Ress[0]);
		if ($doRelav) {
			for ($i = 0; $i<$RowsQty; $i++)
				$Ress[$i][$relCol] = CalcRelav($Ress[$i][2], strtoupper($fText));
		}
		qSort($Idx, $Ress);

		// Подготовка данных к выводу, заполнение массива $Lins
		$k = ($curPageNo-1)*30;
		if ($k+30>$RowsQty)
			$i = $RowsQty-$k;
		else
			$i = 30;
		$n = 0;
		for ($j = $k; $j<$i+$k; $j++) {
			switch ($Ress[$Idx[$j]][4]) {
				case 0 : {
						$prc = $Ress[$Idx[$j]][6];
						break;
					}
				case 1 : {
						$prc = 'от '.$Ress[$Idx[$j]][6];
						break;
					}
				case 2 : {
						$prc = 'договорная';
						break;
					}
				default: $prc = '?';
			}

			$Lins[$n][0] = $Ress[$Idx[$j]][0]; // id строчки
			$Lins[$n][1] = DoStrongWords($Ress[$Idx[$j]][1]); // $Ress[$Idx[$j]][0]- наименование позиции
			$Lins[$n][2] = $prc; // цена
			$Lins[$n][3] = $Ress[$Idx[$j]][8]; // id предприятия
			$Lins[$n][4] = $Ress[$Idx[$j]][9]; // предприятие
			$Lins[$n][5] = $Ress[$Idx[$j]][12]; // телефон
			$Lins[$n][6] = $Ress[$Idx[$j]][10]; // город
			$Lins[$n][7] = $Ress[$Idx[$j]][15]; // урл рубрики
			$Lins[$n][8] = $Ress[$Idx[$j]][13]; // рубрика
			$Lins[$n][9] = $Ress[$Idx[$j]][11]; // bold
			$Lins[$n++][10] = $Ress[$Idx[$j]][3]; // translit
		}

		$tabHeaders = array ('Наименование', 'Цена', 'Предприятие', 'Город', 'Рубрика');
		$Headers = array ();
		$HeadersQty = BuildHeaders($pagNam.AddParStr($parStr, 'col='));
//		$HeadersQty = BuildHeaders($s);
		if ($sortDesc||$sortCol>1){
			$parStr = AddParStr($parStr, 'col='.$sortCol.($sortDesc?'&desc':''));
		}
		$PageNav = CreatePageNavFind($RowsQty, 10, $pagNam, $parStr);
		$pageNavQty = count($PageNav);
		$qtyRow = $RowsQty.' позици'.PrintFinLetter($RowsQty, true).', '.$firmQty.' предприяти'.PrintFinLetter($firmQty, false);

		if (count($arrRubrics)>0) {
			$RightTextMods = GetRightTextBlocksFromArray();
			$RightBanners = GetRightBannersFromArray();
			$rbansQty = count($RightBanners);
			$rTblocksQty = count($RightTextMods);
			if ($rbansQty>5)
				$rbansQty = 5;
			if ($rbansQty+$rTblocksQty>15)
				$rTblocksQty = 15;
		}
	}
}

$linsQty = count($Lins);
$cityFormAction = $pagNam;

$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Товары и услуги';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Результаты поиска по запросу &laquo;'.$forMeta.'&raquo;';
$crumbsQty = count($Crumbs);

$tagH1 = 'Результаты поиска по запросу &laquo;'.$forMeta.'&raquo;';

if ($forMeta=='')
	$title = 'Поиск товаров - Индустрия Бизнеса';
else {
	$title = $forMeta.' - Индустрия Бизнеса';
	$keyws = $forMeta.', цена, продажа, купить';
	$descr = $forMeta;
}

$ft = gettime();
$dt = bcsub((bcsub($ft, $st, 6)), $dt_sql, 6);
$execTime = "время выполнения PHP: $dt; SQL: $dt_sql";

// Запишем в базу статистику по запросу
if ($cnt_wrds>0) {
	$s1 = addslashes($fText);
	$s2 = addslashes($_SERVER['REMOTE_ADDR']);
	$s3 = substr(addslashes($_SERVER['HTTP_USER_AGENT']),0,150);
	if ($isLogStat)
		SqlQuery('insert into FINDS (atime, val, cnt, userIP, userAgent, what) values '."('$dt_sql','$s1','$RowsQty','$s2','$s3','0');");
}

$jqAdd = <<<EoL
		$('#cball').click(function() {
			var toggle = $(this).attr('checked');
			var objs = $('#items_table_form input:checkbox');
			jQuery.each(objs, function(i) {
				objs[i].checked = toggle;
			});
		});
		$('a.excel').bind('click', function() {
			var unchecked = false;
			var Form_obj = $('#items_table_form');
			var checked_obj = $(Form_obj + 'input:checkbox:checked').not('#cball').not('#w-agree');
			if (checked_obj.length <= 0) {
				var objs = $(Form_obj + 'input:checkbox');
				jQuery.each(objs, function(i) {
					objs[i].checked = 'checked';
				});
				unchecked = true;
			}
			Form_obj.attr('action', 'genexcel');
			Form_obj.submit();
			if (unchecked) {
				jQuery.each(objs, function(i) {
					objs[i].checked = '';
				});
			}
			return true;
		});
EoL;
$i = $linsQty-($rbansQty+$rTblocksQty)*2;
$vertDirectQty = ($i<8)?$i:7;
if ($vertDirectQty<0)
	$vertDirectQty = 0;
$isDirect = $vertDirectQty>0;
if ($i-$vertDirectQty>4) {
	$Expos = GetExposBlock();
	$expoQty = count($Expos);
}
$b2bcontext = $isShowES&&($linsQty>5);
$modI = 100;
if ($b2bcontext) {
	if ($linsQty<14)
		$modI = (integer) ceil($linsQty/2);
	else
		$modI = (integer) ceil($linsQty/3);
}
$key4find2b2b = $forMeta;

require_once '../includs/find.html';

// FUNCTIONS
function newRow($id) {
	global $Ress;
	$k = count($Ress);
	for ($i = 0; $i<$k; $i++)
		if ($Ress[$i][9]==$id)
			break;
	return $i==$k;
}

function sortRange(&$IDX, $left, $right) {
	global $Ress;
	$i = $left;
	$j = $right;
	$b = true;
	while ($i!=$j) {
		if (compare($i, $j)) {
			$t = $IDX[$i];
			$IDX[$i] = $IDX[$j];
			$IDX[$j] = $t;
			$b = !$b;
		}
		if ($b)
			$i++; else
			$j--;
	}
	$i--;
	$j++;
	if ($left<$i)
		sortRange($IDX, $left, $i);
	if ($right>$j)
		sortRange($IDX, $j, $right);
}

function qSort(&$IDX, $Rows) {
	global $sortCol;
	switch ($sortCol) {
		case 1 : {
				function compare($i, $j) {
					global $Idx, $Ress, $sortOrder, $relCol, $doRelav; // S.name
					if ($doRelav) {
						if ($Ress[$Idx[$i]][$relCol]==$Ress[$Idx[$j]][$relCol])
							$b = ((strnatcasecmp($Ress[$Idx[$i]][1], $Ress[$Idx[$j]][1])==-1)xor$sortOrder);
						else
							$b = (($Ress[$Idx[$i]][$relCol]>$Ress[$Idx[$j]][$relCol])xor$sortOrder);
					} else
						$b = ((strnatcasecmp($Ress[$Idx[$i]][1], $Ress[$Idx[$j]][1])==-1)xor$sortOrder);
					return $b;
				}
				break;
			}
		case 2 : {
				function compare($i, $j) {
					global $Idx, $Ress, $sortOrder; // S.dog, S.price
					if ($Ress[$Idx[$i]][5]==$Ress[$Idx[$j]][5])
						$b = (($Ress[$Idx[$i]][6]<$Ress[$Idx[$j]][6])xor$sortOrder);
					else
						$b = (($Ress[$Idx[$i]][5]<$Ress[$Idx[$j]][5])xor$sortOrder);
					return $b;
				}
				break;
			}
		case 3 : {
				function compare($i, $j) {
					global $Idx, $Ress, $sortOrder; // C.name
					return (($Ress[$Idx[$i]][9]<$Ress[$Idx[$j]][9])xor$sortOrder);
				}
				break;
			}
		case 4 : {
				function compare($i, $j) {
					global $Idx, $Ress, $sortOrder; // name_city
					return (($Ress[$Idx[$i]][10]<$Ress[$Idx[$j]][10])xor$sortOrder);
				}
				break;
			}
		case 5 : {
				function compare($i, $j) {
					global $Idx, $Ress, $sortOrder; // R.name
					return (($Ress[$Idx[$i]][13]<$Ress[$Idx[$j]][13])xor$sortOrder);
				}
				break;
			}
	}

	sortRange($IDX, 0, count($IDX)-1);
}

function CalcRelav($mName, $fndT) {
	if (isset($fndT)&&($fndT!='')) {
		$wrds = GetWords($fndT);
		if (count($wrds)==0)
			return 0;
	} else
		return 0;
	$valRel = 0;
	$n = preg_match_all('([А-ЯЁа-яёA-Za-z\d-\+\\{}\(\)\"]+((?<=\d)[,.](?=\d)\d+)*)', $mName, $arrs);
	if ($n>0) {
		$wrdsName = $arrs[0];
		$wrdsName[0] = str_replace(',', '.', $wrdsName[0]);
		// 1. полное соответствие первого слова строки поиска слову наименования
		if ($wrds[0]==$wrdsName[0])
			$valRel = 3000;
		if ($valRel==0) {
			// 2. первое слово строки поиска - подстрока в первом слове наименования
			$x = strpos($wrdsName[0], $wrds[0]);
			if (($x!==false)&&($x<3))
				$valRel = 2000;
			else {
				$x = strlen($wrds[0]);
				if ($x>1) {
					$s = substr($wrds[0], 0, ($x/2));
					$x = strpos($wrdsName[0], $s);
					// 3. морфологическая форма первого слова строки поиска на первом месте наименования
					if (($x!==false)&&($x<3))
						$valRel = 1000;
				}
			}
		}
		if (count($wrds)>1) {
			// 4. второе слово строки поиска пристутсвует в непосредственной близости от начала строки наименования
			$n = (integer) ceil((strlen($wrds[0])+strlen($wrdsName[0]))/2);
			$x = strpos($mName, $wrds[1]);
			if (($x!==false)&&(($x-$n)<5))
				$valRel += 200;
			$x = strlen($wrds[1]);
			if ($x>1) {
				$s = substr($wrds[1], 0, ($x/2));
				$x = strpos($mName, $s);
				if (($x!==false)&&(($x-$n)<5))
					$valRel += 100;
			}
		}
		// 6. первое слово строки поиска в точности присутствует в наименовании
		$x = strpos($mName, $wrds[0]);
		if (($x!==false)&&($x>2))
			$valRel += 20;
	}
	return $valRel;
}

?>