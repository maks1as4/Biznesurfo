<?php
require_once ("Tender_Module.php");
class Tender_Module_Rubricator extends Tender_Module
{
	function execute(){
		$this->parentModule = 'rubricator';		
		if(isset($_REQUEST['clear_tender_cache']))
		{
			/*opendir(DR, $Cfg->{cache_path}) || die "can't opendir ".$Cfg->{cache_path}.": $!";
        while(my $file = readdir(DR))
        {
            next unless ($file =~ /xml$/i);
            unlink $Cfg->{cache_path}.$file;
        }
        closedir DR; */
		}
	
		$params=array(
			'is_state'=>$this->getCfg('is_state',NULL), 
			'rubricator_additional_tender_cnt'=>$this->getCfg('rubricator_additional_tender_cnt')
		);		
		if(is_null($params['is_state']) )unset($params['is_state']);
		$iArr=array('per_page'=>10,  'rubricator_columns'=>1, 'otr'=>$this->getCfg('rubricator_otr_str'));
		$this->extractParams($params, $iArr);
		if(empty($params['otr']))unset($params['otr']);				
		$xml=$this->get('/partner/rubricator', $params);

		$this->params['title']=$xml->getValue('title');
		$this->params['keywords']=$xml->getValue('keywords');
		$this->params['description']=$xml->getValue('description');

		$params['header']=$xml->getValue('header');
		$params['text']=$xml->getValue('text');
		
		$otrs=$xml->get('otr');			
		$params['otrs_left']=array();
		$params['otrs_right']=array();
		$params['show_subotrs']=$this->getCfg('rubricator_show_subotrs');
		
		foreach($otrs as $pos=>$otr)
		{
			$item=array('header'=>$otr['header'], 'subotrs'=>array(), 'tender_count'=>0);			
			foreach($otr['columns'][0]['column'] as $column)
			{				
				foreach($column['subotrs'][0]['subotr'] as $subotr)
				{

					//header href tender_count
					$item_subotrs=array(
						'header'=>$subotr['header'],
						'tender_count'=>$subotr['tender_count'],
						'href'=>$subotr['href'],
						'tenders'=>array()
					);
					$item['tender_count']+=$item_subotrs['tender_count'];
					
					$vars=$this->parseUrlToParams($item_subotrs['href']);					
					//var_dump($item_subotrs);
					//die();
					$vars['b2b_id']=$vars['id'];					
					$vars['parent']=$this->parentModule;
					$vars['child']='gettenderlist';
					unset($vars['id']);	unset($vars['h']);	unset($vars['per_page']);;					
					
					
					$item_subotrs['href']=$this->makeSelfLink($vars);		
					
					
					foreach($subotr['tenders'][0]['tender'] as $tender)
					{						
						$vars=$this->parseUrlToParams($tender['href']);
						$vars['parent']=$this->parentModule;
						$vars['child']='registerform';
						$item_subotrs['tenders'][]=array(
							'header'=>$tender['header'],
							'href'=>$this->makeSelfLink($vars)
						);
					}
					$item['subotrs'][]=$item_subotrs;					
				}
			}
			if($pos%2==0)
				$params['otrs_left'][]=$item;
			else
				$params['otrs_right'][]=$item;
		}
		
		$this->params['content']=$this->template('rubricator.html',$params);
		return $this->params;	
	}
}
?>