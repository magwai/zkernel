<?php
class Zkernel_Db_Model_ZappCatalogMenu {
	protected $_options = array(
									'catalog_model' => 'Catalog',
									'catalog_override' => 'catalog',
									'catalogitem_model' => 'Catalogitem',	
									'controller' => 'catalog',
									'action' => 'index',
									'params' => '',
									'route' => null,
									'array' => true,
									'tree' => false
								);
	protected $_prefix = 'Default_Model_';
	protected $_catalog_menu = null;
	protected $view;
								
	function __construct($options = array()){
		$this->_options = array_merge($this->_options,$options);
		if(!count($options) && $this->_catalog_menu !== null) return $this->_catalog_menu;
		$this->view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		
		$this->_catalog_menu = $this->fetchMenu($this->_options['route'],0);
		if(!$this->_options['array']) $this->_catalog_menu = $this->_catalog_menu ? new Zend_Navigation($this->_catalog_menu) : null;
	}

	public function getCatalogMenu(){
		return $this->_catalog_menu;	
	}
	
	private function fetchMenu($route = null, $id = 0) {

		$class_name = $this->_prefix.$this->_options['catalog_model'];
		//if(!class_exists($class_name, false)) return false;
		$catalog_model =  new $class_name();
		
		if(method_exists($catalog_model,'fetchAllActual')) $data = $catalog_model->fetchAllActual($this->_prefix.$this->_options['catalogitem_model'],$id);
		else $data = $catalog_model->fetchAll(null,'id');
    	$data_valid = $data;
		$data_valid = ($data)?$this->view->override($data,$this->_options['catalog_override']):null;

    	$menu = array();
    	
    	if ($data_valid) foreach ($data_valid as $el) {
			$inner = ($this->_options['tree'])?$this->fetchMenu($route, $el->id):array();
			$menu[] = array(
				'label' => $el->title,
				'controller' => $this->_options['controller'],
				'action' => $this->_options['action'],
				'params' => array('stitle' => $el->stitle),
				'route' => $route,
				'pages' => $inner,
				'id' => $el->id
			);
		}
		return $menu;
    }
}