<?php

require_once '../includs/head.php';
require_once '../funcs/rc_define.php';

$isContPage = true;

$pagNam = 'contacts';
if ($flCity!='')
	$findParStr = '?rcid='.$flCity;

$cufonRepl = "Cufon.replace('div.topmenu ul li a,div.right h4', {hover: true});";
if ($isShowES) {
	$addScripts = '<script src="http://api-maps.yandex.ru/1.1/index.xml?key='.$yandex_key.'"
	type="text/javascript"></script>';
	$jsFuncs = <<<EoL

// Создание обработчика для события window.onLoad
    YMaps.jQuery(function () {
      var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);
      map.setCenter(new YMaps.GeoPoint(60.625353, 56.847182), 16);
      map.enableScrollZoom();

      var zoom = new YMaps.Zoom({
                  customTips: [
                      { index: 10, value: "город" },
                      { index: 14, value: "улица" },
                      { index: 16, value: "дом" }
                  ]
              });
      map.addControl(zoom);
      map.addControl(new YMaps.TypeControl());
      map.addControl(new YMaps.ToolBar());
      map.addControl(new YMaps.ScaleLine());

      var placemark = new YMaps.Placemark(new YMaps.GeoPoint(60.625490, 56.846688));
//      placemark.setIconContent('uptc');
      placemark.name = "Индустрия Бизнеса";
      placemark.description = "<i>Офис компании</i>";
      map.addOverlay(placemark);
      placemark.openBalloon();
	});
EoL;
}
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = 'Главная';
$Crumbs[1][0] = '';
$Crumbs[1][1] = 'Контакты';
$crumbsQty = 2;
$keyws = 'адрес телефоны карта проезда контакты справочник Индустрия Бизнеса';
$descr = 'Телефонно-адресная информация справочника Индустрия бизнеса, схема проезда, электронная почта';
$title = 'Контакты: адрес, карта проезда, телефоны компании Индустрии бизнеса';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);

require_once '../includs/contacts.html';

?>