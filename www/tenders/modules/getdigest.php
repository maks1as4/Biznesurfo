<?php

require_once ("Tender_Module.php");
class Tender_Module_GetDigest extends Tender_Module
{
	function Tender_Module_GetTenderList($parent='')
	{
		$this->parentModule='rubricator';		
	}	
	
	function execute(){
		$params=array();
		$id=$this->getInput('b2b9_id');
		$xml=$this->get('/partner/digestcontent', array('h'=>$this->getHost(), 'id'=>$id));		

		$this->params['keywords']=$xml->getValue('keywords');
		$this->params['description']=$xml->getValue('description');
		$this->params['title']=$xml->get('title');
		$new=$xml->get(array('new',0));		
		$params['new']=array('header'=>$new['header'][0]['value'], 'indexed'=>$new['indexed'][0]['value'], 'text'=>$new['text'][0]['value'], 'registered'=>$new['registered'][0]['value']);
		$repl='рџтър <a href="'.$this->makeSelfLink(array('parent'=>$this->parentModule, 'child'=>'registerform')).'&id=$1">$1</a>';
		$params['new']['text']=preg_replace("{рџтър\s?Й\s?(\d+)}",$repl,$params['new']['text']);	
		$this->params['content']=$this->template('getdigest.html',$params);
		//var_dump($params['new']['header']);
		//die();
		return $this->params;		
	}
}

?>