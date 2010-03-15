<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_View_Helper_Txt extends Zend_View_Helper_Abstract  {
	public function txt($key = null, $field = 'value') {
    	if ($key === null) return $this;
    	else {
			$model = new Default_Model_Txt();
			$ret = $model->fetchRow(array(
   				'`key` = ?' => $key
   			));
   			$ret = $this->view->override()->overrideSingle($ret, 'txt');
   			return $ret->$field;
    	}
    }
}