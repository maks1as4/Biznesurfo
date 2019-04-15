<?php
require_once ("Tender_Module.php");
class Tender_Module_AddTender extends Tender_Module
{	
	function execute(){
		$params=array(
			'query'=>http_build_query(array(
				'link_tenderform'=>$this->makeSelfLink(array('parent'=>'rubricator', 'child'=>'addtender')),
				'h'=>$this->getHost(),
				'script_name' => $this->scriptName(),
				'path' => $this->getCfg('path'),				
				'template_var' => $this->getCfg('template_var'),
			)),
			'b2bcontext_server' => $this->getCfg('b2bcontext_server'),
		);
		$this->params['content']=$this->template('addtender.html',$params);
		return $this->params;
	}
}
?>