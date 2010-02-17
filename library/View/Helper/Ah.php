<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Ah extends Zend_View_Helper_Abstract  {
	public function ah($helper) {
		$a = func_get_args();
		unset($a[0]);
		return call_user_func_array(array(
			Zend_Controller_Action_HelperBroker::getStaticHelper($helper),
			'direct'
		), $a);
	}
}