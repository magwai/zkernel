<?php

class Zkernel_Controller_Action_Helper_Page extends Zend_Controller_Action_Helper_Abstract  {
	public function direct($key = null, $field = 'message')
    {
    	if ($key === null) return $this;
    	else {
			$model = new Default_Model_Page();
			$f = $field == 'description' ? 'message' : $field;
   			$ret = $model->fetchOne($f, array(
   				'`stitle` = ?' => $key
   			));
   			if ($f == 'message') {
		    	$sp = preg_split('/\<hr(\ )\/\>/si', $ret);
				if (count($sp) > 1) {
					if ($field == 'description') $ret = preg_replace('/\<p\>$/i', '', trim($sp[0]));
					else {
						array_shift($sp);
						$ret = preg_replace('/^\<\/p\>/i', '', trim(implode('<hr />', $sp)));
					}
				}
   			}
			return $ret;
    	}
    }
}