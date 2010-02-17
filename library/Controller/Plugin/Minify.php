<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Controller_Plugin_Minify extends Zend_Controller_Plugin_Abstract {
	public function routeStartup(Zend_Controller_Request_Abstract $request) {
       	$router = Zend_Controller_Front::getInstance()->getRouter();
		$route = new Zend_Controller_Router_Route_Regex(
			'(.*)\.(js|css)$',
			array(
				'controller' => 'minify',
				'action'     => 'index'
			),
			array('', 'path', 'ext')
		);
		$router->addRoute('minify', $route);
    }
}

