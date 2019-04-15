<?php
if (!function_exists('file_put_contents')){//exist in php5 only
function file_put_contents($filename, $data, $flags=0, $context='')
{
	if(is_array($data))$data=implode('',$data);
	$fh = fopen($filename, 'w');
    $ret=fwrite($fh, $data);
    fclose($fh);
	return $ret;
}
}

if (!function_exists('array_fill_keys')){ //exist in php>5.2.0 only
function array_fill_keys($keys, $value)
	{
		$ret=array();
		foreach($keys as $key)
			$ret[$key]=$value;
		return $ret;
	}
}

if (!function_exists('http_build_query')){ //exist in php5 only
function http_build_query($params, $numeric_prefix="arg", $arg_separator="&")
	{
		$ret=array();
		if(!is_array($params))$params=array();
		foreach($params as $key=>$val)
		{
			if(is_int($key))$key=$numeric_prefix.$key;
			$ret[]=urlencode($key)."=".urlencode($val);
		}
		return implode($arg_separator,$ret);
	}
}

if(!class_exists('B2BContext_Config'))
{
class B2BContext_Config
{
	var $config;	
	function B2BContext_Config($config_path, $add_params='')
	{		 
		if(!is_array($add_params))$add_params=array();
		$myPath=dirname(realpath($config_path));
		$dirsep='/';		
		$doc_root = preg_replace("/\\\/",'/',$_SERVER['DOCUMENT_ROOT']);
		//$doc_root = preg_replace("%/$%",'',$doc_root);
		$pattern = "%^.*".$doc_root."%";		
		//var_dump($myPath);
		$path = 'http://'.$_SERVER['HTTP_HOST']."/".preg_replace($pattern,'',$myPath)."/";	
		//var_dump($path);
		//die();
		$this->config=array(
			'dirsep' => $dirsep,
			'cache_clear_interval'=>0,
			'charset'=>'windows-1251',
			'b2bcontext_path'  => $myPath, 
			'path'             => $path,
			'script_name'      => $_SERVER['SCRIPT_NAME'],
			'cache_path'       => '',
			'use_cache'        => 1,
			'update_time'      => '05:15',
			'template_var'     => 'b2bcontext-style1',      
			'b2bcontext_server' => 'http://b2bcontext.ru',
			'prefix'=>'',
			'host_name'=>'',
			'request_max_duratuion'=>20,
			'request_method'=>''
		);		
		
		include($config_path);
		
		foreach($this->config as $key=>$val)
		{
			if(isset($$key))$this->config[$key]=$$key;
		}
		
		foreach($add_params as $key=>$val)
		{
			if(isset($$key))$this->config[$key]=$$key;
			else $this->config[$key]=$val;
		}
		
		if(empty($this->config['cache_path']))
			$this->config['cache_path']=$this->config['b2bcontext_path'].$this->config['dirsep'].'cache'.$this->config['dirsep'];
		
		$this->config['template_path']= implode($this->config['dirsep'],array(
			$this->config['b2bcontext_path'],
			"templates",
			$this->config['template_var'],
			''
			)
		);
	}
	
	function get($name, $def_value = '')
	{
		return isset($this->config[$name])?$this->config[$name]:$def_value;
	}
	
	function getInput($name, $def_value='')
	{		
		$prefix=$this->config['prefix'];		//$this->config->fromClientEncoding(
		return (isset($_REQUEST[$prefix.$name]))?$this->fromClientEncoding($_REQUEST[$prefix.$name]):$def_value;
	}
	
		function fromClientEncoding($text)
		{
			if(is_array($text))
			{
				$ret=array();
				foreach($text as $item)
				{
					$ret[]=iconv($this->config['charset'],'windows-1251//TRANSLIT',$item);
				}
				return $ret;
			}
			return iconv($this->config['charset'],'windows-1251//TRANSLIT',$text);
		}
		
		function toClientEncoding($text)
		{			
			if(is_array($text))
			{
				$ret=array();
				foreach($text as $item)
				{
					$ret[]=iconv('windows-1251', $this->config['charset']."//TRANSLIT",$item);
				}
				return $ret;
			}
			
			return iconv('windows-1251', $this->config['charset']."//TRANSLIT",$text);
		}
	
}
}

if(!class_exists('B2BContext_Request'))
{
class B2BContext_Request{
	var $config;
	var $struct;
	
	function B2BContext_Request(&$config){
		$this->config=&$config;	
	}
	
	function get($url,$params=''){
		if(!is_array($params))$params=array();	
		$params['h']=$this->config->getInput('h',$this->config->get('host_name'));		
		if(!$this->config->get('use_cache'))return $this->getNoCache($url,$params);
		return $this->getCache($url, $params);
		
	}
	
	function getCache($url, $params)
	{		
		$cacheName=$this->buildCacheName($url,$params);	
		if (file_exists($cacheName)) {
			// Если такой файл уже есть
            // Текущее время
            $curr_t = time();
            // Время обновления файла на сервере b2bcontext: пример 01:15
            // Функция split не работает после версии PHP 5.3.0.
            // list($hour, $min) = split(':', $this->config->get('update_time','00:00'));
            list($hour, $min) = explode(':', $this->config->get('update_time','00:00'));
            $update_t = mktime($hour, $min, 0, date("m"), date("d"), date("Y"));
            // Время создания файла
            $stat = stat($cacheName);
            $create_t = $stat["mtime"];
            if ($create_t < $update_t && $curr_t>$update_t)
			{
                // Нужно обновить файл
                unlink($cacheName);
                $content = $this->getNoCache($url, $params);
                file_put_contents($cacheName, $content);
			}
			else
			{
				$content=file_get_contents($cacheName);
			}
        }else{
			$content = $this->getNoCache($url,$params);			
			file_put_contents($cacheName, $content);
		}
           
		return $content;
		
	}
	
	function getNoCache($url,$params){
		if(!is_array($params))$params=array();
		$params['h']=$this->config->getInput('h',$this->config->get('host_name'));		
		$params['encoding']='utf-8';
		return $this->getContent($url."?".http_build_query($params));
	}
	
	function getContent($url)
	{		
		$url=$this->config->get('b2bcontext_server').$url;
		$reqLen=$this->config->get('request_max_duratuion',20);
		$timeOut=' <div style="display: none;">Timeout!</div>';
		$method=$this->config->get('request_method');
		$url=preg_replace('/&amp;/','&',$url);

		switch($method){
		
		case 'get_contents':
		default:
			$ctx = stream_context_create
			(
				array
				(
					'http' => array('timeout' => $reqLen)
				)
			);            
      if (! (@ $page_content = file_get_contents ($url)))
				exit ($timeOut);
			return $page_content;		
		}
	}
	
	function buildCacheName($url, $params)
	{		
		$name=str_replace(array("/","&"),"_",$url);		
		if(strpos($name, '_partner_')===0)$name=substr($name,9);
		foreach($params as $key => $val){
			$name.="_".$key."_".$val;
		}
		return $this->config->get('cache_path').$name.".xml";
	}		
}	
}

if(!class_exists('B2BContext_Parser'))
{
class B2BContext_Parser{	
	var $struct;
	
	function B2BContext_Parser($page_content)
	{
		$this->struct=$this->parse($page_content);
	}
	
	function get($paths, $def_value='')
	{
		if(!is_array($paths))$paths=array($paths);
		$el=$this->struct;		
		foreach($paths as $path)
		{
			if(!isset($el[$path]))return $def_value;
			$el=$el[$path];
		}
		
		return $el;
	}
	
	function getValue($name, $def_value='')
	{
		return $this->get(array($name,0,'value'),$def_value);
	}
	
	function getAttribsArray($name,$sub_name)
	{
		$items=$this->get(array($name,0,$sub_name),array());
		/*foreach($items as &$item)
		{
			$item=$item['attributes'];
		}*/
		return $items;
	}	
		
	function parse($page_content)
	{
		$p = xml_parser_create("utf-8");
		xml_parser_set_option($p,XML_OPTION_CASE_FOLDING,0);
		xml_parser_set_option($p,XML_OPTION_SKIP_WHITE,1);
		xml_parse_into_struct($p,$page_content,$this->struct,$index);
		xml_parser_free($p);
		
		$ret=array();
		$this->parseXml($ret);			
		$ret=array_pop(array_pop($ret));

		return $ret;
	}

	function itemValue($item)
	{
		/*$ret=array(
			'value'=>isset($item['value'])? $this->toWin($item['value']):'', 
			'attributes'=>isset($item['attributes'])? $item['attributes']:array()
		);		
		foreach($ret['attributes'] as &$attr)
		{
			$attr=$this->toWin($attr);
		}*/
		$attrs=isset($item['attributes'])? $item['attributes']:array();
		$ret=array(
			'value'=>isset($item['value'])? $this->toWin($item['value']):'', 			
		);		
		foreach($attrs as $key=>$val)
		{
			$ret[$key]=$this->toWin($val);
		}
		
		return $ret;
	}
	
	function toWin($txt)
	{
		return iconv("utf-8","windows-1251//TRANSLIT",$txt);
	}
	
	function parseXml(&$ret, $ind=0)
	{
		$len=count($this->struct);
		for($i=$ind;$i<$len;$i++)
		{
			$item=$this->struct[$i];
			if($item['type']=='close')return $i;
			
			$tag=$item['tag'];			
			if(!isset($ret[$tag]))$ret[$tag]=array();
			$val=$this->itemValue($item);
			
			if($item['type']=='open')
			{
				$i=$this->parseXml($val, $i+1);
			}
			$ret[$tag][]=$val;
		}
		return $i;
	}
}	
}


if(!class_exists('B2BContext_Module'))
{
class B2BContext_Module
{
	var $config;
	
	var $parentModule;	
	var $params;	
		
	function B2BContext_Module(&$config, $parent='')
	{
		$this->parentModule=$parent;
		$this->config=$config;

		$this->params=array('title'=>'','description'=>'','body'=>'', 'keywords'=>'');
	}	
	
	function getCfg($name, $def_value='')
	{
		return $this->config->get($name, $def_value);
	}
	
	function template($url, $params, $fields='')
	{
		if(!is_array($fields))$fields=array_keys($params);			
		foreach($fields as $field)
			$$field=isset($params[$field])?$params[$field]:'';
		
		$script_name=$this->makeSelfLink();
		$path=$this->getCfg('path');
		$prefix=$this->getCfg('prefix');
		
		ob_start();
		include $this->getCfg('template_path').$url;
		return ob_get_clean();
	}
	
	function get($url, $params='', $noCache=false)
	{
		$request = new B2BContext_Request($this->config);		
		$xml=($noCache)?$request->getNoCache($url, $params):$request->get($url, $params);		
		//TODO process error
		/*if($page_content =~ /^Error:(.*)$/)
        {
            my $content = "Возникла ошибка: ".$1;
            Encode::from_to($content, 'cp1251', $Cfg->{charset} );
            print "Content-type: text/html; charset=windows-1251\n\n";
            print $content;
            exit;
        }
		*/	
		return new B2BContext_Parser($xml);
	}
	
	
	function getInput($name, $def_value='')
	{		
		return $this->config->getInput($name, $def_value);		
	}
	
	function getHost()
	{
		return $this->config->getInput('h',$this->getCfg('host_name'));
	}
	
	function extractParams(&$ret,$params, $prefix='')
	{		
		foreach($params as $key=>$val)
		{		
			if(is_int($key))
				$ret[$val]=$this->getInput($prefix.$val);
			else
				$ret[$key]=$this->getInput($prefix.$key,$val);
		}		
	}
	
	function clearArray($params, $keys, $prefix='')
	{
		$arr=array();		
		foreach($keys as $key=>$val)
		{
			if(is_int($key))$key=$val;
			if(isset($params[$key])){				
				$arr[$prefix.$key]=is_array($params[$key])?implode(',',$params[$key]):$params[$key];				
			}
		}
		return $arr;
	}
	
	function build_query($params, $numeric_prefix="arg", $arg_separator="&")
	{
		$ret=array();		
		foreach($params as $key=>$val)
		{
			if(is_int($key))$key=$numeric_prefix.$key;
			$ret[]=$key."=".$val;
		}
		return implode($arg_separator,$ret);
	}
	
	function makeLink($script,$params='')
	{			
		if(!is_array($params))return $script;		
		$query=$this->build_query($params);
		$pos=strpos($script,"?");
		if($pos===FALSE)
			return $script."?".$query;
		elseif($pos==strlen($script)-1)
			return $script.$query;
		
		return $script."&".$query;
	}
	
	function scriptName()
	{
		return $this->getCfg('script_name');
	}
	
	function makeSelfLink($params='')
	{
		$real_params=array();
		$prefix=$this->getCfg('prefix');
		if(!is_array($params))$params=array();	
		if(!empty($prefix))
		{
			foreach($_REQUEST as $key=>$val)
			{
				if(substr($key,0,strlen($prefix))!=$prefix)
				{
					$real_params[$key]=$val;
				}
			}
			foreach($params as $key=>$val)
			{
				$real_params[$prefix.$key]=$val;
			}
		}
		else
			$real_params=$params;
		
		return $this->makeLink($this->scriptName(),$real_params);
	}
	
	function parseUrlToParams($url)
	{		
		$params=parse_url($url);
		parse_str($params['query'],$ret);		
		return $ret;
	}
	
	function execute(){
		return '';
	}	
}
}

?>