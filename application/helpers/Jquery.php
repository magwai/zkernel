<?php

class Helper_Jquery extends Zend_Controller_Action_Helper_Abstract  {
	public function direct($selector = null)
    {
    	if (!class_exists(jQuery)) require_once SITE_PATH . '/lib/class/Magwai/Jquery/jQuery.php';
        return $selector ? jQuery::addQuery($selector) : new jQuery();
    }
}