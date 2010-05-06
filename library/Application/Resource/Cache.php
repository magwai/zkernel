<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Resource_Cache extends Zend_Application_Resource_ResourceAbstract {
	const DEFAULT_REGISTRY_KEY = 'Zkernel_Cache';
	protected $_cache = null;

	public function init () {
		if (null === $this->_cache) {
			$options = $this->getOptions();
			if (!isset($options[0])) {
				if (!isset($options['frontend']['adapter'])) $options['frontend']['adapter'] = 'Core';
				if (!isset($options['backend']['adapter'])) $options['backend']['adapter'] = 'Memcached';
				if (!isset($options['frontend']['params'])) $options['frontend']['params'] = array();
				if (!isset($options['backend']['params'])) $options['backend']['params'] = array();
				$this->_cache = Zend_Cache::factory(
					$options['frontend']['adapter'],
					$options['backend']['adapter'],
					$options['frontend']['params'],
					$options['backend']['params']
				);

				if (isset($options['metadata']) && true === (bool) $options['metadata']) {
					Zend_Db_Table_Abstract::setDefaultMetadataCache($this->_cache);
				}

				if (isset($options['translate']) && true === (bool) $options['translate']) {
					Zend_Translate::setCache($this->_cache);
				}

				if (isset($options['locale']) && true === (bool) $options['locale']) {
					Zend_Locale::setCache($this->_cache);
				}
			}

			$key = (isset($options['registry']) && !is_numeric($options['registry'])) ? $options['registry'] : self::DEFAULT_REGISTRY_KEY;
			Zend_Registry::set($key, $this->_cache);
		}
		return $this->_cache;
	}
}