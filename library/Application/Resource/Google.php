<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Google extends Zend_Application_Resource_ResourceAbstract {
	const DEFAULT_REGISTRY_KEY = 'Zkernel_Google';
	protected $_google = null;

	public function init () {
		$options = $this->getOptions();

		$this->_google = array(
			'login' => $options['login'],
			'password' => $options['password']
		);

		$key = (isset($options['registry']) && !is_numeric($options['registry'])) ? $options['registry'] : self::DEFAULT_REGISTRY_KEY;
		Zend_Registry::set($key, $this->_google);

		return $this->_google;
	}
}