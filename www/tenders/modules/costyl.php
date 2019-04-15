<?php

require_once ("Tender_Module.php");
class Tender_Module_Costyl extends Tender_Module
{
	function execute(){
		$params=array(
			'link' => $this->makeSelfLink(array('parent'=>'rubricator','child'=>'addtender'))		
		);
		$this->params['content']=$this->template('costyl.html',$params);
		return $this->params;
	}
}

?>