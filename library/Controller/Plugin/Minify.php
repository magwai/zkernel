<?php

class Zkernel_Controller_Plugin_Minify extends Zend_Controller_Plugin_Abstract {
	public function routeStartup(Zend_Controller_Request_Abstract $request) {
       	$um = new Default_Model_Url();
		$result = $um->fetchAll();
		$router = Zend_Controller_Front::getInstance()->getRouter();
		if ($result) {
			foreach ($result as $n => $el) {
				$map = array();
				if ($el->map) {
					$t = explode(',', $el->map);
					if ($t) foreach ($t as $n => $m) $map[$n + 1] = $m;
				}
				$route = new Zend_Controller_Router_Route_Regex(
					$el->url,
					array(
						'controller' => $el->controller,
						'action'     => $el->action ? $el->action : 'index'
					),
					$map
				);
				$router->addRoute($n, $route);
			}
		}
		$route = new Zend_Controller_Router_Route_Regex(
			'(.*)\.(js|css)$',
			array(
				'controller' => 'minify',
				'action'     => 'index'
			),
			array('', 'path', 'ext')
		);
		$router->addRoute($n + 1, $route);
    }
}

