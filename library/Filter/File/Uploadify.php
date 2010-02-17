<?php
/**
 * Zkernel
 *
 * Copyright (c) 2010 Magwai Ltd. <info@magwai.ru>, http://magwai.ru
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

class Zkernel_Filter_File_Uploadify implements Zend_Filter_Interface
{
    protected $_directory = null;
    protected $_length = 20;

 	public function __construct($options = null)
    {
        if (!empty($options)) {
            if (isset($options['directory'])) $this->_directory = $options['directory'];
            if (isset($options['length'])) $this->_length = $options['length'];
        }
    }

    public function filter($value)
    {
		$ext = strrpos($value, '.');
    	if ($ext !== false) {
    		$t = substr($value, $ext + 1);
    		$value = substr($value, 0, $ext);
    		$ext = $t;
    	}

    	$stitle = Zend_Controller_Action_HelperBroker::getStaticHelper('util')->stitle($value, $this->_length);
    	$p = '';
    	while (file_exists($this->_directory.'/'.$stitle.$p.($ext ? '.'.$ext : ''))) $p = $p ? $p + 1 : 1;

    	return $stitle.$p.($ext ? '.'.$ext : '');
    }
}
