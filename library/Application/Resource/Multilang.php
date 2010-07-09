<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Multilang extends Zend_Application_Resource_ResourceAbstract {
	private $_inited = null;

	public function init () {
		if (null === $this->_inited && strpos($_SERVER['REQUEST_URI'], '/fu') === false && strpos($_SERVER['REQUEST_URI'], '/z/') === false) {
			$options = $this->getOptions();
			Zend_Controller_Front::getInstance()->registerPlugin(new Zkernel_Controller_Plugin_Multilang($options));
			$this->_inited = true;
		}
		return $this;
	}

}