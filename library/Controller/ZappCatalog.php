<?php
/**
 * @zk_title Категория каталога и позиции
 * @zk_routable		0
 * @zk_routes catalog/page/(.*)|page catalog/category/(.*)|stitle catalog/category/(.*)/(.*)|stitle,page
 */
class Zkernel_Controller_ZappCatalog extends Zkernel_Controller_Action {
		
		protected $_catalog_model_name = 'ZappCatalog';	
		protected $_catalogitem_model_name = 'ZappCatalogitem';
		protected $_catalog_override = 'zappcatalog';
		protected $_catalog_override_options = array();	
		protected $_catalogitem_override = 'zappcatalogitem';
		protected $_catalogitem_override_options = array();		
		protected $_catalogitem_per_page = 10;
		
		protected $_catalog_tree_mode = false;
		
		protected $_prefix = 'Zkernel_Db_Model_';		
		
		function indexAction() {
			$stitle = $this->getRequest()->getParam('stitle','');
			$page = $this->getRequest()->getParam('page',1);
			
			$class_name = $this->_prefix.$this->_catalog_model_name;
			$catalog_model =  new $class_name();
			$class_name = $this->_prefix.$this->_catalogitem_model_name;
			$catalogitem_model =  new $class_name();
			
			$category_card = $catalog_model->fetchCard($stitle);
			if(!empty($stitle) && !$category_card) throw new Zend_Controller_Action_Exception('Not Found', 404);
			$category_card_valid = ($category_card)?$this->view->override()->overrideSingle($category_card, $this->_catalog_override, $this->_catalog_override_options):null;
			$this->view->category_valid = $category_card_valid;
			$current_category_id = ($category_card_valid)?$category_card_valid->id:0;
			
			if(!($this->_catalog_tree_mode&&!$current_category_id)){
				$catalogitem_model->setPaginator($page,$this->_catalogitem_per_page);
				$data = $catalogitem_model->fetchList($current_category_id,'orderid');			
				//print_r($data);exit;
				$data_valid = ($data)?$this->view->override($data, $this->_catalogitem_override,$this->_catalogitem_override_options):null;
				//print_r($data_valid);exit;
				$this->view->paginator = $data;
				$this->view->data_valid = $data_valid; 
			}
			if($this->_catalog_tree_mode){
				$categories_data = $catalog_model->fetchAllActual($this->_prefix.$this->_catalogitem_model_name, $current_category_id);				
			}else{
				$categories_data = $catalog_model->fetchAllActual($this->_prefix.$this->_catalogitem_model_name, 0);
			}
			
			$this->view->categories_data_valid = ($categories_data)?$this->view->override($categories_data, $this->_catalog_override, $this->_catalog_override_options):null;
		}
}
