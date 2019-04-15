<?php

require_once ("Tender_Module.php");
class Tender_Module_RegisterForm extends Tender_Module
{
	function execute(){
		$params=array();
		$this->extractParams($params,array('act', 'id', 'firm', 'country', 'region', 'city', 'phone',
			'address', 'web', 'email', 'name_f', 'name_i', 'name_o', 'other', 'icode', 'ecode'));
		
		if($params['act']=='register')
		{			
			$xml=$this->get('/partner/registerform',$params);	
		}
		else
		{
			$xml=$this->get('/partner/registerform',array('id'=>$params['id']));						
		}
		
		$this->params['title']=$xml->getValue('page_title');
		$this->params['description']=$xml->getValue('description');
		$params['host']=$this->getHost();
		$params['message']=$xml->getValue('message');
		$params['success_text']=$xml->getValue('success_text');
		
		$tender=$xml->get(array('tender',0));
		if(is_array($tender))
		{
			foreach(
				array(
					'anons', 'header', 'is_state', 'keywords', 'startdate',
					'stopdate', 'cena_contrakta'
				)
				as $key
			){
				if(!isset($tender[$key]))
					$params[$key]='';
				else
					$params[$key]=$tender[$key][0]['value'];
			}
			
			$params['tender_otr']=$tender['otr'][0]['id'];
			$params['tender_otr_header']=$tender['otr'][0]['header'];
			$params['tender_subotr']=$tender['subotr'][0]['id'];
			$params['tender_subotr_header']=$tender['subotr'][0]['header'];
		}
		
		$vars=$xml->get(array('params',0));
		if(is_array($vars))
		{
			foreach(
				array(
					'address', 'city', 'country', 'email', 'firm',
					'name_f', 'name_i', 'name_o', 'phone','web'
				)
				as $key
			){
				if(!isset($vars[$key]))
					$params[$key]='';
				else
					$params[$key]=$vars[$key][0]['value'];
			}			
		}
		
		$cnt=$this->get('/partner/otrgeography', array(
			'act'=>'get_countries_districts_regions'
		));
		$params['countries']=$cnt->get('country');		
		for($i=0;$i<count($params['countries']);$i++) {
			$cnt_ref=&$params['countries'][$i];
			if(!isset($cnt_ref['district'])) continue;
			$cnt_ref['region']=array();
			foreach($cnt_ref['district'] as $d) {
				$cnt_ref['region']=array_merge($cnt_ref['region'],$d['region']);
			}

		}

		
		$params['region']=$this->getInput('region');
		$url_p=array('b2b_id'=>$params['tender_subotr'],'parent'=>'rubricator', 'child'=>'gettenderlist');
		if(isset($params['is_state']))$url_p['is_state']=$params['is_state'];
		$params['subotr_page_href']=$this->makeSelfLink($url_p);
		$params['subotr_page_header']=$params['tender_otr_header']." :: ".$params['tender_subotr_header'];
		$params['parent']=empty($this->parentModule)?'registerform':$this->parentModule;
		
		$this->params['content']=$this->template('registerform.html',$params);
		return $this->params;
	}
}   
?>
