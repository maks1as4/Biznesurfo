<?php

set_time_limit(0);

mysql_connect('localhost', 'biznesurfo', 'wrbU3n77tY7CSrnd') or die (mysql_error());
mysql_select_db('biznesurfo') or die (mysql_error());
mysql_query('set character_set_client	= "cp1251"');
mysql_query('set character_set_results	= "cp1251"');
mysql_query('set collation_connection	= "cp1251_general_ci"');

function uri2absolute($link, $base){
	if (!preg_match('~^(http://[^/?#]+)?([^?#]*)?(\?[^#]*)?(#.*)?$~i', $link.'#', $matchesLink)){
		return false;
	}
	if (!empty($matchesLink[1])){
		return $link;
	}
	if (!preg_match('~^(http://)?([^/?#]+)(/[^?#]*)?(\?[^#]*)?(#.*)?$~i', $base.'#', $matchesBase)){
		return false;
	}
	if (empty($matchesLink[2])){
		if (empty($matchesLink[3])){
			return 'http://'.$matchesBase[2].$matchesBase[3].$matchesBase[4];
		}
		return 'http://'.$matchesBase[2].$matchesBase[3].$matchesLink[3];
	}
	$pathLink = explode('/', $matchesLink[2]);
	if ($pathLink[0] == ''){
		return 'http://'.$matchesBase[2].$matchesLink[2].$matchesLink[3];
	}
	$pathBase = explode('/', preg_replace('~^/~', '', $matchesBase[3]));
	if (sizeOf($pathBase) > 0){
		array_pop($pathBase);
	}
	foreach ($pathLink as $p){
		if ($p == '.'){
			continue;
		}elseif ($p == '..'){
			if (sizeOf($pathBase) > 0){
				array_pop($pathBase);
			}
		}else{
			array_push($pathBase, $p);
		}
	}
	return 'http://'.$matchesBase[2].'/'.implode('/', $pathBase).$matchesLink[3];
}

function unSlashed($url){
	$parse = parse_url($url);
	if ($parse['host'] == 'www.biznesurfo.ru'){
		$end = substr($parse['path'], -1);
		if ($end == '/') $url = substr($url, 0, strlen($url)-1);
	}
	return $url;
}

function is404($url){
	$result = get_headers($url);
	if (($result[0] == 'HTTP/1.1 404 Not Found') || ($result[0] == 'HTTP/1.0 303 See Other'))
		return true;
	else
		return false;
}

$Stories = array();
$qRes = mysql_query('Select S.id, S.text From STORIES S Order by S.id;');
if ((mysql_num_rows($qRes)>0)&&($qRes)){
	while ($Rows = mysql_fetch_row($qRes))
		$Stories[] = $Rows;
	@mysql_free_result($qRes);
}

$i = 0;
$Bad_links = array();
foreach ($Stories as $story){
	preg_match_all('/(<a[^>]*)href=(\"?)([^\s\">]+?)(\"?)([^>]*>)/ismU', $story[1], $res);
	if (!empty($res[3])){
		foreach ($res[3] as $link){
			$relink = uri2absolute($link, 'http://www.biznesurfo.ru/');
			$relink = unSlashed($relink);
			if (is404($relink)){
				$Bad_links[$i][0] = $story[0];
				$Bad_links[$i][1] = $link;
				$i++;
			}
		}
	}
}

if (!empty($Bad_links)){
	echo 'Всего битых ссылок - '.count($Bad_links).'<hr />';
	foreach ($Bad_links as $bl)
		echo 'ID - '.$bl[0].' | '.$bl[1].'<br />';
}else
	echo 'нет битых ссылок';

?>
