<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Twitter extends Zend_Application_Resource_ResourceAbstract {
	const DEFAULT_REGISTRY_KEY = 'Zkernel_Twitter';
	protected $_twitter = null;

	public function init () {
		$options = $this->getOptions();

		$this->_twitter = array(
			'token' => @$options['token'],
			'token_secret' => @$options['token_secret'],
			'consumer_key' => @$options['consumer_key'],
			'consumer_secret' => @$options['consumer_secret'],
			'callback_url' => @$options['callback_url']
		);

		$key = (isset($options['registry']) && !is_numeric($options['registry'])) ? $options['registry'] : self::DEFAULT_REGISTRY_KEY;
		Zend_Registry::set($key, $this->_twitter);

		return $this->_twitter;
	}
}