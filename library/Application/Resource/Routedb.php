<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Routedb extends Zend_Application_Resource_ResourceAbstract {
	protected $_inited = null;

	public function init () {
		if (null === $this->_inited) {
			if (stripos($_SERVER['REQUEST_URI'], '/ctl') === false) {
				$this->getBootstrap()->bootstrap('db');
				$options = $this->getOptions();

				if (!isset($options['model'])) $options['model'] = 'Default_Model_Url';
				if (!isset($options['name'])) $options['name'] = 'dbroute';


				$class = $options['model'];
				$model = new $class();
				$result = $model->fetchAll(null, 'orderid');
				$front = Zend_Controller_Front::getInstance();
				$router = $front->getRouter();
				if ($result) {
					foreach ($result as $el) {
						$map = array();
						if ($el['map']) {
							$el['map'] = explode(',', $el['map']);
							foreach ($el['map'] as $n => $m) $map[$n + 1] = $m;
						}
						if(isset($el['reverse']) && !empty($el['reverse'])) $reverse = $el['reverse'];
						else $reverse = preg_replace('/\((.+?)\)/i', '%s', $el['url']);
						$route = new Zend_Controller_Router_Route_Regex(
							$el['url'],
							array(
								'controller' => @$el['controller'] ? $el['controller'] : $front->getDefaultControllerName(),
								'action'     => @$el['action'] ? $el['action'] : $front->getDefaultAction()
							),
							$map,
							$reverse
						);

						$router->addRoute($options['name'].$el['id'], $route);
					}
				}
			}
			$this->_inited = true;
		}
		return $this;
	}
}