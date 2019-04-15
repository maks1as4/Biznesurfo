<?php
require_once ("Tender_Module.php");
class Tender_Module_GetDigestList extends Tender_Module
{
	function execute(){		
		$this->parentModule='getdigestlist';
		$params=array();
		$xml=$this->get('/partner/getdigestlist', array('get_digest_cnt'=>$this->getCfg('get_digest_cnt'), 'new'=>1));
		
		$this->params['title']=$xml->get('title');
		$this->params['keywords']=$xml->getValue('keywords');
		$this->params['description']=$xml->getValue('description');
		
		$params['header']=$xml->getValue('header');		
		$params['news']=array();
		$news=$xml->getAttribsArray('news','new');		
		$change= ' <a href="'.$this->makeSelfLink(array('parent'=>'rubricator','child'=>'registerform','id'=>''));
		foreach($news as $new)
		{			
			$item=array();
			foreach(array('header','body','new_id','indexed')as $key)
			{
				$item[$key]=$new[$key][0]['value'];
			}
			$item['href']=$this->makeSelfLink(array('parent'=>$this->parentModule,'child'=>'getdigest','b2b9_id'=>$item['new_id']));
			$item['body']=preg_replace("{(аявка|звешение)\s?№\s?(\d+)}is","$1$change$2\">№$2</a>",$item['body']);
			$params['news'][]=$item;
		}
				
		$this->params['content']=$this->template('getdigestlist.html',$params);
		return $this->params;		
	}
}

?>