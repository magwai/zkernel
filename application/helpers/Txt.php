<?php

class Helper_Txt extends Zend_Controller_Action_Helper_Abstract  {
	public function direct($key = null, $field = 'value')
    {
    	if ($key === null) return $this;
    	else {
			$model = new Site_Model_Txt();
   			return $model->fetchOne($field, array(
   				'`key` = ?' => $key
   			));
    	}
    }
}