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
		$model = 'Default_Model_'.ucfirst($this->getRequest()->getControllerName());
		if (@class_exists($model)) $this->model = new $model();
		if (substr($this->getRequest()->getActionName(), 0, 3) == 'ctl') {
			$this->_helper->viewRenderer('control/router', null, true);
			$this->view->controller = $this->getRequest()->getControllerName();
			$this->view->action = $this->getRequest()->getActionName();
			$this->view->param = $this->getRequest()->getParams();
			$this->view->post = $_POST;
			$this->view->model = $this->model;
			unset($this->view->param['controller']);unset($this->view->param['action']);unset($this->view->param['module']);
		}
	}

	function __call($m, $p) {
	}
}
