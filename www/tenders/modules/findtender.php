<?php
require_once ("Tender_Module.php");
class Tender_Module_FindTender extends Tender_Module
{

	function performFind($params)
	{		
		$url_params=$this->clearArray($params, 
				array('act','price_from','price_to','or_and',	'number', 'is_state', 'subotr',	'region',	'sort_field',		'sort_pointer')
		);		
	
		$url_params['act']='find';		
		$url_params['text']=$params['search'];
		$url_params['page']=$params['p'];
		$url_params['rec_per_page']=$params['per_page'];
		if(!empty($params['take_date'])){
			$url['date_from']=$params['year_from']."-".$params['month_from']."-".$params['day_from'];
			$url['date_to']=$params['year_to']."-".$params['month_to']."-".$params['day_to'];
		}
			
		return $this->get('/partner/findtender', $url_params, true); //yt rtibhetv
	}
	
	function execute(){
		$params=array( 'take_date'=>$this->getInput('b2b5_take_date'));
		$iArr=array('act', 'price_from', 'price_to', 'search', 'or_and', 'number', 'is_state', 'per_page'=>10,  'subotr', 'region','extended_search'=>0, 
		'day_from', 'month_from', 'year_from', 'day_to', 'month_to', 'year_to');
			
		$this->extractParams($params, $iArr, "b2b5_");
		$params['page_sort_href']=$this->makeSelfLink(
			array('parent'=>'findtender', 'child'=>'findtender')
		)."&".$this->build_query($this->clearArray($params, $iArr, 'b2b5_'));		
			
		$nArr=array('p'=>'1','sort_field'=>'startdate','sort_pointer'=>'desc');
		$this->extractParams($params, $nArr);
		$params['sort_pointer_symb']=($params['sort_pointer']== 'desc')? '&uarr;' : '&darr;';
		$params['registerform_link']=$this->makeSelfLink(array(
				'parent'=>'rubricator','child'=>'registerform'
		));

		//'extended_search'=>0,	
		if($params['act']=='find')
		{			
			$xml=$this->performFind($params);
			
			$params['full_count']=$xml->getValue('count');
			$pages=$xml->getAttribsArray('pages','page');
			$params['pages']=array();			
			$p=$params['p'];
			$params['pages_present']=count($pages);
			foreach($pages as $page)
			{
				$item=array(
					'page_title'=>$page['title'][0]['value'],
					'number'=>$page['number'][0]['value'],
					'cur_page'=>$page['cur_page'],			
				);						
				$params['p']=$item['number'];
				$item['page_href'] = $this->makeSelfLink(
					array('parent'=>'findtender', 'child'=>'findtender')
				)."&".$this->build_query($this->clearArray($params, $iArr, 'b2b5_')).				
				"&".$this->build_query($this->clearArray($params, $nArr));		
				$params['pages'][]=$item;

			}
			$params['p']=$p;
			
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
					array('parent'=>'findtender', 'child'=>'registerform', 'id'=>$item['tender_id'])
				);			
				$params['tenders'][]=$item;
			}			
		}
		else		
		{
			$xml=$this->get('/partner/findtender');			
      list($y,$m,$d)=explode("-",date("Y-m-d"));
      $params['day_from']=$params['day_to']=$d;
			$params['month_from']=$params['month_to']=$m;
			$params['year_from']=$params['year_to']=$y;        
		}

		$this->params['title']=$xml->get('title');
		$this->params['keywords']=$xml->getValue('keywords');
		$this->params['description']=$xml->getValue('description');
	
		$params['header']=$xml->getValue('header');
		$params['example']=$xml->getValue('example');		
		$params['rec_per_pages']=$xml->getAttribsArray('rec_per_pages','recperpage');
		$params['years']=$xml->getAttribsArray('years','year');
		$params['is_states']=$xml->getAttribsArray('is_states','is_state');
		$params['monthes']=array(1=>'Января','Февраля','Марта','Апреля','Мая','Июня','Июля','Августа','Сентября','Октября','Ноября','Декабря');			
		
		$geo=$this->get('/partner/otrgeography', array(
			'act'=>'get_otrs_suborts',
			'partner_type'=>'by_tender',			
			'mode'=>'subotr'
		));		
		$params['otrs']=$geo->get('otr');		
		
		$cnt=$this->get('/partner/otrgeography', array(
			'act'=>'get_countries_districts_regions',
			'partner_type'=>'by_tender',
			'mode'=>'region'
		));
		$params['countries']=$cnt->get('country');

		if(!is_array($params['subotr']))$params['subotr']=explode(',',$params['subotr']);
		if(!is_array($params['region']))$params['region']=explode(',',$params['region']);
		if(!is_array($params['subotr']))$params['subotr']=array($params['subotr']);
		if(!is_array($params['region']))$params['region']=array($params['region']);
		$this->params['content']=$this->template('findtender.html',$params);
		
		return $this->params;
	}
}
?>