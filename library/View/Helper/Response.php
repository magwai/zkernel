<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Response extends Zend_View_Helper_Abstract  {
private $_data = array();

	public function send() {
		$this->view->layout()->disableLayout(true);
		$script = $this->view->inlineSingle('script_clean');
		if ($script) $this->_data['script'] = $script;
		echo $this->view->json($this->_data);
	}
	public function response($key = null, $data = '') {
		if ($key !== null) $this->_data[$key] = $data ? $data : array('k' => 'v');
		return $this;
	}
}