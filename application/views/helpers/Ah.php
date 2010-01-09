<?php

class View_Helper_Ah extends Zend_View_Helper_Abstract  {
	public function ah($helper) {
		$a = func_get_args();
		unset($a[0]);
		return call_user_func_array(array(
			Zend_Controller_Action_HelperBroker::getStaticHelper($helper),
			'direct'
		), $a);
	}
}