<?php

//error_reporting (E_ERROR | E_WARNING | E_PARSE);

//require_once '../../includs/level2/head.php';
require_once '../../includs/head.php';
require_once '../../funcs/rc_define.php';
require_once('lib/mode.php');

$b2b = & new b2bcontext('config.php',1);	
//    header("Last-Modified: ".gmdate("D, d M Y H:i:s", time() - 600)." GMT");
# ������������� ����������������� (������ # � ������) � 3� ������ ���� �����, ��� ���� ����� ��� ���� ����������� �������������� ������������.
# ��� ���������� ���� ����-����� ��������� ��������� ��������, ���� �� ������ � ��� ����

$isTendersPage = true;

$pagNam = 'tenders/main';
$Cities = GetCities();
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;

$Crumbs[0][0] = '/';
$Crumbs[0][1] = '�������';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '���������� ��������';
$crumbsQty = 2;
$News = GetNewsBlock();
$newsQty = count($News);
$Expos = GetExposBlock();
$expoQty = count($Expos);

$isDirect = true;

require_once "../../includs/level2/head.html"; 
?>
				<div class="path">
<?php
 for($i=0;$i<$crumbsQty;$i++)
	echo ((($Crumbs[$i][0]=='')?'':'<a href="'.$Crumbs[$i][0].'">').$Crumbs[$i][1].(($Crumbs[$i][0]=='')?'':'</a>')."\n");
?>
				</div><!-- /path -->
				<div class="tenders clear">
					<div class="about_tenders">
������ �������� ������, ������ ������ ��� ��� ��������� �������� �������������?<br />
��� ������ ������������� ���������� ������ � ������ � ��������. �������������������, �� ������� ����������� ������ �� �������������� ����� � �������� ������� �� ���������� � �������� �������� � �����. � ������ �������������� �������� ��, ������ ������������ �������� � ����� �� ������, ������� ����������� ��� ������� ������� �� ��������� �������.<br /> 
����� ��������� �����, � ������� ������ �������� ����� ������������� ���� ������ ����������� �� ����������� ������ � ������. ����� �� ������ ���������� ���� ������, ������� ����� ������� �� ����� �������.<br /><br />
                    </div> <!-- /about tenders -->
					<?php echo $b2b->b2bcontext_content;?>
				</div>
				<br /><br /><br />
				
			</div><!-- /inner -->
		</div><!-- /content -->
	
<?php require_once "../../includs/level2/right.html"; ?>

	</div><!-- /body -->
<?php require_once "../../includs/foot.html"; ?>

</div><!-- /page -->

</body>
</html>