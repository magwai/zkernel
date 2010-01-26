<?php

class Zkernel_Controller_Plugin_Menu extends Zend_Controller_Plugin_Abstract {
	public $model;

	public function routeStartup(Zend_Controller_Request_Abstract $request) {
		$this->model = new Default_Model_Menu();
		$menu = $this->getDeeper();
		$n = new Zend_Navigation($menu);
		Zend_Registry::set('Zend_Navigation', $n);
    }

    function getDeeper($id = 0) {
    	$m = $this->model->fetchAll(array('`parentid` = ?' => $id), 'orderid');
		$menu = array();
		$router = Zend_Controller_Front::getInstance()->getRouter();

		if ($m) foreach ($m as $el) {
			$p = array();
			if ($el->route && $el->param) {
				$pp = explode(',', $el->param);
				$mp = $router->getRoute($el->route)->getVariables();
				if ($mp) foreach ($mp as $k_1 => $el_1) $p[$el_1] = @$pp[$k_1];
			}
			$menu[] = array(
				'label' => $el->title,
				'controller' => $el->controller,
				'action' => $el->action,
				'params' => $p,
				'route' => $el->route ? $el->route : 'default',
				'uri' => $el->url,
				'pages' => $this->getDeeper($el->id)
			);
		}
		return $menu;
    }
}

