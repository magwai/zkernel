<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Controller_Action_Helper_Jquery extends Zend_Controller_Action_Helper_Abstract  {
	public function direct($selector = null)
    {
    	if (!class_exists(jQuery)) require_once APPLICATION_PATH . '/../library/Zkernel/Jquery/jQuery.php';
        return $selector ? jQuery::addQuery($selector) : new jQuery();
    }
}