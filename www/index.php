<?php
require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$isMainPag = true;
$pagNam = '';
$empty = false;
$s = '';
if ($idReg>0) {
 if ($isReg)
  $s = ' and RC.region='.$idReg;
 else
  $s = ' and RC.city='.$idReg;
}

$Razd = array ();
$Rubs = array ();
$qty = 'RC.qty'.($forPred?'_c':'');
$qTxt = 'Select R.id, R.name, SUM('.$qty.'), R.new_url From RUBRICS R join RUBRICS_CNT RC on RC.rubric=R.id Where R.level = 1'.$s.' and '.$qty.'>0 Group by R.id Order by R.sort_order;';
$qRes = SqlQuery($qTxt);
$numRows = mysql_num_rows($qRes);
$i = 0;
if (($numRows>0)&&($qRes)) {
 while ($Rows = mysql_fetch_row($qRes)) {
  $Razd[$i][0] = $Rows[0]; // id
  $Razd[$i][1] = $Rows[1]; // name
  $Razd[$i][2] = $Rows[2]; // count
  $Razd[$i][3] = $Rows[3]; // url
  $qRes2 = SqlQuery('Select distinct R.id, R.name, R.new_url From RUBRICS R join RUBRICS_CNT RC on RC.rubric=R.id Where R.id_parent = '.$Rows[0].$s.' and R.visible=1 and '.$qty.'>0 Order by R.sort_order;');
  $j = 0;
  if ((mysql_num_rows($qRes2)>0)&&($qRes2)) {
   while ($Rows2 = mysql_fetch_row($qRes2)) {
    $Rubs[$i][$j][0] = $Rows2[0]; // id
    $Rubs[$i][$j][1] = $Rows2[1]; // name
    $Rubs[$i][$j][2] = $Rows2[2]; // url
    $Rubs[$i][$j][3] = $forPred?'firms/':'prices/';
    $j++;
   }
   @mysql_free_result($qRes2);
  }
  $Razd[$i++][4] = $j;
 }
} else
 //require_once 'page404.php';
 $empty = true;

@mysql_free_result($qRes);

$Letters = GetAlphabit();

$botBan = '';
if ((!isset($_SESSION['bbn']))||(!CheckIntegerUnsign($_SESSION['bbn'])))
 $_SESSION['bbn'] = 0;
$sql = '(select B.name, B.id from BANNERS B where B.kind=1 and B.visible=1 and B.id>'.$_SESSION['bbn'].' order by id limit 1) '.
  'union '.
  '(select B.name, B.id from BANNERS B where B.kind=1 and B.visible=1 and B.id<='.$_SESSION['bbn'].' order by id limit 1)';
$qRes = SqlQuery($sql);
if (($qRes)&&($Rows = mysql_fetch_row($qRes))) {
 $botBan = $Rows[0];
 $id_bot_banner = $Rows[1];
 $_SESSION['bbn'] = $id_bot_banner;
 if ((!$isRobot)&&($isLogStat)) {
  $sql = 'insert into COUNTERS_BT values ('.
    $id_bot_banner.','.
    '1,'.  // is_banner
    'curdate(),'.
    '1'.  //  cnt
    ') '.
    'on duplicate key update cnt=cnt+1';
  SqlQuery ($sql);
 }
}
@mysql_free_result($qRes);

$Tags = GetTagsCloud();

if ($flCity != '')
 $findParStr = '?rcid='.$flCity;

$cityFormAction = ($forPred)?'firms':'/';
$tagH1 = ($forPred)?'Предприятия':'Товары и услуги';
$razdQty = count($Razd);

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);
$RightBanners = array ();
GetRightBans();
$rbansQty = count($RightBanners);

require_once '../includs/index.html';

?>