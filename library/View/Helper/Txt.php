<?php

class Zkernel_View_Helper_Txt extends Zend_View_Helper_Abstract  {
	public function txt($key = null, $field = 'value') {
    	if ($key === null) return $this;
    	else {
			$model = new Default_Model_Txt();
   			return $model->fetchOne($field, array(
   				'`key` = ?' => $key
   			));
    	}
    }
}