<?php

class Zkernel_View_Helper_Jquery extends Zend_View_Helper_Abstract  {
	public function jquery($selector = null)
    {
    	if (!class_exists(jQuery)) require_once APPLICATION_PATH . '/../library/Zkernel/Jquery/jQuery.php';
        return $selector ? jQuery::addQuery($selector) : new jQuery();
    }
}