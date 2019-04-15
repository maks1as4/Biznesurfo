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

// �������� ����������� ��� ������� window.onLoad
    YMaps.jQuery(function () {
      var map = new YMaps.Map(YMaps.jQuery("#YMapsID")[0]);
      map.setCenter(new YMaps.GeoPoint(60.625353, 56.847182), 16);
      map.enableScrollZoom();

      var zoom = new YMaps.Zoom({
                  customTips: [
                      { index: 10, value: "�����" },
                      { index: 14, value: "�����" },
                      { index: 16, value: "���" }
                  ]
              });
      map.addControl(zoom);
      map.addControl(new YMaps.TypeControl());
      map.addControl(new YMaps.ToolBar());
      map.addControl(new YMaps.ScaleLine());

      var placemark = new YMaps.Placemark(new YMaps.GeoPoint(60.625490, 56.846688));
//      placemark.setIconContent('uptc');
      placemark.name = "��������� �������";
      placemark.description = "<i>���� ��������</i>";
      map.addOverlay(placemark);
      placemark.openBalloon();
	});
EoL;
}
$cityFormAction = $pagNam;
$Crumbs[0][0] = '/';
$Crumbs[0][1] = '�������';
$Crumbs[1][0] = '';
$Crumbs[1][1] = '��������';
$crumbsQty = 2;
$keyws = '����� �������� ����� ������� �������� ���������� ��������� �������';
$descr = '���������-�������� ���������� ����������� ��������� �������, ����� �������, ����������� �����';
$title = '��������: �����, ����� �������, �������� �������� ��������� �������';

$Expos = GetExposBlock();
$expoQty = count($Expos);
$News = GetNewsBlock();
$newsQty = count($News);

require_once '../includs/contacts.html';

?>