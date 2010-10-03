<?php

class Zkernel_Controller_Plugin_Multilang extends Zend_Controller_Plugin_Abstract {
	const DEFAULT_REGISTRY_KEY = 'Zkernel_Multilang';
    const DEFAULT_MODEL = 'Default_Model_Lang';
    private $_model;
	protected $_domain = false;
	protected $_lang = null;
	protected $_session = null;
	protected $_key = null;
	protected $_default = null;

	public function __construct($options = array()) {
		if (isset($options['domain'])) $this->_domain = $options['domain'];
		$class = isset($options['model']) ? $options['model'] : self::DEFAULT_MODEL;
		$this->_model = new $class();
		$this->_session = new Zend_Session_Namespace();
		$this->_key = isset($options['registry']) ? $options['registry'] : self::DEFAULT_REGISTRY_KEY;
    }

	public function routeStartup(Zend_Controller_Request_Abstract $request) {
		/*
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		$routes = $router->getRoutes();
		$router->removeDefaultRoutes();
		if ($routes) foreach ($routes as $k => $el) $router->removeRoute($k);
		$langRoute = new Zend_Controller_Router_Route(
			':lang',
			array(
				'lang' => $this->getDefault()->stitle
			)
	    );
		$router->addRoute('default', $langRoute->chain(new Zend_Controller_Router_Route_Module(
            array(),
            $front->getDispatcher(),
            $front->getRequest()
		)));
		$router->addRoute('lang', $langRoute);
		if ($routes) foreach ($routes as $k => $el)  $router->addRoute($k, $k == 'fu' || $k == 'minify' ? $el : $langRoute->chain($el));
		*/


		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();

		if ($this->_domain) {
			$doms = $this->_model->fetchPairs('id', 'domain');
			if ($doms) foreach ($doms as &$el) $el = @$el[0] ? explode(' ', $el) : array();

			$lang = $request->getParam('lang');
			if ($lang) $this->_session->lang = $this->_model->fetchOne('id', array('`stitle` = ?' => $lang));
			else {
				if ($doms) foreach ($doms as $k => $el) {
					if (in_array($_SERVER['HTTP_HOST'], $el)) {
						$this->_session->lang = $k;
						break;
					}
				}
			}
			$this->_lang = $this->_model->fetchRow(null, '(`id` = '.(int)$this->_session->lang.') DESC, (`default` = 1) DESC', 1);
			if ($this->_lang) $this->_lang = new Zkernel_View_Data($this->_lang);
			$this->_lang->_default = $this->getDefault();
			$this->_lang->_ids = $this->_model->fetchIds();
			$this->_lang->_doms = $doms;
		}
		else {
			$routes = $router->getRoutes();
			$router->removeDefaultRoutes();
			if ($routes) foreach ($routes as $k => $el) $router->removeRoute($k);
			$langRoute = new Zend_Controller_Router_Route(
				':lang',
				array(
					'lang' => $this->getDefault()->stitle
				)
		    );
			$router->addRoute('default', $langRoute->chain(new Zend_Controller_Router_Route_Module(
	            array(),
	            $front->getDispatcher(),
	            $front->getRequest()
			)));
			$router->addRoute('lang', $langRoute);
			if ($routes) foreach ($routes as $k => $el)  $router->addRoute($k, $k == 'fu' || $k == 'minify' ? $el : $langRoute->chain($el));
		}

		$this->save();
	}

	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		if (!$this->_domain) {
			if ($request->getParam('lang')) $this->_session->lang = $this->_model->fetchOne('id', array('`stitle` = ?' => $request->getParam('lang')));
			$this->_lang = $this->_model->fetchRow(null, '(`id` = '.(int)$this->_session->lang.') DESC, (`default` = 1) DESC', 1);
			if ($this->_lang) $this->_lang = new Zkernel_View_Data($this->_lang);
			$this->_lang->_default = $this->getDefault();
			$this->_lang->_ids = $this->_model->fetchIds();

			$front = Zend_Controller_Front::getInstance();
			$router = $front->getRouter();
			$router->setGlobalParam('lang', $this->_lang->stitle);

			$this->save();
		}
	}

    /**
     * Получить язык по умолчанию
     *
     * @return Zkernel_View_Data
     */
	public function getDefault() {
		if ($this->_default === null) {
			$this->_default = $this->_model->fetchRow('`default` = 1');
			if ($this->_default) $this->_default = new Zkernel_View_Data($this->_default);

		}
		return $this->_default;
	}

	public function set($id) {
		$this->_lang = $this->_model->fetchRow(null, '(`id` = '.(int)$id.') DESC, (`default` = 1) DESC', 1);
		if ($this->_lang) $this->_lang = new Zkernel_View_Data($this->_lang);
		$this->_lang->_default = $this->getDefault();
	}

	public function save() {
		Zend_Registry::set($this->_key, $this->_lang);
	}
}