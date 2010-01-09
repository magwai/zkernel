<?php

class Magwai_Model_Cconf extends Magwai_Db_Table
{
	protected $_name = 'cconf';

	public function fetchValue($key, $action = null, $controller = null) {
		if ($controller === null) $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		if ($action === null) $action = substr(Zend_Controller_Front::getInstance()->getRequest()->getActionName(), 3);

		return $this->getAdapter()->fetchOne(
    		$this->select()
    			->from($this, array('value'))
    			->where('`controller` = ?', $controller)
    			->where('`action` = ?', $action)
    			->where('`key` = ?', $key)
    	);
    }

	public function fetchPairs($where = null, $action = null, $controller = null) {
		if ($controller === null) $controller = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
		if ($action === null) $action = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
		$select = $this->select()
    		//->from($this, array('CONCAT(`type`, ":", `key`)', 'value'))
    		->from($this, array('key', 'IF (`type` != "text", CONCAT(`type`, ":", `value`), `value`)'))
    		->where('`controller` = ?', $controller)
    		->where('`action` = ?', $action);
    	if ($where !== null) $select->where($where);
		return $this->getAdapter()->fetchPairs($select);
    }
}
