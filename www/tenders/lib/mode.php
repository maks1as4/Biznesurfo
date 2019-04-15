<?php
/*
 * Каждый модуль формирует переенные для title, keywords, description $rubricator_title,$rubricator_keywords,$rubricator_description
 * 
 */
require_once('utils.php');
  
class B2BContext_Tender
{
	var $content;
	var $head;
	var $title;
	var $keywords;
	var $description;
	
	var $config;
	var $modules;
	

	function clearCache($cache_clear_interval=0)
  {
		$cache_path=$this->config->get('cache_path');
		if(!$cache_clear_interval)
			$cache_clear_interval=(int)$this->config->get('cache_clear_interval',0);;
		if($cache_clear_interval>0 && file_exists($cache_path))
		{
			$tm=time()-$cache_clear_interval*3600;
			if(($dir=opendir($cache_path))!==false)
      {
				while ( ($f = readdir( $dir ))!==false )
				{
					$f=$cache_path.$f;
					if(is_file($f) && filectime($f)<=$tm)
						@unlink($f);
				}
				closedir($dir);
			}
		}
	}
	
	function initHead()
	{
		$charset = $this->config->get('charset');
		$path = $this->config->get('path');
		$this->head = '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'">
			<link rel="stylesheet" type="text/css" href="'.$path.'css/style.css">
      <link rel="stylesheet" type="text/css" href="'.$path.'css/calendar.css">
			<script language="JavaScript" src="'.$path.'js/otr_country.js"></script>
			<script language="JavaScript" src="'.$path.'js/Calendar.js"></script>
			<script language="JavaScript" src="'.$path.'js/AjaxStreaming.js"></script>
			<!--// Незабываем прописывать и здесь путь к файлам и в файле ie.css  //-->
			<!--[if lte IE 8]>
				<link href="'.$path.'css/ie.css" rel="stylesheet" type="text/css" media="screen, projection" />
				<script type="text/jscript" src="'.$path.'js/ie.pack.js"></script>
			<![endif]-->';
	}
        
	function getActiveModules($work_special, $parent, $child)
	{
		$list_modules=array();
		switch($work_special)
		{
			case 2:
				$list_modules=array('getdigestlist'=>1);
			break;
			case 3:
				$list_modules=array('getexprensivetender'=>1);
			break;
			case 1:
			default:
				$work_special=1;
				$list_modules=array('findtender'=>1, 'getexprensivetender'=>1, 'rubricator'=>1, 'getdigestlist'=>1);
				#-- горячие тендеры оставить только в рубрикаторе. 
				if(in_array($child, array('registerform', 'gettenderlist', 'addtender')))
				{
					unset($list_modules['getexprensivetender']);
				}
				#-- рубрикатора не д.б. при выводе результатов поиска
				if($this->config->getInput('b2b5_act')=='find' || $child=='getdigest')
				{
					unset($list_modules['rubricator']);
					unset($list_modules['getexprensivetender']);;
				}
				if($child=='registerform')
					$parent='rubricator';
			break;
		}	
    
    //Используется для формирование индивидуальной ссылки, а также для возможных распорок
		if(!($child=='addtender' || $child=='getdigest' || $work_special>1))
			$list_modules['costyl']=1;
			
		if(!empty($child) )
		{
			if(isset($list_modules[$parent]))
				$list_modules[$parent]=$child;
			else
				$list_modules[$child]=1;			
		}

		return $list_modules;
	}
	
	function executeModules($list_modules, $parent)
	{
		$dirsep=$this->config->get('dirsep');
		$b2bpath=$this->config->get('b2bcontext_path');
		$all_modules=array(
			'addtender'=>'AddTender',
			'costyl'=>'Costyl',
			'findtender'=>'FindTender',
			'getdigest'=>'GetDigest',
			'getdigestlist'=>'GetDigestList',
			'gettenderlist'=>'GetTenderList',			
			'getexprensivetender'=>'GetExprensiveTender',
			'registerform'=>'RegisterForm',
			'rubricator'=>'Rubricator',
		);		
		$this->modules=array();		

		foreach($list_modules as $key=>$val)
		{
			$module=($val==1)?$key:$val;
			if(!isset($all_modules[$module]))continue;
			$module_name='Tender_Module_'.$all_modules[$module];
			if(!class_exists($module_name))
				require($b2bpath.$dirsep.'modules'.$dirsep.$module.".php");			
			$item=new $module_name($this->config, $parent);
			$this->modules[$module]=$item->execute();			
		}
		return $this->modules;
	}
	
	function B2BContext_Tender($config_path, $work_special=1)
	{
		$this->config=new B2BContext_Config($config_path,array(
			//Значения по умолчанию
			#-- Настройки рубрикатора
			'rubricator_additional_tender_cnt' => 3, 
			'rubricator_otr_str' => '',
			'rubricator_show_subotrs' => 0,
    
			#-- Настройки горячих тендеров
			'hot_tender_number' => 3,
			'hot_tender_keywords' => '',
			'hot_tender_otr' => '',   
    
			#-- Настройки блока "новости и аналитика" 
			'get_digest_cnt' => 3,
    
			#-- Настройки gettenderlist
			'gettenderlist_per_page' => '20',			
			)	
		);
		
		$dirsep=$this->config->get('dirsep');
		$b2bpath=$this->config->get('b2bcontext_path');
		//require_once($b2bpath.$dirsep.'lib'.$dirsep.'utils.php');
		
		//различные периодические задачи
		if(isset($_REQUEST['clear_tender_cache']))
			$this->clearCache(1);
		else
			$this->clearCache();
		
		$child=$this->config->getInput('child');		
		$parent=$this->config->getInput('parent');
		if($child=='registerform')$parent='rubricator';
    
		$list_modules=$this->getActiveModules($work_special, $parent, $child);		
		$this->executeModules($list_modules, $parent);		

		if(!empty($parent) && isset($this->modules[$child]))
		{
			$this->modules[$parent]=$this->modules[$child];			
		}	
		
		//head
		$this->initHead();			
		
		#-- формирование title, keywords, description    
		if($work_special==1)
		{
			if(in_array($child, array('gettenderlist', 'registerform', 'getdigest')))
			{
				/*var_dump($list_modules);
				var_dump($this->modules);
				die();*/
				
				$this->title=$this->modules[$child]['title'];
				$this->keywords=$this->modules[$child]['keywords'];
				$this->description=$this->modules[$child]['description'];
			}
			else if($this->config->getInput('b2b5_act')=='find')
			{
				$this->title=$this->modules['findtender']['title'];
				$this->keywords=$this->modules['findtender']['keywords'];
				$this->description=$this->modules['findtender']['description'];
			}
			else
			{
				$this->title=$this->modules['rubricator']['title']." ".$this->modules['findtender']['title'];
				$this->keywords=$this->modules['rubricator']['keywords']." ".$this->modules['findtender']['keywords'];
				$this->description=$this->modules['rubricator']['description']." ".$this->modules['findtender']['description'];
			}
		}
		else
		{
			$this->title=$this->keywords=$this->description='';
			foreach($this->modules as $item)
			{
				$this->title.=$item['title'];
				$this->keywords.=$item['keywords'];
				$this->description.=$item['description'];
			}
		}
		
		//content
		$cont_modules=array('findtender', 'getexprensivetender', 'costyl', 'rubricator' );
		if($child!='addtender')$cont_modules[]='getdigestlist';
		$this->content='';    
		foreach($cont_modules as  $module)
		{
			if(!isset($this->modules[$module]))continue;
			$this->content.=$this->modules[$module]['content'];
		}		
		
		$this->content=$this->config->toClientEncoding($this->content);
		$this->title=$this->config->toClientEncoding($this->title);
		$this->keywords=$this->config->toClientEncoding($this->keywords);
		$this->description=$this->config->toClientEncoding($this->description);
  }
	
	function compatible()
	{
		
	}
}


if(!class_exists('b2bcontext'))
{
class b2bcontext{
	var $b2bcontext_content;
	var $b2bcontext_head;
	var $b2bcontext_title;
	var $b2bcontext_keywords;
	var $b2bcontext_description;
	function b2bcontext($config, $work_special=1)
	{
		$item=new B2BContext_Tender($config, $work_special);
		$this->b2bcontext_content=$item->content;
		$this->b2bcontext_head=$item->head;
		$this->b2bcontext_title=$item->title;
		$this->b2bcontext_keywords=$item->keywords;
		$this->b2bcontext_description=$item->description;
	}
}
}
?>
