<?php
session_start();
include "lib.image.php";

$str = "012345678";
//$str = 'ABCDEFGHLKPOMNZWQX';
for($i = 0; $i < 4; $i++)
{
	$code[] = substr($str, rand(0, strlen($str) - 1), 1);
}

$_SESSION['umm_last_cim_code'] = $text = implode("", $code);

$image_width = 89;
$image_height = 48;
//$font_uri = array('arialbd.ttf','comicbd.ttf', 'comic.ttf', 'georgia.ttf', 'gothic.ttf', 'courbd.ttf', 'sylfaen.ttf');
$font_uri = array('georgia.ttf');
$font_size = 20;
$font_depth = 1;
$bgcolor = array(251,251,251);
$color = array(22, 39, 100);
$color2 = array(62, 92, 168);

$img = new CryptPng($image_width, $image_height);

if($img->create())
{
//	$img->apply(new GradientEffect($bgcolor[0], $bgcolor[1], $bgcolor[2]));
//	$img->apply(new GridEffect(10, $bgcolor[0], $bgcolor[1], $bgcolor[2]));
	$img->setFonts($font_uri);
//	$img->setFonts($font_uri[(rand(0, count($font_uri)-1))]);
//	$img->apply(new DotEffect());
	$t = new TextEffect($text, $font_size, $font_depth, $color, $color2);
	/*
	foreach($font_uri as $k => $v)
	{
		$t->addFont($v);
	}
	*/
	$img->apply($t);
	$img->render();
}	
?>

