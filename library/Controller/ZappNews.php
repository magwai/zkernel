<?php

/**
 * @zk_title   		Новости
 * @zk_routable		0
 * @zk_routes		news news/page/(\d)|page news/((19|20)\d\d)|year news/((19|20)\d\d)/page/(\d)|year,page news/((19|20)\d\d)/(0[1-9]|1[012])|year,month news/((19|20)\d\d)/(0[1-9]|1[012])/page/(\d)|year,month,page
 */

class Zkernel_Controller_ZappNews extends Zkernel_Controller_Action {
		
	protected $_model_name = 'ZappNews';	
	protected $_override = 'zappnews';
	protected $_per_page = 10;
	protected $_prefix = 'Zkernel_Db_Model_';
	protected $_override_options = array();	
	
	function indexAction() {
		$page = $this->getRequest()->getParam('page',1);
		$year = $this->getRequest()->getParam('year','');
		$month = $this->getRequest()->getParam('month','');
		
		$class_name = $this->_prefix.$this->_model_name;
		$_model =  new $class_name();
		
		$_model->setPaginator($page,$this->_per_page);
		$data = $_model->fetchList($year,$month);
		$data_valid = ($data)? $this->view->override($data,$this->_override,$this->_override_options):null;
		
		if (!$data_valid) throw new Zend_Controller_Action_Exception('Not Found', 404);
		$this->view->data_valid = $data_valid;
		$this->view->paginator = $data;
		$this->view->year = $year;
		$this->view->month = $month; 
	}

/**
 * @zk_title   		Карточка новости
 * @zk_routable		0
 * @zk_routes		news/((19|20)\d\d)/(0[1-9]|1[012])/(.*)|year,month,stitle
 */	
	function cardAction(){
		$stitle = $this->getRequest()->getParam('stitle','');
		
		$class_name = $this->_prefix.$this->_model_name;
		$_model =  new $class_name();
		
		$card = $_model->fetchCard($stitle);
		$card_valid = ($card)? $this->view->override()->overrideSingle($card,$this->_override,$this->_override_options):null;
		if (!$card_valid) throw new Zend_Controller_Action_Exception('Not Found', 404);
		
		$this->view->card_valid = $card_valid;
	}
}