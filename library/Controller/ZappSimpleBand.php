<?php

/**
 * @zk_title   		Лента элементов
 * @zk_routable		0
 * @zk_routes		band band/page/(\d)|page
 */

class Zkernel_Controller_ZappSimpleBand extends Zkernel_Controller_Action {
		
	protected $_model_name = 'ZappSimpleBand';	
	protected $_override = 'zappsimpleband';
	protected $_override_options = array();
	protected $_per_page = 10;
	protected $_prefix = 'Zkernel_Db_Model_';


        function indexAction() {
		$page = $this->getRequest()->getParam('page',1);

		$class_name = $this->_prefix.$this->_model_name;
		$_model =  new $class_name();
		
		$_model->setPaginator($page,$this->_per_page);
		$data = $_model->fetchList();
		$data_valid = ($data)? $this->view->override($data,$this->_override,$this->_override_options):null;
		if (!$data_valid) throw new Zend_Controller_Action_Exception('Not Found', 404);
		$this->view->data_valid = $data_valid;
		$this->view->paginator = $data;
	}

/**
 * @zk_title   		Карточка элемента ленты
 * @zk_routable		0
 * @zk_routes		band/(.*)|stitle
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