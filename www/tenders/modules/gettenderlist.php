<?php
require_once ("Tender_Module.php");
class Tender_Module_GetTenderList extends Tender_Module
{
	function execute(){		
		if(empty($this->parentModule))$this->parentModule='rubricator';
		
		$params=array();		
		$is_state=$this->getCfg('is_state',NULL);
		if(!is_null($is_state))$params['is_state']=$is_state;
		
		$params['id']=$this->getInput('b2b_id');	
		$this->extractParams($params, array(
			'per_page'=>$this->getCfg('gettenderlist_per_page',20),
			'p'=>'1',
			'sort_field'=>'startdate',
			'sort_pointer'=>'desc')
		);		
		//$cur_page -p		
		$xml=$this->get('/partner/gettenderlist', $params);

		$params['sort_pointer_symb']  = ($params['sort_pointer']=='desc') ? '&uarr;' : '&darr;';			
		$params['page_sort_href']=$this->makeSelfLink(
				array('parent'=>$this->parentModule, 'child'=>'gettenderlist','b2b_id'=>$params['id'])
		);	
		
		$this->params['title']=$xml->get('title');
		$this->params['keywords']=$xml->getValue('keywords');
		$this->params['description']=$xml->getValue('description');
		
		$params['header']=$xml->getValue('header');
		$params['full_count']=$xml->getValue('count');
		$params['research_otr']=$xml->getValue('research_otr');
		
		$pages=$xml->getAttribsArray('pages','page');
		$params['pages_present']=count($pages);
		$params['pages']=array();
		foreach($pages as $page)
		{
			$item=array(
				'page_title'=>$page['title'][0]['value'],
				'page_href'=>$page['href'][0]['value'],
				'cur_page'=>$page['cur_page'],			
			);
			$vars=$this->parseUrlToParams($item['page_href']);
			$vars['b2b_id']=$vars['id'];unset($vars['id']);
			$vars['parent']=$this->parentModule;
			$vars['child']='gettenderlist';
			unset($vars['h']);
			$item['page_href']=$this->makeSelfLink($vars);
			$params['pages'][]=$item;
		}
		
		$tenders=$xml->getAttribsArray('tenders','tender');
		$params['tenders']=array();
		foreach($tenders as $tender)
		{
			$item=array();
			foreach(
				array(
					'anons', 'cena_contrakta', 'header', 'region', 
					'startdate', 'stopdate', 'subotr', 'tender_id'
				)as $key)
			{
				$item[$key]=$tender[$key][0]['value'];
			}			
			$item['href']=$this->makeSelfLink(
				array('parent'=>'rubricator', 'child'=>'registerform', 'id'=>$item['tender_id'])
			);			
			$params['tenders'][]=$item;
		}
		
		$params['registerform_link']=$this->makeSelfLink(
				array('parent'=>'rubricator', 'child'=>'registerform')
		);			
		
		$this->config->config['get_demo_otr']=$params['research_otr'];
		$this->config->config['get_demo_subotr_id']=$params['id'];    
		
		$this->params['content']=$this->template('gettenderlist.html',$params);
		return $this->params;		
	}
}

?>