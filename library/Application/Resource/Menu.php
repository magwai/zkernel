<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Menu extends Zend_Application_Resource_ResourceAbstract {
	private $_inited = null;

	public function init () {
		if (null === $this->_inited) {
			$options = $this->getOptions();
			$class = class_exists('Default_Plugin_Menu') ? 'Default_Plugin_Menu' : 'Zkernel_Controller_Plugin_Menu';
			Zend_Controller_Front::getInstance()->registerPlugin(new $class($options));
			$this->_inited = true;
		}
		return $this;
	}
}