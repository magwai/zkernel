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
     * TODO: Здесь мусорно. Инит кэша метаданых тут не нужен
     */
	protected function _initPapp() {
		$this->bootstrap('db');
    	$cache = Zend_Cache::factory('Core', 'Memcached', array('automatic_serialization' => true));
    	Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
		if ($this->hasOption('zfdebug')/* && $this->getOption('zfdebug.active') == 'true'*/) {
    		$c = $this->getOption('zfdebug');
    		if (isset($c['plugins']['Database'])) $c['plugins']['Database']['adapter']['standard'] = $this->getPluginResource('db')->getDbAdapter();
    		if (isset($c['plugins']['Cache'])) $c['plugins']['Cache']['backend'] = $cache->getBackend();
    		$c['image_path'] = '/zkernel/img/debug';
    		$c['jquery_path'] = '/zkernel/js/jquery/jquery.js';
    		$zfdebug = new Zkernel_Controller_Plugin_Debug($c);
            Zend_Controller_Front::getInstance()->registerPlugin($zfdebug);
       	}
	}

    /**
     * Инициализация загрузчика файлов
     * TODO: Это нужно отсюда убрать в модуль
     */
	public function _initFu() {
       	$router = Zend_Controller_Front::getInstance()->getRouter();
		$route = new Zend_Controller_Router_Route_Regex(
			'/fu',
			array(
				'controller' => 'fu',
				'action'     => 'index'
			)
		);
		$router->addRoute('fu', $route);
    }
}

