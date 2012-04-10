<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Controller_Action extends Zend_Controller_Action {

	/**
     * Default model object.
     *
     * @var Zkernel_Db_Table
     */
	public $model;

	function init() {
		$p = explode('/', trim(@$_SERVER['REQUEST_URI'], '/'));
		$reg = Zend_Registry::isRegistered('Zkernel_Multilang') ? Zend_Registry::get('Zkernel_Multilang') : '';
		if ($reg && !$reg->domain && $p) array_shift($p);
		$model = 'Default_Model_'.ucfirst($this->getRequest()->getControllerName());
		if (@class_exists($model)) $this->model = new $model();
		if (substr(@$p[1], 0, 3) == 'ctl') {
			$pp = array();
			for ($i = 2; $i < count($p); $i += 2) $pp[$p[$i]] = @$p[$i + 1];
			$this->_helper->viewRenderer('control/router', null, true);
			$this->view->controller = $p[0];
			$this->view->action = $p[1];
			$this->view->param = $pp;
			$this->view->post = $_POST;
			$this->view->model = $this->model;
			unset($this->view->param['controller']);unset($this->view->param['action']);unset($this->view->param['module']);
		}
	}

	function __call($m, $p) {
	}
}