<?php

//error_reporting (E_ERROR | E_WARNING | E_PARSE);

//require_once '../../includs/level2/head.php';
require_once '../../includs/head.php';
require_once '../../funcs/rc_define.php';
require_once('lib/mode.php');

$b2b = & new b2bcontext('config.php',1);	
//    header("Last-Modified: ".gmdate("D, d M Y H:i:s", time() - 600)." GMT");
# Рекомендуется раскомментировать (убрать # в начале) у 3х идущих выше строк, для того чтобы ваш сайт эффективнее индексировался поисковиками.
# Или установите свою дату-время последней модерации страницы, если вы знаете о чем речь

$isTendersPage = true;

$pagNam = 'tenders/main';
$Cities = GetCities();
$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
$cityFormAction = $pagNam;

$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Рубрикатор тендеров';
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
Хотите заказать услуги, купить товары или Вам требуется надежный производитель?<br />
Наш портал предоставляет уникальный сервис – доступ к тендерам. Зарегистрировавшись, Вы сможете просмотреть заказы на предоставление услуг и поставку товаров по заявленным в тендерах условиям и ценам. В списке представленных тендеров Вы, выбрав деятельность компании и нажав на ссылку, сможете просмотреть все текущие тендеры по выбранной отрасли.<br /> 
Чтобы упростить поиск, в разделе «Поиск тендеров» можно отфильтровать весь список предложений по необходимой услуге и товару. Также Вы можете разместить свой тендер, оформив форму запроса по Вашей закупке.<br /><br />
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