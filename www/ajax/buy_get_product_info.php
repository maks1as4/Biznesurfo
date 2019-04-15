<?php

require_once "../../includs/head.php";

$check = false;
$product_name = '';

if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){
	$id_product = $_REQUEST['product'];
	$id_client = $_REQUEST['supplier'];
	$hash = $_REQUEST['code'];
	if (CheckIntegerUnsign($id_product) && CheckIntegerUnsign($id_client) && preg_match('/^[a-z0-9]{32}$/', $hash)){
		if (md5($id_product.md5($id_client).$secret_salt) == $hash){
			$qRes = SqlQuery("Select `name`, `translit`, `dop`, `price`, `dog` From `STR` Where `id` = '".mysql_real_escape_string($id_product)."' and `client` = '".mysql_real_escape_string($id_client)."' and `active` = '1';");
			if (mysql_num_rows($qRes) === 1){
				$Row = mysql_fetch_row($qRes);
				$product_name = $Row[0];
				$product_price = ($Row[4] == '0') ? priceParser($Row[3]).' руб.' : '';
				if ($product_price!='' && $Row[2]=='1') $product_price = 'от '.$product_price;
				$check = true;
			}
			@mysql_free_result($qRes);
		}
	}
}

$json_array = array('name'=>iconv('cp1251', 'utf-8', $product_name), 'price'=>iconv('cp1251', 'utf-8', $product_price), 'check'=>$check);
echo json_encode($json_array);

?>