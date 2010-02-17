<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Controller_Plugin_Routedb extends Zend_Controller_Plugin_Abstract {
	public function routeStartup(Zend_Controller_Request_Abstract $request) {
		$model = new Default_Model_Url();
		$result = $model->fetchAll(null, 'orderid');
		if ($result) {
			$front = Zend_Controller_Front::getInstance();
			$router = $front->getRouter();
			foreach ($result as $el) {
				$map = array();
				if ($el['map']) {
					$el['map'] = explode(',', $el['map']);
					foreach ($el['map'] as $n => $m) $map[$n + 1] = $m;
				}
				$reverse = preg_replace('/\((.+?)\)/i', '%s', $el['url']);
				$route = new Zend_Controller_Router_Route_Regex(
					$el['url'],
					array(
						'controller' => @$el['controller'] ? $el['controller'] : $front->getDefaultControllerName(),
						'action'     => @$el['action'] ? $el['action'] : $front->getDefaultAction()
					),
					$map,
					$reverse
				);
				$router->addRoute('dbroute'.$el['id'], $route);
			}
		}
    }
}

