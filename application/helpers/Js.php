<?php

class Helper_Js extends Zend_Controller_Action_Helper_Abstract  {
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