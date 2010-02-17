<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Controller_Action_Helper_Js extends Zend_Controller_Action_Helper_Abstract  {
	protected $_eval = array();

	public function addEval($script) {
		$this->_eval[] = $script;
	}

	public function renderEval() {
		return $this->_eval ? 'try { '.implode('', $this->_eval).' } catch (e) {}' : '';
	}

	public function direct()
    {
        return $this;
    }
}