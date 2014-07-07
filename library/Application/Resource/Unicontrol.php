<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Unicontrol extends Zend_Application_Resource_ResourceAbstract {
	private $_inited = null;

	public function init () {
		if (null === $this->_inited) {
			Zend_Controller_Front::getInstance()->getRouter()->addRoute('catalog', new Zend_Controller_Router_Route_Regex(
				'control(?:/(.*?))?',
				array('controller' => 'control', 'action'=> 'unicontrol')
			));
			$this->_inited = true;
		}
		return $this;
	}

}