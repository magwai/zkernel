<?php

class Zkernel_View_Helper_ZappOverride extends Zkernel_View_Helper_Override  {
	
	function overrideZappnews(&$r, $options = array()) {
		if(!is_array($options)) $options = (array) $options;
		$default = array(
			'length' => 200,
			'cut' => true,
			'date' => 'word',
			'route' => '',
		);

		$options = array_merge($default,$options);
		
		$r->date_valid = Zkernel_Common::getDate($r->date, $options['date']);
		$r->date_day_valid = Zkernel_Common::getDate($r->date, 'd');
		$r->date_month_valid = Zkernel_Common::getDate($r->date, 'm');
					 

		if($options['route'])
		$r->url_valid = $this->view->url(array(
											'year' => Zkernel_Common::getDate($r->date, 'Y'),
											'month' => Zkernel_Common::getDate($r->date, 'm'),
											'stitle' => $r->stitle  
										),$options['route']);

		$this->overridePage($r);

		$r->cut_message = $this->view->textHelper()->truncate($r->message,$options['length'],$options['cut']);
	}
	
	function overrideZappsimpleband(&$r, $options = array()) {
		
		if(!is_array($options)) $options = (array) $options;
		$default = array(
			'length' => 0,
			'cut' => true,
			'date' => 'word',
			'route' => '',
		);

		$options = array_merge($default,$options);
		
		$r->date_valid = Zkernel_Common::getDate($r->date, $options['date']);			 

		if($options['route'])
		$r->url_valid = $this->view->url(array('stitle' => $r->stitle),$options['route']);
		$this->overridePage($r);
		if($options['length']) $r->cut_message = $this->view->textHelper()->truncate($r->message,$options['length'],$options['cut']);
	}

	function overrideZappcatalog(&$r, $options = array()) {
		if(!is_array($options)) $options = (array) $options;
		$default = array(
			'length' => 0,
			'cut' => true,
			'date' => 'word',
			'route' => '',
		);
		
		$options = array_merge($default,$options);

		if($options['route']) $r->url_valid = $this->view->url(array('stitle' => $r->stitle),$options['route']);		
	}
	
	function overrideZappcatalogitem(&$r, $options = array()) {
		
		if(!is_array($options)) $options = (array) $options;
		$default = array(
			'length' => 0,
			'cut' => true,
			'date' => 'word',
			'route' => '',
		);
		
		$options = array_merge($default,$options);
		if(isset($r->message)) $this->overridePage($r);
		if($options['route']) $r->url_valid = $this->view->url(array('stitle' => $r->stitle),$options['route']);	
	}		
}