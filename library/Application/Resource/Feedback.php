<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Feedback extends Zend_Application_Resource_ResourceAbstract {
	private $_inited = null;

	public function init () {
		if (null === $this->_inited) {
			$options = $this->getOptions();
			Zend_Controller_Front::getInstance()->registerPlugin(new Zkernel_Controller_Plugin_Feedback($options));
			$this->_inited = true;
		}
		return $this;
	}
}