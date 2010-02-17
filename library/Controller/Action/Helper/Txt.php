<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Controller_Action_Helper_Txt extends Zend_Controller_Action_Helper_Abstract  {
	public function direct($key = null, $field = 'value')
    {
    	if ($key === null) return $this;
    	else {
			$model = new Default_Model_Txt();
   			return $model->fetchOne($field, array(
   				'`key` = ?' => $key
   			));
    	}
    }
}