<?php

class Zkernel_Controller_Action_Helper_Page extends Zend_Controller_Action_Helper_Abstract  {
	public function direct($key = null, $field = 'message')
    {
    	if ($key === null) return $this;
    	else {
			$model = new Default_Model_Page();
   			return $model->fetchOne($field, array(
   				'`stitle` = ?' => $key
   			));
    	}
    }
}