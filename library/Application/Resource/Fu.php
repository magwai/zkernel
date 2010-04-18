<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Fu extends Zend_Application_Resource_ResourceAbstract {
	protected $_inited = null;

	public function init () {
		if (null === $this->_inited) {
			$router = Zend_Controller_Front::getInstance()->getRouter();
			$route = new Zend_Controller_Router_Route_Regex(
				'/fu',
				array(
					'controller' => 'z',
					'action'     => 'fu'
				)
			);
			$router->addRoute('fu', $route);
			$this->_inited = true;
		}
		return $this;
	}
}