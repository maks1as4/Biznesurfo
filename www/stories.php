<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$idStory = 0;
$urlStory = '';
if (count($_GET)>0) {
	if ((isset($_GET['sid']))&&($_GET['sid']!='')&&(CheckIntegerUnsign($_GET['sid']) ))
		$idStory = $_GET['sid'];
	if ((isset($_GET['surl']))&&($_GET['surl']!='')&&(CheckStr($_GET['surl'], 6, 0, false) )) {
		$urlStory = $_GET['surl'];
		$idStory = -1;
	}
	if ((isset($_GET['rcid']))&&($_GET['rcid']!='')) {
		$flCity = $_GET['rcid'];
		$s = $flCity[0];
		$idReg = substr($flCity, 1);
		if (!((($s=='r')||($s=='c'))&&(CheckIntegerZeroUnsign($idReg)))) {
			$idReg = 0;
			$flCity = '';
		}
		$isReg = ($s=='r');
	}
}
if ($idStory==0)
	require_once 'page404.php';

if ($idStory==-1) {
	$s = 'UPPER(S.url)=UPPER('."'$urlStory')";
	$idStory = $urlStory;
} else
	$s = 'S.id='.$idStory;
$pagNam = 'stories/'.$idStory.'.html';
$Cities = GetCities(); // Чтобы выводились все города и регионы

$numRows = 0;
$stName = '';
$stBody = '';
$qRes = SqlQuery('Select COALESCE(S.keywords, ""), COALESCE(S.descript, ""), COALESCE(S.title, ""), S.head, S.text, S.rubric From STORIES S Where '.$s.';');
$numRows = mysql_num_rows($qRes);
if (($numRows!=0)&&($qRes)) {
	if ($Rows = mysql_fetch_row($qRes)) {
		$keyws = $Rows[0];
		$descr = $Rows[1];
		$title = $Rows[2].' - Индустрия Бизнеса';
		$stName = $Rows[3];
		$stBody = $Rows[4];
		$idRubric = $Rows[5];
	}
	@mysql_free_result($qRes);
} else
	require_once 'page404.php';

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Товары и услуги';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Тематические статьи';
$crumbsQty = 2;
$tagH1 = $stName;
$News = GetNewsBlock();
$newsQty = count($News);
$Expos = GetExposBlock();
$expoQty = count($Expos);
/*
  GetRubChilds($idRubric);
  if (count($arrRubrics)>0) {
  $RightTextMods = GetRightTextBlocksFromArray();
  $RightBanners = GetRightBannersFromArray();
  $rbansQty = count($RightBanners);
  if ($rbansQty>2)	$rbansQty = 2;
  $rTblocksQty = count($RightTextMods);
  if ($rTblocksQty>4)	$rTblocksQty = 4;
  }
 */
$isDirect = true;
require_once '../includs/stories.html';
?>