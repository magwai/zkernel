<?php

class entity extends k_entity {
	function __get($k) {
		if ($k == 'view') return $this->view;
		$ret = parent::__get($k);
		if (!isset($this->_data[$k])) {
			$name = substr(get_class($this), 7);
			$view = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('view');
			$d = $view->override()->overrideSingle($this->_data, $name);
			if (isset($d[$k]) && $d[$k] != $ret) {
				$ret = $d[$k];
			}
		}
		return $ret;
	}
}