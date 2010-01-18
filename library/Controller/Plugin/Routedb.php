<?php

class Zkernel_Controller_Plugin_Routedb extends Zend_Controller_Plugin_Abstract {
	public function routeStartup(Zend_Controller_Request_Abstract $request) {
		$model = new Default_Model_Url();
		$result = $model->fetchAll(null, 'orderid');
		if ($result) {
			$router = Zend_Controller_Front::getInstance()->getRouter();
			foreach ($result as $el) {
				$map = array();
				if ($el['map']) {
					$el['map'] = explode(',', $el['map']);
					foreach ($el['map'] as $n => $m) $map[$n + 1] = $m;
				}
				$route = new Zend_Controller_Router_Route_Regex(
					$el['url'],
					array(
						'controller' => $el['controller'],
						'action'     => $el['action']
					),
					$map
				);
				$router->addRoute('dbroute'.$el['orderid'], $route);
			}
		}
    }
}

