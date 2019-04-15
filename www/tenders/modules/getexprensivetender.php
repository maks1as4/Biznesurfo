<?php

require_once ("Tender_Module.php");
class Tender_Module_GetExprensiveTender extends Tender_Module
{
	function execute(){		
		$xml=$this->get('/partner/getexpensivetender', array(		
			'hot_tender_number'=>$this->getCfg('hot_tender_number',10),
			'hot_tender_keywords'=>$this->getCfg('hot_tender_keywords'),
			'hot_tender_otr'=>$this->getCfg('hot_tender_otr'),
		));

		$this->params['keywords']=$xml->getValue('keywords');
		$this->params['description']=$xml->getValue('description');
		$this->params['title']=$xml->get('title');
		
		$params=array();
		$params['header']=$xml->getValue('header');
		$tenders=$xml->getAttribsArray('tenders','tender');
		$params['tenders']=array();
		foreach($tenders as $tender)
		{
			$item=array();
			foreach(array('anons', 'header', 'tender_id','href')as $key)
			{
				$item[$key]=$tender[$key][0]['value'];
			}			
			$item['href']=$this->makeSelfLink(
				array('parent'=>'rubricator', 'child'=>'registerform', 'id'=>$item['tender_id'])
			);
			$vars=$this->parseUrlToParams($item['href']);			
			$vars['parent']='rubricator';
			$vars['child']='registerform';			
			$item['href']=$this->makeSelfLink($vars);			
			$params['tenders'][]=$item;
		}

		$this->params['content']=$this->template('getexprensivetender.html',$params);
		return $this->params;		
	}
}

?>