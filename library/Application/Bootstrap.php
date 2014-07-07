<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Application_Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
    /**
     * Инициализация приложения
     */

	protected function _initApp() {
		Zend_Controller_Action_HelperBroker::addPrefix('Zkernel_Controller_Action_Helper');
	}

    /**
     * Инициализация автозагрузки
     */
	protected function _initAutoload() {
        $autoloader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Default_',
            'basePath'  => APPLICATION_PATH,
        ));
        return $autoloader;
    }

    /**
     * Инициализация конфигурации
     */
	protected function _initConfig() {
		$path = APPLICATION_PATH.'/../library/Zkernel/Application/configs';
		$config = new Zend_Config(array(), true);
		$it = new DirectoryIterator($path);
		foreach ($it as $file) {
			if ($file->isFile()) {
				$fullpath = $path.'/'.$file;
				switch(substr(trim(strtolower($fullpath)), -3)) {
	                case 'ini':
	                    $cfg = new Zend_Config_Ini($fullpath, $this->getEnvironment());
	                    break;
	                case 'xml':
	                    $cfg = new Zend_Config_Xml($fullpath, $this->getEnvironment());
	                    break;
	                default:
	                    throw new Zend_Config_Exception('Invalid format for config file');
	                    break;
	            }
	            $config->merge($cfg);
			}
		}
		$config->merge(new Zend_Config($this->getOptions()));

		$this->setOptions($config->toArray());
	}

    /**
     * Инициализация отладчика
     */
	protected function _initDebug() {
		if ($this->hasOption('zfdebug')) {
			$opt = $this->getOption('zfdebug');
			if ($opt['active']) {
				$this->bootstrap('db');
	            Zend_Controller_Front::getInstance()->registerPlugin(new Zkernel_Controller_Plugin_Debug($opt));
			}
       	}
	}

    /**
     * Инициализация локали и транслейта
     */
	/*protected function _initLocale() {
		$model = new Default_Model_Txt();
		$translate = new Zend_Translate(
			'array',
			$model->fetchPairs('key', 'value')
		);
		Zend_Registry::set('Zend_Translate', $translate);
	}*/
}

