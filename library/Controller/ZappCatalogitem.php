<?php

/**
 * @zk_title Карточка элемента каталога
 * @zk_routable 0
 * @zk_routes catalog/(.*)|stitle
 */
class Zkernel_Controller_ZappCatalogitem extends Zkernel_Controller_Action
{
	protected $_catalog_model_name = 'ZappCatalog';	
	protected $_catalogitem_model_name = 'ZappCatalogitem';
	protected $_catalog_override = 'zappcatalog';
	protected $_catalog_override_options = array();	
	protected $_catalogitem_override = 'zappcatalogitem';
	protected $_catalogitem_override_options = array();
	
	protected $_catalogmedia_model_name = '';
	protected $_catalogmedia_override = 'catalogmedia';
	protected $_catalogmedia_preview_items = 0;
	
	protected $_prefix = 'Zkernel_Db_Model_';
	 
	
	function indexAction(){
		$stitle = $this->getRequest()->getParam('stitle','');

		$class_name = $this->_prefix.$this->_catalogitem_model_name;
		$catalogitem_model =  new $class_name();
		
		$card = $catalogitem_model->fetchCard($stitle);

		$card_valid = ($card)? $this->view->override()->overrideSingle($card,$this->_catalogitem_override,$this->_catalogitem_override_options):null;
		if (!$card_valid) throw new Zend_Controller_Action_Exception('Not Found', 404);
		
		$class_name = $this->_prefix.$this->_catalog_model_name;
		$catalog_model =  new $class_name();
		$category = $catalog_model->fetchRow(array('`id` =?' => $card_valid->parentid));
		$category_valid = ($category)?$this->view->override()->overrideSingle($category, $this->_catalog_override, $this->_catalog_override_options):null;
		
		
		if($this->_catalogmedia_model_name){
			$class_name = $this->_prefix.$this->_catalogmedia_model_name;
			$catalogmedia_model =  new $class_name();
			
			if($this->_catalogmedia_preview_items) {
				$media_data = $catalogmedia_model->fetchList($card_valid->id,$this->_catalogmedia_preview_items);
				$premedia_data_valid = ($media_data)? $this->view->override($media_data,$this->_catalogmedia_override):null;
				$this->view->premedia_data_valid = $premedia_data_valid;
			}
			
			$media_data = $catalogmedia_model->fetchList($card_valid->id);
			$allmedia_data_valid = ($media_data)? $this->view->override($media_data,$this->_catalogmedia_override):null;
			$this->view->allmedia_data_valid = $allmedia_data_valid;
		}
		
		$this->view->card_valid = $card_valid;
		$this->view->category_valid = $category_valid;

		$categories_data = $catalog_model->fetchAllActual($this->_prefix.$this->_catalogitem_model_name, 0);
		$this->view->categories_data_valid = ($categories_data)?$this->view->override($categories_data, $this->_catalog_override, $this->_catalog_override_options):null;
	}
}
