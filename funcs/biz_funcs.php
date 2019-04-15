<?php

function DebugMsg() {
	global $isDeveloper, $_Debug;
	if ($isDeveloper) {
		$args = func_get_args();
		if (count($args)==2) {
			if (is_array($args[1]))
				$msg = $args[0].'<br /><pre>'.htmlspecialchars(print_r($args[1],true)).'</pre>';
			else
				$msg = $args[0].' = '.$args[1];
		} elseif (count($args)==1) {
			if (is_array($args[0]))
				$msg = '<br /><pre>'.htmlspecialchars(print_r($args[0],true)).'</pre>';
			else
				$msg = $args[0];
		}
		if (!isset($_Debug['msg']))
			$_Debug['msg'] = '';
		$_Debug['msg'] .= $msg.'<br />';
	}
}

function SqlQuery($sql) {
	global $hasError, $isLogSQL, $isDeveloper, $_Debug;
	$msg = $sql.'<div class="sql_desc" style="display: none;">';
	$t1 = gettime();
	$result = mysql_query($sql);
	$t2 = bcsub(gettime(), $t1, 6);

	if($result === false) {
		$hasError = true;
		$err_desc = mysql_error();
		$msg .= '<b>Error:</b><br />'.$err_desc.'<br /><b>Trace:</b><br />';

		if ($isLogSQL){
			$trace = debug_backtrace($limit=1);
			$sqlLine = 'File: '.$trace[0]['file'].' ; Line: '.$trace[0]['line'];
			mysql_query("Insert Into `SQL_ERRORS` Set `error`='".mysql_real_escape_string($err_desc)."', `line`='".mysql_real_escape_string($sqlLine)."', `sql`='".mysql_real_escape_string($sql)."';");
		}
	}
	foreach (debug_backtrace() as $key => $value) {
		$msg .= ($key+1).'. File: '.$value['file'].': '.$value['line'].'.&nbsp;&nbsp;Function: '.$value['function'].'<br />';
	}
	if ($isDeveloper)
		$DebugRow[0] = $msg.'</div>';
		$DebugRow[1] = $t2;
		$DebugRow[2] = $result;
		$_Debug['sql'][] = $DebugRow;
	return $result;
}

function GetConsole() {
	global $_Debug;
	$con = '<style type="text/css">'.
			'#ib_console {display:none; width:100%; position:absolute; top:0; left:0; z-index:2000; background-color:#efefef; border-bottom:2px solid #ababab; font-size: 12px;}'.
			'#ib_console div {padding:15px;}'.
			'#ib_console table, #ib_console table td {border:1px solid rgb(163, 160, 160); border-collapse: collapse; padding: 5px 5px 3px 5px; font-size: 12px; vertical-align: top;}'.
			'#ib_console .sql_error {background-color: rgb(235, 56, 56);}'.
			'#ib_console .sql_row {cursor:pointer;}'.
			'#ib_console div pre {overflow-x:auto; white-space:pre-wrap; white-space:-moz-pre-wrap !important; white-space:-pre-wrap; white-space:-o-pre-wrap; /* width: 99%; */ word-wrap:break-word;}'.
			'</style>'.
			'<script type="text/javascript">'.
			'$(window).load(function(){'.
			'	$(".sql_row").click(function () {'.
			'		$(this).find(".sql_desc").slideToggle(50);'.
			'	});'.
			'});'.
			'</script>'.
			'<div id="ib_console">'.
			'<div>'.$_Debug['msg'].'</div>';
	if (count($_Debug['sql'])>0) {
		$con .= '<div>'.
				'<h3><b>SQL-запросы</b></h3>'.
				'<table>';
		for ($i=0;$i<count($_Debug['sql']);$i++) {
			$con .= '<tr class="sql_row'.((!$_Debug['sql'][$i][2])?' sql_error':'').'">'.
					'<td style="text-align: center; width: 30px;">'.($i+1).'</td>'.
					'<td>'.$_Debug['sql'][$i][0].'</td>'.
					'<td style="text-align: center; color: blue; width: 50px;">'.$_Debug['sql'][$i][1].'</td>'.
					'</tr>';
		}
		$con .= '</table>'.
				'</div>';
	}
	$con .= '</div>';
	echo $con;
}

function PutStat($pageNo, $rubric) {
	/*
	$pageNo - индекс страниц (скриптов):
	2 - Материалы;
	3 - Поиск;
	4 - Прайс-лист клиента. Тогда $rubruc - id клиента
	5 - Предприятия по рубрикам
	*/
	global $isRobot;
	if (!$isRobot)
		SqlQuery('insert into COUNTERS values ('.$pageNo.','.$rubric.',curdate(),1) on duplicate key update visits=visits+1');
}

function PutClientStat($idClient, $kind) {
	// $kind - Вид посещения клиента: 0-карточка; 1-ссылка на сайт; 2-скачан прайс; 3-печать
	global $isRobot;
	if (!$isRobot)
		SqlQuery('insert into COUNTERS_CLIENT values ('.$idClient.','.$kind.',curdate(),1) on duplicate key update visits=visits+1');
}

function GetExample() {
	global $forPred;
	$s = '';
	$qRes = SqlQuery('Select E.val From EXAMPLES'.($forPred?'_PRED':'').' E Where E.id='.rand(1, 21).';');
	if ((mysql_num_rows($qRes)!=0)&&($qRes)) {
		if ($Rows = mysql_fetch_row($qRes))
			$s = $Rows[0];
		@mysql_free_result($qRes);
	}
	return $s;
}

function GetRubrPath($curId) {
	global $MatAct;
	if ((isset($MatAct))&&($MatAct))
		$nm = 'Товары и услуги';
	else
		$nm = 'Фирмы';
	if ($curId==='')
		return '<h1>'.$nm.'</h1>';
	$i = $curId;
	$s = '';
	do {
		$b = false;
		$qRes = SqlQuery('Select R.id_parent, R.name, R.new_url From RUBRICS R Where R.id='.$i.';');
		if ((mysql_num_rows($qRes)!=0)&&($qRes)) {
			if ($Rows = mysql_fetch_row($qRes)) {
				if ($s=='')
					$s = '<h1>'.$Rows[1].'</h1>';
				else
					$s = '<A href="/'.$Rows[2].'">'.$Rows[1].'</A>&nbsp;/&nbsp;'.$s;
				$i = $Rows[0];
			}
			@mysql_free_result($qRes);
			$b = true;
		}
	} while ($b);
	return '<A href="/">'.$nm.'</A>&nbsp;/&nbsp;'.$s;
}

function GetCrumbs($curId, $lastLink=false) {
	global $tagH1, $forPred, $parStr;
	$ar = array ();
	if ((isset($forPred))&&($forPred))
		$f = 'firms/';
	else
		$f = 'prices/';
	$i = $curId;
	$j = 1;
	$s = '';
	do {
		$b = false;
		$qRes = SqlQuery('Select R.id_parent, R.name, R.new_url From RUBRICS R Where R.id='.$i.';');
		if ((mysql_num_rows($qRes)!=0)&&($qRes)) {
			if ($Rows = mysql_fetch_row($qRes)) {
				if ($s=='') {
					$s = $Rows[1];
					if ($lastLink)
						$ar[$j][0] = $f.$Rows[2].$parStr;
					else
						$ar[$j][0] = '';
					$ar[$j][1] = $s;
				} else {
					$ar[$j][0] = $f.$Rows[2].$parStr;
					$ar[$j][1] = $Rows[1];
				}
				$j++;
				$i = $Rows[0];
			}
			@mysql_free_result($qRes);
			$b = true;
		}
	} while ($b);
	if ((isset($forPred))&&($forPred)) {
		$ar[$j][0] = 'firms'.$parStr;
		$ar[$j][1] = 'Предприятия';
	} else {
		$ar[$j][0] = 'prices'.$parStr;
		$ar[$j][1] = 'Товары и услуги';
	}
	$tagH1 = $s;
	return $ar;
}

function GetRubrPathWoLink($curId) {
	$i = $curId;
	$s = '';
	do {
		$b = false;
		$qRes = SqlQuery('Select R.id_parent, R.name From RUBRICS R Where R.id='.$i.';');
		if ((mysql_num_rows($qRes)!=0)&&($qRes)) {
			if ($Rows = mysql_fetch_row($qRes)) {
				$s = $Rows[1].' / '.$s;
				$i = $Rows[0];
			}
			@mysql_free_result($qRes);
			$b = true;
		}
	} while ($b);
	if ($s!='')
		$s = substr($s, 0, -3);
	return $s;
}

function CreatePageNav($MaxRecs, $MaxRefs, $txtRef, $pars) {
	global $curPageNo, $maxPageNo, $sortCol, $sortDesc, $idReg, $qtyPerPage;
	$ar = array ();
	if ((is_numeric($MaxRecs))&&(is_numeric($MaxRefs))) {
		$q = (integer) ceil($MaxRecs/$qtyPerPage);
		$maxPageNo = $q;
		if ($q>1) {
			if ($pars=='')
				$s = '?';
			else
				$s = $pars.'&';
			$b = (!$sortDesc&&$sortCol==1&&$idReg==0);
			if ($q==$MaxRefs+1)
				$MaxRefs = $q;
			$start = 1;
			if (($curPageNo>=$MaxRefs)&&($MaxRefs!=$q)) {
				$start = $curPageNo-((integer) ceil(($MaxRefs-1)/2));
			}
			if (($start+$MaxRefs)>=$q)
				$start = $q-$MaxRefs+1;
			if ($start<1)
				$start = 1;
			if ($q>$MaxRefs)
				$q = $MaxRefs;
			if ($curPageNo>1)
				$ar[] = '<a href="'.$txtRef.($b?('/'.($curPageNo-1)):($s.'pno='.($curPageNo-1))).'" class="edge">&larr; предыдущая</a>';
			for ($i = $start; $i<=$start+$q-1; $i++) {
				if ($i==$curPageNo)
					$ar[] = $i;
				else
					$ar[] = '<a href="'.$txtRef.($b?('/'.$i):($s.'pno='.$i)).'">'.$i.'</a>';
			}
			if ($curPageNo!=$maxPageNo)
				$ar[] = '<a href="'.$txtRef.($b?('/'.($curPageNo+1)):($s.'pno='.($curPageNo+1))).'" class="edge">следующая &rarr;</a>';
		}
	}
	return $ar;
}

function CreatePageNavFind($MaxRecs, $MaxRefs, $txtRef, $pars) {
	global $curPageNo, $maxPageNo, $sortCol, $sortDesc, $idReg, $qtyPerPage;
	$ar = array ();
	if ((is_numeric($MaxRecs))&&(is_numeric($MaxRefs))) {
		$q = (integer) ceil($MaxRecs/$qtyPerPage);
		$maxPageNo = $q;
		if ($q>1) {
			if ($q==$MaxRefs+1)
				$MaxRefs = $q;
			$start = 1;
			if (($curPageNo>=$MaxRefs)&&($MaxRefs!=$q)) {
				$start = $curPageNo-((integer) ceil(($MaxRefs-1)/2));
			}
			if (($start+$MaxRefs)>=$q)
				$start = $q-$MaxRefs+1;
			if ($start<1)
				$start = 1;
			if ($q>$MaxRefs)
				$q = $MaxRefs;

			$pars .= ($pars)?'&':'?';
			if ($curPageNo>1)
				$ar[] = '<a href="'.$txtRef.$pars.'pno='.($curPageNo-1).'" class="edge">&larr; следующая</a>';
			for ($i = $start; $i<=$start+$q-1; $i++) {
				if ($i==$curPageNo)
					$ar[] = $i;
				else
					$ar[] = '<a href="'.$txtRef.$pars.'pno='.$i.'">'.$i.'</a>';
			}
			if ($curPageNo!=$maxPageNo)
				$ar[] = '<a href="'.$txtRef.$pars.'pno='.($curPageNo+1).'" class="edge">предыдущая &rarr;</a>';
		}
	}
	return $ar;
}

function CreateKabinetNav($MaxRecs, $MaxRefs, $txtRef, $pars) {
	global $curPageNo, $maxPageNo, $qtyPerPage;
	$ar = array ();
	if ((is_numeric($MaxRecs))&&(is_numeric($MaxRefs))) {
		$q = (integer) ceil($MaxRecs/$qtyPerPage);
		$maxPageNo = $q;
		if ($q>1) {
			if ($pars=='')
				$s = '?';
			else
				$s = $pars.'&';
			if ($q==$MaxRefs+1)
				$MaxRefs = $q;
			$start = 1;
			if (($curPageNo>=$MaxRefs)&&($MaxRefs!=$q)) {
				$start = $curPageNo-((integer) ceil(($MaxRefs-1)/2));
			}
			if (($start+$MaxRefs)>=$q)
				$start = $q-$MaxRefs+1;
			if ($start<1)
				$start = 1;
			if ($q>$MaxRefs)
				$q = $MaxRefs;
			if ($curPageNo>1)
				$ar[] = '<li><a href="'.$txtRef.$s.'p='.($curPageNo-1).'">&laquo;</a></li>';
			else
				$ar[] = '<li class="disabled"><a href="javascript://">&laquo;</a></li>';
			for ($i = $start; $i<=$start+$q-1; $i++) {
				if ($i==$curPageNo)
					$ar[] = '<li class="active"><a href="'.$txtRef.$s.'p='.$i.'">'.$i.'</a></li>';
				else
					$ar[] = '<li><a href="'.$txtRef.$s.'p='.$i.'">'.$i.'</a></li>';
			}
			if ($curPageNo!=$maxPageNo)
				$ar[] = '<li><a href="'.$txtRef.$s.'p='.($curPageNo+1).'">&raquo;</a></li>';
			else
				$ar[] = '<li class="disabled"><a href="javascript://">&raquo;</a></li>';
		}
	}
	return $ar;
}

function CreateBootstrapNav($MaxRecs, $MaxRefs, $txtRef, $pars) {
	global $curPageNo, $maxPageNo, $qtyPerPage;
	$ar = array ();
	if ((is_numeric($MaxRecs))&&(is_numeric($MaxRefs))) {
		$q = (integer) ceil($MaxRecs/$qtyPerPage);
		$maxPageNo = $q;
		if ($q>1) {
			if ($pars=='')
				$s = 'А';
			else
				$s = $pars.'&';
			if ($q==$MaxRefs+1)
				$MaxRefs = $q;
			$start = 1;
			if (($curPageNo>=$MaxRefs)&&($MaxRefs!=$q)) {
				$start = $curPageNo-((integer) ceil(($MaxRefs-1)/2));
			}
			if (($start+$MaxRefs)>=$q)
				$start = $q-$MaxRefs+1;
			if ($start<1)
				$start = 1;
			if ($q>$MaxRefs)
				$q = $MaxRefs;
			if ($curPageNo>1)
				$ar[] = '<li><a href="'.$txtRef.$s.'p='.($curPageNo-1).'">&laquo;</a></li>';
			else
				$ar[] = '<li class="disabled"><a href="javascript://">&laquo;</a></li>';
			for ($i = $start; $i<=$start+$q-1; $i++) {
				if ($i==$curPageNo)
					$ar[] = '<li class="active"><a href="'.$txtRef.$s.'p='.$i.'">'.$i.'</a></li>';
				else
					$ar[] = '<li><a href="'.$txtRef.$s.'p='.$i.'">'.$i.'</a></li>';
			}
			if ($curPageNo!=$maxPageNo)
				$ar[] = '<li><a href="'.$txtRef.$s.'p='.($curPageNo+1).'">&raquo;</a></li>';
			else
				$ar[] = '<li class="disabled"><a href="javascript://">&raquo;</a></li>';
		}
	}
	return $ar;
}

function PrintFinLetter($qty, $isMat) {
	$s = substr($qty, -1);
	if (($s==0)||($s>4))
		$L = 'й';
	elseif ((strlen($qty)>1)&&(substr($qty, -2, 1)=='1'))
		$L = 'й';
	elseif ($s=='1')
		$L = ($isMat)?'я':'е';
	else
		$L = ($isMat)?'и':'я';
	return $L;
}

function PrintFinLetter2($qty) {
	$s = substr($qty, -1);
	if (($s==0)||($s>4))
		$L = 'ов';
	elseif ((strlen($qty)>1)&&(substr($qty, -2, 1)=='1'))
		$L = 'ов';
	elseif ($s=='1')
		$L = '';
	else
		$L = 'а';
	return $L;
}

function GetWords($txt) {
	$n = preg_match_all('([А-ЯЁа-яёA-Za-z\d]+((?<=\d)[,.](?=\d)\d+)*)', $txt, $arrs);
	if ($n>0) {
		$stopW = array('в', 'с', 'от', 'до', 'при', 'под', 'на', 'без', 'за', 'над', 'для');
		$Ress = $arrs[0];
		foreach ($stopW as $val) {
			$key = array_search($val, $Ress, true);
			if ($key!==false)
				$Ress[$key] = $Ress[0];
		}
		$Ress = array_unique($Ress);
		$Ress = array_values($Ress);
		for ($i = 0; $i<count($Ress); $i++)
			$Ress[$i] = str_replace(',', '.', $Ress[$i]);
	}
	else
		$Ress = array ();
	return $Ress;
}

function GetTotalInfo() {
	$ar = array ();
	$qRes = SqlQuery('Select DATE(D.cdate), D.qty_m, D.qty_c From DATA_INFO D Order by D.id DESC;');
	if ((mysql_num_rows($qRes)!=0)&&($qRes)&&($Rows = mysql_fetch_row($qRes))) {
		$ar = $Rows;
		@mysql_free_result($qRes);
	}
	return $ar;
}

function gettime() {
	$pt = explode(' ', microtime());
	$rt = $pt[1].substr($pt[0], 1);
	return $rt;
}

function CheckRefer($ref) {
	$p = strpos($ref, 'biznesurfo');
	return ($p!==false);
}

function ParseReferer($ref) {
	global $referWords, $notFoundFlag, $referWordsQty, $referString;
	if (isset($_SESSION['lpg']))
		return 0;
	$key = '';
	$p = stripos($ref, 'yandex.ru/');
	if ($p!==false)
		$key = 'text';
	else {
		$p = stripos($ref, 'google.ru/');
		if ($p!==false)
			$key = 'q';
		else {
			$p = stripos($ref, 'rambler.ru/');
			if ($p!==false)
				$key = 'query';
			else {
				$p = stripos($ref, 'mail.ru/');
				if ($p!==false)
					$key = 'q';
			}
		}
	}
	if ($key!='') {
		$ref = substr($ref, stripos($ref, $key));
		$a = explode('&', $ref);
		$referString = '';
		$i = 0;
		while ($i<count($a)) {
			$b = split('=', $a[$i]);
			if ($b[0]==$key) {
				$referString = trim(urldecode($b[1]));
				$p = strpos($b[1], '%D');
				if ($p===0)
					$referString = iconv('UTF-8', 'WINDOWS-1251', $referString);
			}
			$i++;
		}
		if ($referString!='') {
			$referWords = GetWords($referString);
			$notFoundFlag = true;
			$referWordsQty = count($referWords);
		}
	}
}

function ShowYandexDirect() {
	/*
	  <!-- Яндекс.Директ -->
	  <script type="text/javascript">
	  //<![CDATA[
	  yandex_partner_id = 54032;
	  yandex_site_bg_color = 'FFFFFF';
	  yandex_site_charset = 'windows-1251';
	  yandex_ad_format = 'direct';
	  yandex_font_size = 1;
	  yandex_direct_type = 'vertical';
	  yandex_direct_limit = 3;
	  yandex_direct_header_bg_color = 'FEEAC7';
	  yandex_direct_title_color = '0000CC';
	  yandex_direct_url_color = '006600';
	  yandex_direct_all_color = '0000CC';
	  yandex_direct_text_color = '000000';
	  yandex_direct_hover_color = '0066FF';
	  yandex_direct_favicon = true;
	  document.write('<sc'+'ript type="text/javascript" src="http://an.yandex.ru/system/context.js"></sc'+'ript>');
	  //]]>
	  </script>
	 */
}

function GetRightBans() {
	global $isMainPag, $idRubric, $isDirect;
	if ((isset($isMainPag))&&($isMainPag)) {
		ExtractRightBanners(0);
	} elseif ($idRubric>0) {
		ExtractRightBanners($idRubric);
		ExtractRightTextMod($idRubric);
	}

	if ((isset($isDirect))&&($isDirect)) {
		//	ShowYandexDirect();
	}
}

function ExtractRightBanners($idR) {
	global $RightBanners, $isRobot;
	$s = 'rbn_'.$idR;
	if ((!isset($_SESSION[$s]))||(!CheckIntegerUnsign($_SESSION[$s])))
		$_SESSION[$s] = 0;

	$sql =	'(select B.name, B.url, B.id, 1 as sort_col, B.width, B.height '.
			'from BANNERS B '.
			'join RUBRICS_CHILD RCH on (B.rubric=RCH.id and RCH.id_child='.$idR.') '.
			'where B.kind=2 '.
			'and B.visible=1 '.
			'and B.id>'.$_SESSION[$s].' '.
			'and not (B.show_below=0 and B.rubric<>'.$idR.')) '.
			'union '.
			'(select B.name, B.url, B.id, 2, B.width, B.height '.
			'from BANNERS B '.
			'join RUBRICS_CHILD RCH on (B.rubric=RCH.id and RCH.id_child='.$idR.') '.
			'where B.kind=2 '.
			'and B.visible=1 '.
			'and B.id<='.$_SESSION[$s].' '.
			'and not (B.show_below=0 and B.rubric<>'.$idR.')) '.
			'order by sort_col, id';
	$i = 0;
	$ids = array();
	$qRes = SqlQuery($sql);
	if ($qRes) {
		while ($Rows = mysql_fetch_row($qRes)){
			if ($Rows[0]!=''){
				if ($i==0)
					$_SESSION[$s] = $Rows[2];
				$RightBanners[$i][] = $Rows[0];
				$RightBanners[$i][] = $Rows[1];
				$RightBanners[$i][] = $Rows[2];
				$RightBanners[$i][] = $Rows[4];
				$RightBanners[$i][] = $Rows[5];
				$ids[] = $Rows[2];
				$i++;
			}
		}
		@mysql_free_result($qRes);
		if ((count($ids)>0)&&($isRobot==0)) {
			sort($ids);
			$sql =	'insert into COUNTERS_BT '.
					'select '.
					'id,'.
					'1,'.		// is_banner
					'curdate(),'.
					'1 '.		//  cnt
					'from BANNERS '.
					'where id in ('.implode(',',$ids).') '.
					'on duplicate key update cnt=cnt+1';
			SqlQuery($sql);
		}
	}
}

function ExtractRightTextMod($idR) {
	global $RightTextMods, $isRobot;
	$s = 'rtm_'.$idR;
	if ((!isset($_SESSION[$s]))||(!CheckIntegerUnsign($_SESSION[$s])))
		$_SESSION[$s] = 0;

	$sql =	'(select T.head, T.txt, T.phone, T.link, T.id, 1 as sort_col '.
			'from TEXTMODULS T '.
			'join RUBRICS_CHILD RCH on (T.rubric=RCH.id and RCH.id_child='.$idR.') '.
			'where T.id>'.$_SESSION[$s].' '.
			'and T.visible=1 '.
			'and not (T.show_below=0 and T.rubric<>'.$idR.'))'.
			'union '.
			'(select T.head, T.txt, T.phone, T.link, T.id, 2 '.
			'from TEXTMODULS T '.
			'join RUBRICS_CHILD RCH on (T.rubric=RCH.id and RCH.id_child='.$idR.') '.
			'where T.id<='.$_SESSION[$s].' '.
			'and T.visible=1 '.
			'and not (T.show_below=0 and T.rubric<>'.$idR.')) '.
			'order by sort_col,id';
	$i = 0;
	$ids = array();
	$qRes = SqlQuery($sql);
	if ($qRes) {
		while ($Rows = mysql_fetch_row($qRes)) {
			if ($i==0)
				$_SESSION[$s] = $Rows[4];
			$RightTextMods[] = $Rows;
			$ids[] = $Rows[4];
			$i++;
		}
		@mysql_free_result($qRes);
		if ((count($ids)>0)&&($isRobot==0)) {
			sort($ids);
			$sql =	'insert into COUNTERS_BT '.
					'select '.
					'id,'.
					'0,'.		// is_banner
					'curdate(),'.
					'1 '.		//  cnt
					'from TEXTMODULS '.
					'where id in ('.implode(',',$ids).') '.
					'on duplicate key update cnt=cnt+1';
			SqlQuery($sql);
		}
	}
}

function GetRightBannersFromArray($limit=0) {
	global $arrRubrics, $isRobot;
	$s = implode(',', $arrRubrics);
	$sql =	'select '.
			'B.name,'.
			'B.url,'.
			'B.id,'.
			'B.width,'.
			'B.height '.
			'from BANNERS B '.
			'join RUBRICS_CHILD RCH on (B.rubric=RCH.id and RCH.id_child IN ('.$s.')) '.
			'where B.kind=2 '.
			'and B.visible=1 '.
			'and not (B.show_below=0 and B.rubric not in ('.$s.')) '.
			'group by B.name '.
			'order by RAND() ';
			if ($limit>0)
				$sql .= 'limit '.$limit;
	$qRes = SqlQuery($sql);
	$Bans = array ();
	$i = 0;
	$ids = array();
	if ($qRes) {
		while ($Rows = mysql_fetch_row($qRes))
			if ($Rows[0]!=''){
				$Bans[$i] = $Rows;
				$ids[] = $Rows[2];
				$i++;
			}
		@mysql_free_result($qRes);
		if ((count($ids)>0)&&($isRobot==0)) {
			$sql =	'insert into COUNTERS_BT '.
					'select '.
					'id,'.
					'1,'.		// is_banner
					'curdate(),'.
					'1 '.		//  cnt
					'from BANNERS '.
					'where id in ('.implode(',',$ids).') '.
					'on duplicate key update cnt=cnt+1';
			SqlQuery($sql);
		}
	}
	return $Bans;
}

function GetRightTextBlocksFromArray($limit=0) {
	global $arrRubrics, $isRobot;
	$s = implode(',', $arrRubrics);
	$sql =	'select '.
			'T.head,'.
			'T.txt,'.
			'T.phone,'.
			'T.link,'.
			'T.id '.
			'from TEXTMODULS T '.
			'join RUBRICS_CHILD RCH on (T.rubric=RCH.id and RCH.id_child IN ('.$s.')) '.
			'where not (T.show_below=0 and T.rubric not in ('.$s.')) '.
			'and T.visible=1 '.
			'group by T.head,T.txt '.
			'order by RAND() ';
			if ($limit>0)
				$sql .= 'limit '.$limit;
	$qRes = SqlQuery($sql);
	$Blocks = array ();
	$ids = array();
	if ($qRes) {
		while ($Rows = mysql_fetch_row($qRes)) {
			$Blocks[] = $Rows;
			$ids[] = $Rows[4];
		}
		@mysql_free_result($qRes);
		if ((count($ids)>0)&&($isRobot==0)) {
			sort($ids);
			$sql =	'insert into COUNTERS_BT '.
					'select '.
					'id,'.
					'0,'.		// is_banner
					'curdate(),'.
					'1 '.		//  cnt
					'from TEXTMODULS '.
					'where id in ('.implode(',',$ids).') '.
					'on duplicate key update cnt=cnt+1';
			SqlQuery($sql);
		}
	}
	return $Blocks;
}

function GetIdByTranslit($url) {
	$id = 0;
	$s = strtolower($url);

	$qRes = SqlQuery('Select R.id From RUBRICS R Where LOWER(R.new_url)='."'$s';");
	if (($qRes)&&(mysql_num_rows($qRes)==1)) {
		if ($Rows = mysql_fetch_row($qRes))
			$id = $Rows[0];
	}
	@mysql_free_result($qRes);
	return $id;
}

function DoStrongWords($name) {
	global $aKeys;
	$s = $name;
	if (preg_match('/[А-ЯЁа-яёA-Za-z]/', $s[0])) {
		$fs = '';
		// Первое слово начинается с буквы
		// Найдем границу этого слова
		$p = strpos($s, ' ');
		if ($p===false) {
			// Наименование состоит из одного слова
			$fs = $s;
		} else {
			$notIns = true;
			if (strlen($s)>$p+5) {
				$p1 = strpos($s, ' ', $p+1);
				if ((preg_match('/[А-ЯЁа-яёA-Za-z]/', $s[$p+5]) )&&
					(preg_match('/[А-ЯЁа-яёA-Za-z]/', $s[$p+1]))&&
					(($p1===false) || ($p1>$p+5)))
				{
					// Второе слово являеься смысловым, поскольку начинается с буквы, содержит 
					// не менее 4 символов, причем четвертый символ - буква
					// Найдем границу второго слова
					$p1 = strpos($s, ' ', $p+5);
					if ($p1===false) {
						// Значит наименование завершается этим словом
						$fs = $s;
					} else {
						// Получим слова для выделения в стронг
						$fs = substr($s, 0, $p1);
					}
					$notIns = false;
				}
			}
			if ($notIns) {
				// Вставим стронг только после первого слова
				$fs = substr($s, 0, $p);
			}
		}
		if (!in_array($fs, $aKeys))
			$aKeys[] = $fs;
		$s = str_replace($fs, '<strong>'.$fs.'</strong>', $s);
	}
	return $s;
}

function DetectRobot() {
	$userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$isRobot = 0;
	if (substr_count($userAgent, 'crawler')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'yandex')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'webalta')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'mail.ru')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'google')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'aport')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'rambler')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'yahoo')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'stackrambler')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'search')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'indexer')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'bingbot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'sitebot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'b2bcontext')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'cuill.com')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'mj12bot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'searchbot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'ahrefsbot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'seokicks-robot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'siteexplorer')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'facebook')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'alexa')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'ezooms')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, '360spider')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'solomonobot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'web-monitoring')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'acoonbot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'msnbot')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'semrush.com')>0)
		$isRobot = 1;
	else if (substr_count($userAgent, 'NetcraftSurveyAgent')>0)
		$isRobot = 1;
	return $isRobot;
}

function GetAlphabit() {
	$Lets = array ();
	$qRes = SqlQuery('Select distinct(A.letter) From ALPH_INDEX A Order by A.letter;');
	if ((mysql_num_rows($qRes)!=0)&&($qRes)) {
		$i = 0;
		while ($Rows = mysql_fetch_row($qRes)) {
			$Lets[$i][0] = 'map/'.urlencode($Rows[0]);
			$Lets[$i++][1] = ($Rows[0]=='Z')?'A..Z':$Rows[0];
		}
		@mysql_free_result($qRes);
	}
	return $Lets;
}

function GetNewsBlock() {
	$newsShift = 0;
	if (isset($_SESSION ['nsn'])) {
		$i = $_SESSION ['nsn'];
		if (is_numeric($i))
			$newsShift = $i+1;
		if ($newsShift>4)
			$newsShift = 0;
	}
	$_SESSION ['nsn'] = $newsShift;
	$ar = array ();
	$qRes = SqlQuery('Select CONCAT("news/", N.id, ".html"), DATE(N.ns_date), N.head, N.anonce From NEWS N Order by N.ns_date desc Limit '.$newsShift.', 2;');
	if ((mysql_num_rows($qRes)>0)&&($qRes)) {
		while ($Rows = mysql_fetch_row($qRes)) {
			$ar[] = $Rows;
		}
		@mysql_free_result($qRes);
	}
	for ($i = 0; $i<count($ar); $i++)
		if (strlen($ar[$i][3])>170) {
			$p = strpos($ar[$i][3], ' ', 120);
			if ($p!==false)
				$ar[$i][3] = substr($ar[$i][3], 0, $p);
		}
	return $ar;
}

function GetExposBlock() {
	$ar = array ();
	$qRes = SqlQuery('Select COALESCE(E.url, E.id), E.name, E.anonce From EXPO E Where E.date1>NOW() Order by E.date1, E.id DESC Limit 0, 2;');
	if ((mysql_num_rows($qRes)>0)&&($qRes)) {
		while ($Rows = mysql_fetch_row($qRes)) {
			$ar[] = $Rows;
		}
		@mysql_free_result($qRes);
	}
	for ($i = 0; $i<count($ar); $i++)
		if (strlen($ar[$i][2])>170) {
			$p = strpos($ar[$i][2], ' ', 120);
			if ($p!==false)
				$ar[$i][2] = substr($ar[$i][2], 0, $p);
		}
	return $ar;
}

function GetExpoReportsBlock() {
	$ar = array ();
	$qRes = SqlQuery('Select COALESCE(E.url, E.expo), E.short_head, E.anonce From EXPO_REPORT E Order by E.id DESC Limit 0, 2;');
	if ((mysql_num_rows($qRes)>0)&&($qRes)) {
		while ($Rows = mysql_fetch_row($qRes)) {
			$ar[] = $Rows;
		}
		@mysql_free_result($qRes);
	}
	return $ar;
}

function GetTagsCloud() {
	global $idRubric, $forPred, $Tags_str;
	$idRub = 0;  // !! -- временно, пока нет облака для разделов
	if ($forPred)
		$Tags_str = array (4, 9, 52, 56);
	else
		$Tags_str = array (6, 12, 50, 57);
	$ar = array ();
	$qRes2 = SqlQuery('Select T.name, T.transl, T.weight From TAGSCLOUD T Where T.rubric='.$idRub.' and T.kind='.($forPred?'1':'0').' Order by T.sort_ord;');
	$i = 0;
	if ((mysql_num_rows($qRes2)>0)&&($qRes2)) {
		while ($Rows = mysql_fetch_row($qRes2)) {
			$ar[$i][0] = $Rows[0];
			$ar[$i][1] = $forPred?$Rows[1]:('index/'.$Rows[1].'.html');
			$ar[$i++][2] = $Rows[2];
		}
		@mysql_free_result($qRes2);
	}
	return $ar;
}

function GetRubChilds($id) {
	global $arrRubrics;
	$m = count($arrRubrics);
	$qRes = SqlQuery('Select R.id From RUBRICS R Where R.id_parent='.$id.';');
	if ((mysql_num_rows($qRes)>0)&&($qRes)) {
		while ($Rows = mysql_fetch_row($qRes))
			$arrRubrics[] = $Rows[0];
		@mysql_free_result($qRes);
	}
	if ($m>0) {
		$j = count($arrRubrics);
		for ($i = ($m-1); $i<$j; $i++)
			GetRubChilds($arrRubrics[$i]);
	}
}

function HumanDate($s) {
	list($y, $m, $d) = explode('-', $s);
	return $d.'.'.$m.'.'.$y;
}

function GetCities($inCh=false) {
	global $idRubric, $arrRubrics;
	$s = '';
	$s1 = '';

	$sql =	'select '.
			'CITIES.id,'.
			'CITIES.name,'.
			'REGIONS.id,'.
			'REGIONS.name '.
			'from CLIENTS '.
			'join CLIENT_RUBRICS on CLIENTS.id=CLIENT_RUBRICS.client '.
			'join REGIONS on CLIENTS.region=REGIONS.id '.
			'left join CITIES on CLIENTS.city=CITIES.id '.
			'group by 3,1 '.
			'order by 4,2';
	$qRes = SqlQuery($sql);
	$ar = array ();
	$ar[0][0] = '';
	$ar[0][1] = 'Любой';
	$i = 1;
	$oldReg = -1;
	$curReg = -1;
	if ($qRes) {
		while ($Rows = mysql_fetch_row($qRes)) {
			$curReg = $Rows[2];
			if ($curReg!=$oldReg) {
				$oldReg = $curReg;
				$ar[$i][0] = 'r'.$curReg;
				$ar[$i++][1] = $Rows[3];
				if ($Rows[0]!=0) {
					$ar[$i][0] = 'c'.$Rows[0];
					$ar[$i++][1] = '&nbsp;&nbsp;&nbsp;'.$Rows[1];
				}
			} elseif ($Rows[0]!=0) {
				$ar[$i][0] = 'c'.$Rows[0];
				$ar[$i++][1] = '&nbsp;&nbsp;&nbsp;'.$Rows[1];
			}
		}
		@mysql_free_result($qRes);
	}
	return $ar;
}

function AddLocParStr($pars) {
	global $locParStr;
	if ($locParStr=='/')
		$locParStr = '/?'.$pars;
	else
		$locParStr .= '&'.$pars;
}

function AddParStr($str, $pars) {
	$s = $str==''?'?':'&';
	return $str.$s.$pars;
}

function BuildHeaders($ref) {
	global $tabHeaders, $Headers, $sortDesc, $sortCol;
	$ref = str_replace('//?', '/?', $ref);
	$j = count($tabHeaders);
	for ($i = 0; $i<$j; $i++) {
		if ($sortCol==$i+1)
			$Headers[] = '<td><span class="sorter_price_l">'.$tabHeaders[$i].'</span><span class="sorter_price_r"><a href="'.$ref.($i+1).'&desc" class="sorter_desc'.($sortDesc?'':'_enabled').'" title="Сортировать по убыванию" rel="nofollow"> </a><a href="'.$ref.($i+1).'" class="sorter_asc'.($sortDesc?'_enabled':'').'" title="Сортировать по возрастанию" rel="nofollow"> </a></span></td>'."\n";
		else
			$Headers[] = '<td><a href="'.$ref.($i+1).'" class="sorter" title="Сортировать" rel="nofollow">'.$tabHeaders[$i].'</a></td>'."\n";
	}
	return $j;
}

function di($s) {
	echo ('<pre>');
	print_r($s);
	die();
}

function getBrowser() {
	$r =  $_SERVER['HTTP_USER_AGENT'];
	$arr = array("|Opera|", "|Chrome/|", "|Firefox/|", "|Navigator/|", "|MSIE|", "|MAXTHON|");
	$volums = array("Opera", "Google Chrome", "Firefox", "Netscape Navigator", "Internet Explorer", "Maxton");
	for($i=0; $i<count($arr); $i++){
		if(preg_match($arr[$i], $r))
			$a = $volums[$i];
	}
	if(!isset($a)) return 'Other';
	else return $a;
}

function generateCode($length=6){
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	$clen = strlen($chars)-1;
	while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,$clen)];
	}
	return $code;
}

function DataParser($dbDate){
	list($date, $time) = explode(' ', $dbDate);
	list($y, $m, $d) = explode('-', $date);
	return $d.'.'.$m.'.'.$y;
}

function priceParser($price){
	list($whole, $fractional) = explode('.', $price);
	$result = preg_replace('/(\d)(?=(?:[0-9]{3})+(?![0-9]))/', '\1&nbsp;', $whole);
	if ($fractional > 0)
		$result .= ','.$fractional;
	return $result;
}

function translit($word, $max_char=100){
	$word = mb_strtolower($word, 'windows-1251');
	$word = preg_replace("/[^a-zA-Zа-яА-Я0-9\s\-\*]/", "", strip_tags($word));
	$word = preg_replace("/(\s)+/", " ", trim($word));
	preg_match_all("/[\d+][x|x][\d+]/", $word, $Delimetr);
	if (count($Delimetr[0])>0){
		for ($i=1; $i<=count($Delimetr[0]); $i++)
			$word = preg_replace("/(\d+)(x|x)(\d+)/", "$1-$3", $word);
	}
	$tr = array(
		'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'yo','ж'=>'zh','з'=>'z',
		'и'=>'i','й'=>'j','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p',
		'р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'kh','ц'=>'c','ч'=>'ch',
		'ш'=>'sh','щ'=>'shh','ь'=>'','ы'=>'y','ъ'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
		'-'=>'-', ' '=>'-', '*'=>'-'
	);
	$word = strtr($word, $tr);
	if ($max_char > 0)
		$word = substr($word, 0, $max_char);
	return $word;
}

function cutEmail($email){
	if (mb_strlen($email, 'CP1251') > 30)
		$email = mb_substr($email, 0, 30, 'CP1251').'...';
	return $email;
}

function getPhoneFormat($in_code, $in_number){
	if ($in_code == '8800'){
		$out_phone = '8-800-'.substr_replace(substr_replace($in_number, '-', 4, 0), '-', 2, 0);
	}elseif ($in_code{0} == '9'){
		$out_phone = $in_code.'-'.substr_replace(substr_replace($in_number, '-', 4, 0), '-', 2, 0);
	}else{
		$out_phone = '('.$in_code.') '.substr_replace(substr_replace($in_number, '-', -4, 0), '-', -2, 0);
	}
	return $out_phone;
}

function slashPhone ($fullphone) {
	$slashed = array();
	$fullphone = preg_replace(array('/\-/', '/\(/', '/\)/'), array('', '', ''), trim($fullphone));
	if (preg_match('/ /', $fullphone)){
		list($slashed[0], $slashed[1]) = explode(' ', $fullphone);
	}else{
		if (strlen($fullphone) == 10){
			$slashed[0] = substr($fullphone, 0, 3);
			$slashed[1] = substr($fullphone, 3, 10);
		}else{ // 8800
			$slashed[0] = substr($fullphone, 0, 4);
			$slashed[1] = substr($fullphone, 4, 11);
		}
	}

	return $slashed;
}

function GetLastRubrics($parent_id, $lvl){
	global $i;
	global $id_root;
	global $Rubrics;
	global $lvl;
	$lvl++;
	$qRes = SqlQuery("Select `id`, `id_parent`, `name` From `RUBRICS` Where `id_parent`='".mysql_real_escape_string($parent_id)."' and `visible`='1' Order by `sort_order`;");
	if (mysql_num_rows($qRes) > 0){
		while ($Row = mysql_fetch_row($qRes)){
			$id = $Row[0];
			$qRes_count = SqlQuery("Select * From `RUBRICS` Where `id_parent`='".mysql_real_escape_string($id)."' and `visible`='1';");

			if ($lvl === 1){
				$Rubrics[$i][0] = '0';
				$Rubrics[$i][1] = $id_root = $Row[0];
				$Rubrics[$i][2] = $Row[2];
				$i++;
			}elseif (mysql_num_rows($qRes_count) === 0){
				$Rubrics[$i][0] = $id_root;
				$Rubrics[$i][1] = $Row[0];
				$Rubrics[$i][2] = $Row[2];
				$i++;
			}

			@mysql_free_result($qRes_count);
			GetLastRubrics($id, $lvl);
			$lvl--;
		}
	}
	@mysql_free_result($qRes);
}

function GetCityException($idReg){
	$city_name = 'N\A';
	switch ($idReg){
		case '91':{
			$city_name = 'Москва';
			break;
		}
		case '84':{
			$city_name = 'Санкт-петербург';
			break;
		}
	}
	return $city_name;
}

function lastInsertId(){
	$query_str = "SELECT LAST_INSERT_ID() AS last_id";
	$query = mysql_query($query_str);
	if (mysql_num_rows($query)){
		$query_result = mysql_fetch_array($query, MYSQL_ASSOC);
	}
	$result = $query_result['last_id'];
	return $result;
}

function deleteImages($path, $file_name, $ext, $suffix=''){
	$allowed_ext = array('jpg', 'png');
	$allowed_suffix = array('', '_medium', '_small');
	if (is_numeric($file_name) && $file_name > 0 && in_array($ext, $allowed_ext) && in_array($suffix, $allowed_suffix)){
		$full_file_name = $path.$file_name.$suffix.'.'.$ext;
		if (file_exists($full_file_name)){
			chmod($full_file_name, 0555);
			unlink($full_file_name);
		}
	}
}

function getTranslitFromId($id){
	$qRes = SqlQuery("Select `translit` From `CLIENTS` Where `id`='".mysql_real_escape_string($id)."';");
	if (mysql_num_rows($qRes) === 1){
		$Row = mysql_fetch_row($qRes);
		if ($Row[0] !== null)
			return (CheckTranslit($Row[0])) ? $Row[0] : '';
		else
			return '';
	}else
		return '';
}

function getClientInfo($id){
	if (isset($id) && !empty($id) && CheckIntegerUnsign($id)){
		$Company_info = array();
		$qRes = SqlQuery("
			Select C.`name`, C.`positions`, C.`logo`, C.`address`, C.`translit`
			From `CLIENTS` C
			Where C.`id`='".mysql_real_escape_string($id)."';
		");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$Row[0] = preg_replace("'<script[^>]*?>.*?</script>'si", "", $Row[0]);
			@list($nm, $opf) = explode(',', $Row[0]);
			$Company_info['name'] = (isset($opf)) ? $opf.' '.$nm : $nm;
			$Company_info['t_name'] = ($Row[1] > 10) ? 'ttt '.$Row[1].' ttt' : 'ttttt';
			$Company_info['t_product_limit'] = $Row[1];
			$Company_info['end'] = ($Row[3]!='') ? false : true;
			$Logo = $Row[2];
		}else
			Show_Critical_Error('Ошибка 1');
		@mysql_free_result($qRes);

		$qRes = SqlQuery("Select count(*) From `STR` Where `client`='".mysql_real_escape_string($id)."' and `active`='1';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$Company_info['count_price'] = $Row[0];
		}else
			Show_Critical_Error('Ошибка 2');
		@mysql_free_result($qRes);

		$qRes = SqlQuery("Select count(*) From `STR` Where `client`='".mysql_real_escape_string($id)."' and `active`='0';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$Company_info['count_archive'] = $Row[0];
		}else
			Show_Critical_Error('Ошибка 3');
		@mysql_free_result($qRes);

		if (mb_strlen($Company_info['name'], 'CP1251') > 46)
			$Company_info['name'] = mb_substr($Company_info['name'], 0, 46, 'CP1251').'...';

		$Company_info['t_product_add']  = ($Company_info['t_product_limit'] != 0) ? ($Company_info['count_price'] < $Company_info['t_product_limit']) : true;
		$Company_info['t_product_left'] = $Company_info['t_product_limit'] - $Company_info['count_price'];

		if ($Logo != ''){
			$imgsize = getimagesize('../logo/'.$Logo);
			$width = $imgsize[0];
			$height = $imgsize[1];
			if ($width > 260){
				$width_tmp = $width;
				$width = 260;
				$difference = (int)(round(($width_tmp - 260) / ($imgsize[0] / $imgsize[1])));
				$height = $height - $difference;
			}
			if ($height > 90){
				$height_tmp = $height;
				$height = 90;
				$difference = (int)(round(($height_tmp - 90) / ($imgsize[1] / $imgsize[0])));
				$width = $width - $difference;
			}
			$Company_info['logo'] = $Logo;
			$Company_info['logo_width'] = $width;
			$Company_info['logo_height'] = $height;
		}else{
			$Company_info['logo'] = $Company_info['logo_width'] = $Company_info['logo_height'] = '';
		}

		return $Company_info;
	}else
		Show_Critical_Error('Ошибка 4');
}

function CheckIDTranslit($inStr) {
	if ($inStr[0] == '_'){
		$inStr = substr($inStr, 1);
		if (CheckTranslit($inStr)){
			$qRes = SqlQuery("Select `id` From `CLIENTS` Where `translit`='".mysql_real_escape_string($inStr)."';");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				if (CheckIntegerUnsign($Row[0])){
					$data[0] = $Row[0];
					$data[1] = $inStr;
					$data[2] = false;
				}else
					Show_Critical_Error('Ошибка 6');
			}else{
				$data[0] = 0;
				$data[1] = '';
				$data[2] = '';
			}
			@mysql_free_result($qRes);
		}else{
			$data[0] = 0;
			$data[1] = '';
			$data[2] = '';
		}
	}else{
		if (CheckIntegerUnsign($inStr)){
			$qRes = SqlQuery("Select `translit` From `CLIENTS` Where `id`='".mysql_real_escape_string($inStr)."';");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				if ($Row[0] !== null){
					if (CheckTranslit($Row[0])){
						$data[0] = $inStr;
						$data[1] = $Row[0];
						$data[2] = true;
					}else
						Show_Critical_Error('Ошибка 7');
				}else{
					$data[0] = $inStr;
					$data[1] = '';
					$data[2] = false;
				}
			}else{
				$data[0] = 0;
				$data[1] = '';
				$data[2] = '';
			}
			@mysql_free_result($qRes);
		}else{
			$data[0] = 0;
			$data[1] = '';
			$data[2] = '';
		}
	}
	return $data;
}

function getClientContent($id_company, $isOwner = false){
	$products_qty = 0;
	$sity_name = $sity_name_where = '';
	$Client = $Client_phones = $Rubrics = $Name = $Logo = array();

	$qRes = SqlQuery("
		Select C.`name`, C.`address`, C.`logo`, C.`coord`, C.`map_zoom`,
			(Select `full_phone` From `CLIENT_PHONES` Where `id_client` = C.`id` Order by `sort_order` Limit 1) as phone,
			(Select `email` From `CLIENT_EMAILS` Where `id_client` = C.`id` Order by `sort_order` Limit 1) as email,
			(Select `site` From `CLIENT_SITES` Where `id_client` = C.`id` Order by `sort_order` Limit 1) as site,
			C.`region`, C.`city`, C.`ya_verification`,
			(Select `city_code` From `CLIENT_PHONES` Where `id_client` = C.`id` Order by `sort_order` Limit 1) as c_code
		From `CLIENTS` C
		Where C.`id` ='".mysql_real_escape_string($id_company)."';
	");
	if (mysql_num_rows($qRes)===1){
		$Client = mysql_fetch_row($qRes);
	}
	@mysql_free_result($qRes);

	$qRes = SqlQuery("Select `full_phone`, `city_code` From `CLIENT_PHONES` Where `id_client` = '".mysql_real_escape_string($id_company)."' and `sort_order` != '1' Order by `sort_order` Limit 2;");
	if (mysql_num_rows($qRes)>0){
		while ($Rows = mysql_fetch_row($qRes))
			$Client_phones[] = $Rows;
	}
	@mysql_free_result($qRes);

	if ($isOwner)
		$sql = "Select S.`rubric`, R.`new_url`, R.`name`, (Select count(S2.`id`) From `STR` S2 Where S2.`client` = '".mysql_real_escape_string($id_company)."' and S2.`rubric` = S.`rubric` and S2.`active` = '1') From `STR` S join  `RUBRICS` R on S.`rubric` = R.`id` Where S.`client` = '".mysql_real_escape_string($id_company)."' and S.`active` = '1' Group by S.`rubric` Order by R.`name`;";
	else
		$sql = "Select R.`id`, R.`new_url`, R.`name`, C.`cnt_str` From `CLIENT_RUBRICS` C join `RUBRICS` R on R.`id`=C.`rubric` Where C.`client`='".mysql_real_escape_string($id_company)."' and C.`cnt_str`>0 Order by R.`name`;";
	$qRes = SqlQuery($sql);
	if (mysql_num_rows($qRes)>0){
		while ($Rows = mysql_fetch_row($qRes))
			$Rubrics[] = $Rows;
	}
	@mysql_free_result($qRes);

	foreach ($Rubrics as $rub)
		$products_qty += $rub[3];

	if ($Client[2] != ''){
		$imgsize = getimagesize('../logo/'.$Client[2]);
		$width = $imgsize[0];
		$height = $imgsize[1];
		if ($width > 300){
			$width_tmp = $width;
			$width = 300;
			$difference = (int)(round(($width_tmp - 300) / ($imgsize[0] / $imgsize[1])));
			$height = $height - $difference;
		}
		if ($height > 100){
			$height_tmp = $height;
			$height = 100;
			$difference = (int)(round(($height_tmp - 100) / ($imgsize[1] / $imgsize[0])));
			$width = $width - $difference;
		}
		$Logo[0] = $width;
		$Logo[1] = $height;
	}else{
		$Logo[0] = '';
		$Logo[1] = '';
	}

	$Name = explode(',', $Client[0]);

	if ($Client[9] != '0'){
		$qRes = SqlQuery("Select C.`name`, L.`name_where` From `CITIES` C join `LOCATION` L on C.`id` = L.`city` Where C.`id` = '".mysql_real_escape_string($Client[9])."';");
		if (mysql_num_rows($qRes) === 1){
			$Row = mysql_fetch_row($qRes);
			$sity_name = $Row[0];
			$sity_name_where = ($Row[1] !== null) ? $Row[1] : $sity_name;
		}
		@mysql_free_result($qRes);
	}else{
		if ($Client[8] == '84'){
			$sity_name = 'Санкт-петербург';
			$sity_name_where = 'В Санкт-петербурге';
		}
		if ($Client[8] == '91'){
			$sity_name = 'Москва';
			$sity_name_where = 'В Москве';
		}
	}

	$Data = array($Client, $Name, $Logo, $Rubrics, $products_qty, $sity_name, $sity_name_where, $Client_phones);
	return $Data;
}

function exec_script($url, $params = array()){
	$parts = parse_url($url);

	if (!$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80)){
		return false;
	}

	$data = http_build_query($params, '', '&');

	fwrite($fp, "POST " . (!empty($parts['path']) ? $parts['path'] : '/') . " HTTP/1.1\r\n");
	fwrite($fp, "Host: " . $parts['host'] . "\r\n");
	fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
	fwrite($fp, "Content-Length: " . strlen($data) . "\r\n");
	fwrite($fp, "Connection: Close\r\n\r\n");
	fwrite($fp, $data);
	fclose($fp);

	return true;
}

function ShowCounterYandex() {
	global $translit_company;
	$subdomain = ((isset($translit_company))&&($translit_company!='')?$translit_company:'www');
	$id_user = ((isset($_SESSION['user']['id'])&&($_SESSION['user']['id']>0))?$_SESSION['user']['id']:'none');
	if (strpos($_SERVER['REQUEST_URI'],'/kabinet')===0)
		$section = 'kabinet';
	elseif ($subdomain!='www')
		$section = 'subdomain';
	else
		$section = 'site';
	$counter = <<<EoL
<!--noindex-->
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            var yaParams = {subdomain:"$subdomain",id_user:"$id_user",section:"$section"};
            w.yaCounter13055926 = new Ya.Metrika({id:13055926,
                    webvisor:true,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    params: yaParams});
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/13055926" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
<!--/noindex-->
EoL;
	echo $counter;
}

function ShowCounterGoogle() {
	global $translit_company;
	$subdomain = ((isset($translit_company))&&($translit_company!='')?$translit_company:'www');
	$id_user = ((isset($_SESSION['user']['id'])&&($_SESSION['user']['id']>0))?$_SESSION['user']['id']:'none');
	if (strpos($_SERVER['REQUEST_URI'],'/kabinet')===0)
		$section = 'kabinet';
	elseif ($subdomain!='www')
		$section = 'subdomain';
	else
		$section = 'site';
	$counter = <<<EoL
<!--noindex-->
<!-- Google Analytics -->
<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-22729203-1']);
  _gaq.push(['_setSiteSpeedSampleRate', 10]);
  _gaq.push(['_setCustomVar',1,'subdomain','$subdomain',3]);
  _gaq.push(['_setCustomVar',2,'id_user','$id_user',3]);
  _gaq.push(['_setCustomVar',3,'section','$section',3]);
  _gaq.push(['_setDomainName', 'biznesurfo.ru']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

  window.onerror = function(msg, url, line) {
    var preventErrorAlert = true;
    _gaq.push(['_trackEvent', 'JS Error', msg, navigator.userAgent + ' -> ' + url + " : " + line, 0, true]);
    return preventErrorAlert;
};
</script>
<!-- /Google Analytics -->
<!--/noindex-->
EoL;
	echo $counter;
}

?>
