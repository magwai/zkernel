<?php

class Zkernel_Controller_Plugin_Menu extends Zend_Controller_Plugin_Abstract {
	const DEFAULT_REGISTRY_KEY = 'Zend_Navigation';
    const DEFAULT_MODEL = 'Default_Model_Menu';
    protected $_model;
	protected $_map_cache = array();
	protected $_menu = null;
	protected $_key = null;
	protected $_default = null;

	public function __construct($options = array()) {
		$class = isset($options['model']) ? $options['model'] : self::DEFAULT_MODEL;
		$this->_model = new $class();
		$this->_key = isset($options['registry']) ? $options['registry'] : self::DEFAULT_REGISTRY_KEY;
    }

	public function routeShutdown(Zend_Controller_Request_Abstract $request) {
		//print_r($this->getDeeper());exit;
		$this->_menu = new Zend_Navigation($this->getDeeper());
		$this->save();
	}

	function getDeeper($id = 0) {
    	$m = $this->funcGetList($id);
		$mu = new Default_Model_Url();
    	$menu = array();
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		$request = $front->getRequest();
		$reg = Zend_Registry::isRegistered('Zkernel_Multilang') ? Zend_Registry::get('Zkernel_Multilang') : '';
		$view = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->view;
		if ($m) foreach ($m as $el) {
			if ($this->funcElCheck($el)) {
				$el = $view->override()->overrideSingle($el, 'menu');
				$p = $reg && !$reg->session && !@$reg->_default->domain ? array('lang' => $reg->stitle) : array();
				if ($el->route && $el->param && $el->route/* && ($el->route == 'default' || strpos($el->route, 'dbroute') !== false)*/) {
					if ($el->route == 'default') $map = '';
					else {
						$mk = substr($el->route, 7);
						if (isset($this->_map_cache[$mk])) $map = $this->_map_cache[$mk];
						else {
							$map = $mu->fetchOne('map', array('`id` = ?' => substr($el->route, 7)));
							$this->_map_cache[$mk] = $map;
						}
					}
					$pp = explode(',', $el->param);
					if ($map) {
						$mp = explode(',', $map);

						if ($mp && $pp) foreach ($mp as $n => $mp1) $p[$mp1] = $pp[$n];
					}
					else if ($pp) foreach ($pp as $mp1) {
						$ppp = explode(':', $mp1);
						$p[@$ppp[0] ? $ppp[0] : 'id'] = @$ppp[1];
					}
				}
				if(empty($el->url)) {
					$md = array(
						'label' => $el->title,
						'controller' => $el->controller,
						'action' => $el->action,
						'params' => $p,
						'route' => $el->route ? $el->route : 'default',
						'uri' => $el->url,
						'pages' => $this->getDeeper($el->id)
					);
					$d = $el->toArray();
					unset($d['param']);
					$menu[] = array_merge($d, $this->funcElArray($el, $md));
				}else{
					$md = array(
						'label' => $el->title,
						'uri' => $el->url,
						'pages' => $this->getDeeper($el->id),
						'key' => $el->key,
						'pic' => $el->pic
					);
					$menu[] = $this->funcElArray($el, $md);
				}
			}
		}
		return $menu;
    }

	public function save() {
		Zend_Registry::set($this->_key, $this->_menu);
	}

	function funcGetList($id) {
		return $this->_model->fetchAll(array('`parentid` = ?' => $id), 'orderid');
	}

	function funcElCheck($el) {
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		return !$el->route || $router->hasRoute($el->route) && !(isset($el->show_it) && !$el->show_it);
	}

	function funcElArray($el, $md) {
		return $md;
	}
}