<?php

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

		if (substr($this->getRequest()->getActionName(), 0, 3) == 'ctl' && method_exists($this, 'ctlinit')) $this->ctlinit();
	}

	function __call($m, $p) {
		if (substr($m, 0, 3) == 'ctl' && substr($m, strlen($m) - 6) == 'Action') $this->_helper->control()->routeDefault();
	}
}

